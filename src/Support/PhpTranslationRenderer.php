<?php

namespace Arm092\Translation\Support;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Stmt\Return_;
use PhpParser\PrettyPrinter\Standard;

final class PhpTranslationRenderer
{
    public function render(array $translations): string
    {
        $translations = $this->sort($translations);

        return (new Standard)->prettyPrintFile([new Return_(BuilderHelpers::normalizeValue($translations))])."\n";
    }

    private function sort(array $values): array
    {
        uksort($values, 'strnatcasecmp');
        foreach ($values as &$value) {
            if (is_array($value)) {
                $value = $this->sort($value);
            }
        }

        return $values;
    }
}
