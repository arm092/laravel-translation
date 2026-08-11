<?php

namespace Arm092\Translation\Tests;

use Arm092\Translation\TranslationBindingsServiceProvider;
use Arm092\Translation\TranslationServiceProvider;
use Illuminate\Support\Facades\Gate;
use Orchestra\Testbench\TestCase;

class AuthorizationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            TranslationServiceProvider::class,
            TranslationBindingsServiceProvider::class,
        ];
    }

    public function test_null_gate_preserves_unrestricted_legacy_behavior()
    {
        config()->set('translation.authorization_gate', null);

        $this->get(config('translation.ui_url'))->assertOk();
    }

    public function test_configured_gate_allows_authorized_requests()
    {
        config()->set('translation.authorization_gate', 'manage-translations');
        Gate::define('manage-translations', fn ($user = null) => true);

        $this->get(config('translation.ui_url'))->assertOk();
    }

    public function test_configured_gate_denies_unauthorized_requests()
    {
        config()->set('translation.authorization_gate', 'manage-translations');
        Gate::define('manage-translations', fn ($user = null) => false);

        $this->get(config('translation.ui_url'))->assertForbidden();
    }

    public function test_route_middleware_accepts_a_string()
    {
        config()->set('translation.route_group_config.middleware', 'web');

        $this->get(config('translation.ui_url'))->assertOk();
    }
}
