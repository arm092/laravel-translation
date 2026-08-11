<?php

namespace Arm092\Translation\Console\Commands;

use Arm092\Translation\Contracts\TranslationExporter;
use Illuminate\Console\Command;

class ExportTranslationsCommand extends Command
{
    protected $signature = 'translation:export {path} {--locale=*}';

    protected $description = 'Export translations as CSV v1';

    public function handle(TranslationExporter $exporter): int
    {
        $count = $exporter->export($this->argument('path'), $this->option('locale'));
        $this->info("Exported $count translation records.");

        return self::SUCCESS;
    }
}
