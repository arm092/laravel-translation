<?php

namespace Arm092\Translation;

use Arm092\Translation\Drivers\Translation;
use Illuminate\Translation\TranslationServiceProvider as ServiceProvider;

class TranslationBindingsServiceProvider extends ServiceProvider
{
    /**
     * Register package bindings in the container.
     */
    public function register(): void
    {
        if ($this->app['config']['translation.driver'] !== 'database') {
            if (! $this->app->bound('translator')) {
                parent::register();
            }

            return;
        }

        parent::register();
        $this->registerDatabaseLoader();
    }

    protected function registerDatabaseLoader(): void
    {
        $this->app->extend('translation.loader', function ($loader, $app) {
            return new ContractDatabaseLoader($app->make(Translation::class), $loader);
        });
    }
}
