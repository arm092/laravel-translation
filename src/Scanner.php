<?php

namespace Arm092\Translation;

use Arm092\Translation\Contracts\TranslationScanner;
use Arm092\Translation\Scanning\DynamicTranslationUsage;
use Arm092\Translation\Scanning\ScanResult;
use Arm092\Translation\Scanning\TranslationOccurrence;
use Illuminate\Filesystem\Filesystem;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

class Scanner implements TranslationScanner
{
    public function __construct(
        private Filesystem $disk,
        array|string $scanPaths,
        private array $translationMethods,
        private array $excludedPaths = [],
        private array $ignoredKeys = [],
        private ?string $cachePath = null,
    ) {
        $this->scanPaths = (array) $scanPaths;
    }

    private array $scanPaths;

    /** Preserve the historical group/single result consumed by existing commands. */
    public function findTranslations(): array
    {
        $results = ['single' => [], 'group' => []];

        foreach ($this->scan()->keys() as $key) {
            if (preg_match('/^([A-Za-z0-9:_-]+)\.(.+)$/s', $key, $match)) {
                $results['group'][$match[1]][$match[2]] = '';
            } else {
                $results['single']['single'][$key] = '';
            }
        }

        return $results;
    }

    public function scan(bool $refresh = false): ScanResult
    {
        $files = $this->files();
        $fingerprint = $this->fingerprint($files);

        if (! $refresh && $this->cachePath && $this->disk->exists($this->cachePath)) {
            $cached = json_decode($this->disk->get($this->cachePath), true);
            if (is_array($cached) && ($cached['fingerprint'] ?? null) === $fingerprint) {
                return ScanResult::fromArray($cached['result'] ?? []);
            }
        }

        $occurrences = [];
        $dynamic = [];
        foreach ($files as $file) {
            $this->scanFile($file, $occurrences, $dynamic);
        }

        $result = new ScanResult($occurrences, $dynamic);
        if ($this->cachePath) {
            $this->disk->ensureDirectoryExists(dirname($this->cachePath));
            $this->disk->put($this->cachePath, json_encode(['fingerprint' => $fingerprint, 'result' => $result->toArray()], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return $result;
    }

    public function clearCache(): void
    {
        if ($this->cachePath && $this->disk->exists($this->cachePath)) {
            $this->disk->delete($this->cachePath);
        }
    }

    private function files(): array
    {
        $files = [];
        foreach ($this->scanPaths as $root) {
            $rootReal = realpath($root);
            if ($rootReal === false) {
                continue;
            }
            foreach ($this->disk->allFiles($rootReal) as $file) {
                $real = realpath($file->getPathname());
                if ($real === false || ! str_starts_with($real, $rootReal.DIRECTORY_SEPARATOR) || is_link($file->getPathname())) {
                    continue;
                }
                if ($this->cachePath && strcasecmp($real, realpath($this->cachePath) ?: $this->cachePath) === 0) {
                    continue;
                }
                if ($this->excluded($real)) {
                    continue;
                }
                $files[] = $real;
            }
        }
        usort($files, function ($left, $right) {
            $leftName = pathinfo(str_replace('\\', '/', $left), PATHINFO_BASENAME);
            $rightName = pathinfo(str_replace('\\', '/', $right), PATHINFO_BASENAME);
            $priority = fn ($name) => str_starts_with($name, '_') ? 0 : 1;

            return ($priority($leftName) <=> $priority($rightName)) ?: strnatcasecmp($leftName, $rightName);
        });

        return array_values(array_unique($files));
    }

    private function excluded(string $path): bool
    {
        foreach ($this->excludedPaths as $excluded) {
            $real = realpath($excluded) ?: $excluded;
            if ($real !== '' && str_starts_with(strtolower($path), strtolower(rtrim($real, '\\/').DIRECTORY_SEPARATOR))) {
                return true;
            }
        }

        return false;
    }

    private function fingerprint(array $files): string
    {
        return hash('sha256', json_encode([
            'version' => 6,
            'methods' => $this->translationMethods,
            'ignored' => $this->ignoredKeys,
            'files' => array_map(fn ($file) => [$file, filemtime($file), filesize($file)], $files),
        ]));
    }

    private function scanFile(string $file, array &$occurrences, array &$dynamic): void
    {
        $code = $this->disk->get($file);
        if (str_ends_with($file, '.blade.php')) {
            preg_match_all('/@lang\s*\(\s*([\'\"])(.*?)\1\s*\)/s', $code, $matches, PREG_OFFSET_CAPTURE);
            foreach ($matches[2] as [$key, $offset]) {
                $this->addLiteral($key, $file, substr_count(substr($code, 0, $offset), "\n") + 1, $occurrences);
            }
        }

        try {
            $php = str_ends_with($file, '.blade.php') ? preg_replace('/@lang\s*\([^)]*\)/s', '', $code) : $code;
            if (! str_contains($php, '<?php')) {
                $php = "<?php\n".$php;
            }
            $nodes = (new ParserFactory)->createForNewestSupportedVersion()->parse($php) ?? [];
        } catch (\Throwable $exception) {
            $this->scanLiteralCalls($code, $file, $occurrences);
            if (! $occurrences) {
                $dynamic[] = new DynamicTranslationUsage('parse-error: '.$exception->getMessage(), $file, method_exists($exception, 'getStartLine') ? $exception->getStartLine() : 1);
            }

            return;
        }

        foreach ((new NodeFinder)->find($nodes, fn (Node $node) => $this->isTranslationCall($node)) as $call) {
            $argument = $call->args[0]->value ?? null;
            if ($argument instanceof Node\Scalar\String_) {
                $this->addLiteral($argument->value, $file, $call->getStartLine(), $occurrences);
            } else {
                $dynamic[] = new DynamicTranslationUsage((new Standard)->prettyPrintExpr($argument), $file, $call->getStartLine(), true);
            }
        }
    }

    private function isTranslationCall(Node $node): bool
    {
        $methods = array_unique([...$this->translationMethods, 'trans_choice', 'get', 'choice']);
        if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name) {
            return in_array($node->name->toString(), $methods, true);
        }
        if ($node instanceof Node\Expr\StaticCall && $node->class instanceof Node\Name && $node->name instanceof Node\Identifier) {
            return strtolower($node->class->getLast()) === 'lang' && in_array($node->name->toString(), $methods, true);
        }
        if ($node instanceof Node\Expr\MethodCall && $node->name instanceof Node\Identifier && in_array($node->name->toString(), ['get', 'choice'], true)) {
            return $node->var instanceof Node\Expr\FuncCall && $node->var->name instanceof Node\Name && $node->var->name->toString() === 'app';
        }

        return false;
    }

    private function addLiteral(string $key, string $file, int $line, array &$occurrences): void
    {
        if ($key === '' || in_array($key, $this->ignoredKeys, true)) {
            return;
        }
        $occurrences[] = new TranslationOccurrence($key, $file, $line);
    }

    private function scanLiteralCalls(string $code, string $file, array &$occurrences): void
    {
        $names = array_map(fn ($method) => preg_quote(ltrim($method, '@'), '/'), $this->translationMethods);
        $pattern = '/(?:'.implode('|', $names).')\s*\(\s*([\'\"])(.*?)\1/si';
        preg_match_all($pattern, $code, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[2] as [$key, $offset]) {
            $this->addLiteral($key, $file, substr_count(substr($code, 0, $offset), "\n") + 1, $occurrences);
        }
    }
}
