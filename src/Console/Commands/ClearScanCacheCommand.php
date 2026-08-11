<?php

namespace Arm092\Translation\Console\Commands;

use Arm092\Translation\Scanner;
use Illuminate\Console\Command;

class ClearScanCacheCommand extends Command
{
    protected $signature = 'translation:scan-cache-clear';

    protected $description = 'Clear the translation scanner cache';

    public function handle(Scanner $scanner): int
    {
        $scanner->clearCache();
        $this->info('Translation scan cache cleared.');

        return self::SUCCESS;
    }
}
