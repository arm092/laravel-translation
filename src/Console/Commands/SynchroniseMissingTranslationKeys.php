<?php

namespace Arm092\Translation\Console\Commands;

use Arm092\Translation\Support\ProtectedLocales;

class SynchroniseMissingTranslationKeys extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:sync-missing-translation-keys {language?} {--force-protected}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add all of the missing translation keys for all languages or a single language';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $language = $this->argument('language') ?: false;

        if ($language) {
            app(ProtectedLocales::class)->authorize($language, (bool) $this->option('force-protected'));
        } else {
            foreach (array_keys($this->translation->allLanguages()->all()) as $locale) {
                app(ProtectedLocales::class)->authorize($locale, (bool) $this->option('force-protected'));
            }
        }

        try {
            // if we have a language, pass it in, if not the method will
            // automagically sync all languages
            $this->translation->saveMissingTranslations($language);

            $this->info(__('translation::translation.keys_synced'));

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
