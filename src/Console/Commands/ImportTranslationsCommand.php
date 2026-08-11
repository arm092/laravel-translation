<?php

namespace Arm092\Translation\Console\Commands;

use Arm092\Translation\Contracts\TranslationImporter;
use Illuminate\Console\Command;

class ImportTranslationsCommand extends Command
{
    protected $signature = 'translation:import {path} {--conflict=fail} {--commit} {--force-protected}';

    protected $description = 'Validate, preview or import CSV v1 translations';

    public function handle(TranslationImporter $importer): int
    {
        $result = $importer->import($this->argument('path'), $this->option('conflict'), (bool) $this->option('commit'), (bool) $this->option('force-protected'));
        $this->table(['Rows', 'Written', 'Committed'], [[$result['rows'], $result['written'], $result['committed'] ? 'yes' : 'no']]);

        return self::SUCCESS;
    }
}
