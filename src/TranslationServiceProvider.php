<?php

namespace Arm092\Translation;

use Arm092\Translation\Console\Commands\AddLanguageCommand;
use Arm092\Translation\Console\Commands\AddTranslationKeyCommand;
use Arm092\Translation\Console\Commands\ClearScanCacheCommand;
use Arm092\Translation\Console\Commands\ExportTranslationsCommand;
use Arm092\Translation\Console\Commands\FormatTranslationsCommand;
use Arm092\Translation\Console\Commands\ImportTranslationsCommand;
use Arm092\Translation\Console\Commands\ListLanguagesCommand;
use Arm092\Translation\Console\Commands\ListMissingTranslationKeys;
use Arm092\Translation\Console\Commands\MissingTranslationsCommand;
use Arm092\Translation\Console\Commands\ScanTranslationsCommand;
use Arm092\Translation\Console\Commands\SynchroniseMissingTranslationKeys;
use Arm092\Translation\Console\Commands\SynchroniseTranslationsCommand;
use Arm092\Translation\Console\Commands\UnusedTranslationsCommand;
use Arm092\Translation\Contracts\TranslationBatchWriter;
use Arm092\Translation\Contracts\TranslationExporter;
use Arm092\Translation\Contracts\TranslationFormatter;
use Arm092\Translation\Contracts\TranslationImporter;
use Arm092\Translation\Contracts\TranslationScanner;
use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Support\CsvTranslations;
use Arm092\Translation\Support\Frontend;
use Arm092\Translation\Support\RouteNames;
use Arm092\Translation\Support\SourceLocale;
use Arm092\Translation\Support\TranslationBatch;
use Arm092\Translation\Support\TranslationCache;
use Arm092\Translation\Support\TranslationFormat;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package services.
     */
    public function boot(): void
    {
        $this->loadViews();

        $this->registerLivewireComponents();

        $this->registerRoutes();

        $this->publishConfiguration();

        $this->publishAssets();

        $this->loadMigrations();

        $this->loadTranslations();

    }

    /**
     * Register package bindings in the container.
     */
    public function register(): void
    {
        $this->mergeConfiguration();

        $this->registerCommands();

        $this->registerContainerBindings();
    }

    /**
     * Load and publish package views.
     */
    private function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'translation');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/translation'),
        ]);
    }

    /**
     * Register package routes.
     */
    private function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    /**
     * Publish package configuration.
     */
    private function publishConfiguration(): void
    {
        $this->publishes([
            __DIR__.'/../config/translation.php' => config_path('translation.php'),
        ], 'config');
    }

    /**
     * Merge package configuration.
     */
    private function mergeConfiguration(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/translation.php', 'translation');
    }

    /**
     * Publish package assets.
     */
    private function publishAssets(): void
    {
        $this->publishes([
            __DIR__.'/../public/assets' => public_path('vendor/translation'),
        ], 'assets');
    }

    /**
     * Load package migrations.
     */
    private function loadMigrations(): void
    {
        if (config('translation.driver') !== 'database') {
            return;
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Load package translations.
     */
    private function loadTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'translation');

        $this->publishes([
            __DIR__.'/../resources/lang' => $this->app->langPath('vendor/translation'),
        ]);
    }

    /**
     * Register package commands.
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                AddLanguageCommand::class,
                AddTranslationKeyCommand::class,
                ListLanguagesCommand::class,
                ListMissingTranslationKeys::class,
                SynchroniseMissingTranslationKeys::class,
                SynchroniseTranslationsCommand::class,
                ScanTranslationsCommand::class,
                MissingTranslationsCommand::class,
                UnusedTranslationsCommand::class,
                ClearScanCacheCommand::class,
                ExportTranslationsCommand::class,
                ImportTranslationsCommand::class,
                FormatTranslationsCommand::class,
            ]);
        }
    }

    /**
     * Register package bindings in the container.
     */
    private function registerContainerBindings(): void
    {
        $this->app->singleton(Frontend::class);
        $this->app->singleton(RouteNames::class);
        $this->app->singleton(SourceLocale::class);
        $this->app->singleton(TranslationCache::class);
        $this->app->singleton(TranslationBatchWriter::class, TranslationBatch::class);
        $this->app->singleton(CsvTranslations::class);
        $this->app->alias(CsvTranslations::class, TranslationExporter::class);
        $this->app->alias(CsvTranslations::class, TranslationImporter::class);
        $this->app->singleton(TranslationFormatter::class, TranslationFormat::class);

        $this->app->singleton(Scanner::class, function () {
            $config = $this->app['config']['translation'];

            return new Scanner(
                new Filesystem,
                $config['scan_paths'],
                $config['translation_methods'],
                $config['scan_excluded_paths'] ?? [],
                $config['scan_ignored_keys'] ?? [],
                $config['scan_cache_path'] ?? null,
            );
        });
        $this->app->alias(Scanner::class, TranslationScanner::class);

        $this->app->singleton(Translation::class, function ($app) {
            return (new TranslationManager($app, $app['config']['translation'], $app->make(Scanner::class)))->resolve();
        });
    }

    /**
     * Register optional Livewire 4 components.
     */
    private function registerLivewireComponents(): void
    {
        if (! $this->app->make(Frontend::class)->usesLivewire()) {
            return;
        }

        forward_static_call_array(['Livewire\\Livewire', 'addNamespace'], [
            'translation-manager',
            null,
            'Arm092\\Translation\\Livewire',
            __DIR__.'/Livewire',
            __DIR__.'/../resources/views/livewire',
        ]);
    }
}
