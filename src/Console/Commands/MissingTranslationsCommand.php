<?php

namespace Arm092\Translation\Console\Commands;

use Arm092\Translation\Support\SourceLocale;
use Arm092\Translation\Support\TranslationQuality;
use Illuminate\Console\Command;

class MissingTranslationsCommand extends Command
{
    protected $signature = 'translation:missing {locale?} {--format=table} {--fail}';

    protected $description = 'List translation keys used by code but missing from a locale';

    public function handle(TranslationQuality $quality, SourceLocale $source): int
    {
        $locale = $this->argument('locale') ?: $source->get();
        $keys = $quality->missing($locale);
        $this->option('format') === 'json' ? $this->line(json_encode(['locale' => $locale, 'missing' => $keys], JSON_PRETTY_PRINT)) : $this->table(['Missing key'], array_map(fn ($key) => [$key], $keys));

        return $this->option('fail') && $keys ? self::FAILURE : self::SUCCESS;
    }
}
