<?php

namespace Arm092\Translation\Console\Commands;

use Arm092\Translation\Support\SourceLocale;
use Arm092\Translation\Support\TranslationQuality;
use Illuminate\Console\Command;

class UnusedTranslationsCommand extends Command
{
    protected $signature = 'translation:unused {locale?} {--format=table} {--fail}';

    protected $description = 'List stored translation keys not found by the scanner';

    public function handle(TranslationQuality $quality, SourceLocale $source): int
    {
        $locale = $this->argument('locale') ?: $source->get();
        $keys = $quality->unused($locale);
        $this->option('format') === 'json' ? $this->line(json_encode(['locale' => $locale, 'unused' => $keys], JSON_PRETTY_PRINT)) : $this->table(['Unused key'], array_map(fn ($key) => [$key], $keys));

        return $this->option('fail') && $keys ? self::FAILURE : self::SUCCESS;
    }
}
