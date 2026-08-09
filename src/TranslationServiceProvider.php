<?php

namespace JoeDixon\Translation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use JoeDixon\Translation\Console\Commands\AddLanguageCommand;
use JoeDixon\Translation\Console\Commands\AddTranslationKeyCommand;
use JoeDixon\Translation\Console\Commands\ListLanguagesCommand;
use JoeDixon\Translation\Console\Commands\ListMissingTranslationKeys;
use JoeDixon\Translation\Console\Commands\SynchroniseMissingTranslationKeys;
use JoeDixon\Translation\Console\Commands\SynchroniseTranslationsCommand;
use JoeDixon\Translation\Drivers\Translation;
use JoeDixon\Translation\Support\Frontend;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package services.
     *
     * @return void
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
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfiguration();

        $this->registerCommands();

        $this->registerContainerBindings();
    }

    /**
     * Load and publish package views.
     *
     * @return void
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
     *
     * @return void
     */
    private function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    /**
     * Publish package configuration.
     *
     * @return void
     */
    private function publishConfiguration(): void
    {
        $this->publishes([
            __DIR__.'/../config/translation.php' => config_path('translation.php'),
        ], 'config');
    }

    /**
     * Merge package configuration.
     *
     * @return void
     */
    private function mergeConfiguration(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/translation.php', 'translation');
    }

    /**
     * Publish package assets.
     *
     * @return void
     */
    private function publishAssets(): void
    {
        $this->publishes([
            __DIR__.'/../public/assets' => public_path('vendor/translation'),
        ], 'assets');
    }

    /**
     * Load package migrations.
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
            ]);
        }
    }

    /**
     * Register package bindings in the container.
     *
     * @return void
     */
    private function registerContainerBindings(): void
    {
        $this->app->singleton(Frontend::class);

        $this->app->singleton(Scanner::class, function () {
            $config = $this->app['config']['translation'];

            return new Scanner(new Filesystem(), $config['scan_paths'], $config['translation_methods']);
        });

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

        \Livewire\Livewire::addNamespace(
            namespace: 'translation-manager',
            classNamespace: 'JoeDixon\\Translation\\Livewire',
            classPath: __DIR__.'/Livewire',
            classViewPath: __DIR__.'/../resources/views/livewire',
        );
    }
}
