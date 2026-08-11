<?php

namespace Arm092\Translation\Console\Commands;

use Arm092\Translation\Contracts\TranslationFormatter;
use Illuminate\Console\Command;

class FormatTranslationsCommand extends Command
{
    protected $signature = 'translation:format {--locale=*} {--write} {--force-protected}';

    protected $description = 'Preview or apply deterministic translation ordering';

    public function handle(TranslationFormatter $formatter): int
    {
        $result = $formatter->format($this->option('locale'), (bool) $this->option('write'), (bool) $this->option('force-protected'));
        $this->info(($result['written'] ? 'Formatted ' : 'Would format ').$result['translations'].' translations.');

        return self::SUCCESS;
    }
}
