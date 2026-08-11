<?php

namespace Arm092\Translation\Console\Commands;

use Arm092\Translation\Scanner;
use Illuminate\Console\Command;

class ScanTranslationsCommand extends Command
{
    protected $signature = 'translation:scan {--format=table} {--refresh} {--fail}';

    protected $description = 'Scan application code for translation usage';

    public function handle(Scanner $scanner): int
    {
        $result = $scanner->scan((bool) $this->option('refresh'));
        $payload = $result->toArray();
        if ($this->option('format') === 'json') {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Key', 'File', 'Line'], array_map(fn ($item) => [$item->key, $item->file, $item->line], $result->occurrences));
            if ($result->dynamic) {
                $this->warn(count($result->dynamic).' dynamic or ambiguous translation usages require review.');
            }
        }

        return $this->option('fail') && $result->dynamic ? self::FAILURE : self::SUCCESS;
    }
}
