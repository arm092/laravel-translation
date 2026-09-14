<?php

namespace Arm092\Translation\Tests;

use Arm092\Translation\TranslationBindingsServiceProvider;
use Arm092\Translation\TranslationServiceProvider;
use Orchestra\Testbench\TestCase;

class RouteMiddlewareConfigurationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            TranslationServiceProvider::class,
            TranslationBindingsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('translation.route_group_config.middleware', 'web');
    }

    public function test_route_middleware_accepts_a_string(): void
    {
        $this->get(config('translation.ui_url'))->assertOk();
    }
}
