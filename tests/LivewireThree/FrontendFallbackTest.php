<?php

namespace Arm092\Translation\Tests\LivewireThree;

use Composer\InstalledVersions;
use Arm092\Translation\Support\Frontend;
use Arm092\Translation\TranslationBindingsServiceProvider;
use Arm092\Translation\TranslationServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase;

class FrontendFallbackTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            TranslationServiceProvider::class,
            TranslationBindingsServiceProvider::class,
        ];
    }

    public function test_livewire_three_uses_the_alpine_and_fetch_fallback(): void
    {
        $version = InstalledVersions::getVersion('livewire/livewire');

        $this->assertNotNull($version);
        $this->assertTrue(version_compare($version, '3.0.0', '>='));
        $this->assertTrue(version_compare($version, '4.0.0', '<'));
        $this->assertFalse($this->app->make(Frontend::class)->usesLivewire());

        $this->get(config('translation.ui_url'))
            ->assertOk()
            ->assertSee('/vendor/translation/js/app.js', false)
            ->assertDontSee('<!-- Livewire Scripts -->', false);
    }
}
