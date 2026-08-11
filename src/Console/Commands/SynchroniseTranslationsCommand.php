<?php

namespace Arm092\Translation\Console\Commands;

use Arm092\Translation\Drivers\Database;
use Arm092\Translation\Drivers\File;
use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Scanner;
use Arm092\Translation\Support\ProtectedLocales;
use Arm092\Translation\Support\SourceLocale;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class SynchroniseTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:sync-translations {from?} {to?} {language?} {--dry-run} {--conflict=overwrite} {--force-protected}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise translations between drivers';

    /**
     * File scanner.
     *
     * @var Scanner
     */
    private $scanner;

    /**
     * Translation.
     *
     * @var Translation
     */
    private $translation;

    /**
     * From driver.
     */
    private $fromDriver;

    /**
     * To driver.
     */
    private $toDriver;

    /**
     * Translation drivers.
     *
     * @var array
     */
    private $drivers = ['file', 'database'];

    private $sourceLocale;

    private int $planned = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Scanner $scanner, Translation $translation, SourceLocale $sourceLocale)
    {
        parent::__construct();
        $this->scanner = $scanner;
        $this->translation = $translation;
        $this->sourceLocale = $sourceLocale;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $languages = array_keys($this->translation->allLanguages()->toArray());

        // If a valid from driver has been specified as an argument.
        if ($this->argument('from') && in_array($this->argument('from'), $this->drivers)) {
            $this->fromDriver = $this->argument('from');
        }

        // When the from driver will be entered manually or if the argument is invalid.
        else {
            $this->fromDriver = $this->anticipate('Which driver would you like to take translations from?', $this->drivers);

            if (! in_array($this->fromDriver, $this->drivers)) {
                $this->error('Invalid driver');

                return self::FAILURE;
            }
        }

        // Create the driver.
        $this->fromDriver = $this->createDriver($this->fromDriver);

        // When the to driver has been specified.
        if ($this->argument('to') && in_array($this->argument('to'), $this->drivers)) {
            $this->toDriver = $this->argument('to');
        }

        // When the to driver will be entered manually.
        else {
            $this->toDriver = $this->anticipate('Which driver would you like to add the translations to?', $this->drivers);

            if (! in_array($this->toDriver, $this->drivers)) {
                $this->error('Invalid driver');

                return self::FAILURE;
            }
        }

        // Create the driver.
        $this->toDriver = $this->createDriver($this->toDriver);

        // If the language argument is set.
        if ($this->argument('language')) {
            // If all languages should be synced.
            if ($this->argument('language') == 'all') {
                $language = false;
            }
            // When a specific language is set and is valid.
            elseif (in_array($this->argument('language'), $languages)) {
                $language = $this->argument('language');
            } else {
                $this->error('Invalid language');

                return self::FAILURE;
            }
        } // When the language will be entered manually or if the argument is invalid.
        else {
            $language = $this->anticipate('Which language? (leave blank for all)', $languages);

            if ($language && ! in_array($language, $languages)) {
                $this->error('Invalid language');

                return self::FAILURE;
            }
        }

        $this->line('Syncing translations');

        // If a specific language is set.
        if ($language) {
            $this->mergeTranslations($this->toDriver, $language, $this->fromDriver->allTranslationsFor($language));
        } // Else process all languages.
        else {
            $translations = $this->mergeLanguages($this->toDriver, $this->fromDriver->allTranslations());
        }

        $this->info($this->option('dry-run') ? "Dry run: {$this->planned} translations would be synced" : 'Translations have been synced');
    }

    private function createDriver($driver)
    {
        if ($driver === 'file') {
            return new File(new Filesystem, app('path.lang'), $this->sourceLocale->get(), $this->scanner);
        }

        return new Database($this->sourceLocale->get(), $this->scanner);
    }

    private function mergeLanguages($driver, $languages)
    {
        foreach ($languages as $language => $translations) {
            $this->mergeTranslations($driver, $language, $translations);
        }
    }

    private function mergeTranslations($driver, $language, $translations)
    {
        app(ProtectedLocales::class)->authorize($language, (bool) $this->option('force-protected'));
        $this->mergeGroupTranslations($driver, $language, $translations['group']);
        $this->mergeSingleTranslations($driver, $language, $translations['single']);
    }

    private function mergeGroupTranslations($driver, $language, $groups)
    {
        foreach ($groups as $group => $translations) {
            foreach ($translations as $key => $value) {
                if (is_array($value)) {
                    continue;
                }
                if (! $this->shouldWrite($driver, $language, 'group', $group, $key, $value)) {
                    continue;
                }
                $this->planned++;
                if ($this->option('dry-run')) {
                    continue;
                }
                $driver->addGroupTranslation($language, $group, $key, $value);
            }
        }
    }

    private function mergeSingleTranslations($driver, $language, $vendors)
    {
        foreach ($vendors as $vendor => $translations) {
            foreach ($translations as $key => $value) {
                if (is_array($value)) {
                    continue;
                }
                if (! $this->shouldWrite($driver, $language, 'single', $vendor, $key, $value)) {
                    continue;
                }
                $this->planned++;
                if ($this->option('dry-run')) {
                    continue;
                }
                $driver->addSingleTranslation($language, $vendor, $key, $value);
            }
        }
    }

    private function shouldWrite($driver, string $language, string $type, string $group, string $key, mixed $value): bool
    {
        $policy = (string) $this->option('conflict');
        if (! in_array($policy, ['overwrite', 'skip', 'fail'], true)) {
            throw new \InvalidArgumentException("Invalid conflict policy [$policy].");
        }
        $current = $driver->allTranslationsFor($language)->get($type, collect())->get($group, collect())->get($key);
        if ($current === null || (string) $current === (string) $value) {
            return true;
        }
        if ($policy === 'skip') {
            return false;
        }
        if ($policy === 'fail') {
            throw new \RuntimeException("Conflict for [$language:$group.$key].");
        }

        return true;
    }
}
