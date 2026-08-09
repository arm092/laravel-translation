<?php

namespace Arm092\Translation;

use Illuminate\Translation\TranslationServiceProvider as ServiceProvider;
use Illuminate\Translation\Translator;
use Arm092\Translation\Drivers\Translation;

class TranslationBindingsServiceProvider extends ServiceProvider
{
    /**
     * Register package bindings in the container.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->app['config']['translation.driver'] !== 'database') {
            if (! $this->app->bound('translator')) {
                parent::register();
            }

            return;
        }

        $this->registerDatabaseTranslator();
    }

    private function registerDatabaseTranslator(): void
    {
        $this->registerDatabaseLoader();

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];
            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app->getLocale();
            $trans = new Translator($loader, $locale);
            $trans->setFallback($app->getFallbackLocale());

            return $trans;
        });
    }

    protected function registerDatabaseLoader(): void
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new ContractDatabaseLoader($app->make(Translation::class));
        });
    }
}
