<?php

namespace Arm092\Translation\Tests;

use Illuminate\Support\Facades\Route;
use Arm092\Translation\TranslationBindingsServiceProvider;
use Arm092\Translation\TranslationServiceProvider;
use Orchestra\Testbench\TestCase;

class RouteConfigurationTest extends TestCase
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
        $app['config']->set('translation.route_group_config', [
            'middleware' => ['web'],
            'as' => 'translation.',
        ]);
    }

    public function test_route_name_prefix_is_used_by_routes_and_package_views(): void
    {
        $this->assertTrue(Route::has('translation.languages.index'));
        $this->assertFalse(Route::has('languages.index'));

        $this->get(config('translation.ui_url'))
            ->assertOk()
            ->assertSee(route('translation.languages.create'), false)
            ->assertSee(route('translation.languages.index'), false);
    }
}
