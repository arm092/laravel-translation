<?php

namespace Arm092\Translation\Tests;

use Arm092\Translation\Support\Frontend;
use Arm092\Translation\TranslationBindingsServiceProvider;
use Arm092\Translation\TranslationServiceProvider;
use Orchestra\Testbench\TestCase;

class FrontendFallbackTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            TranslationServiceProvider::class,
            TranslationBindingsServiceProvider::class,
        ];
    }

    public function test_alpine_frontend_is_used_without_livewire_four(): void
    {
        if ($this->app->make(Frontend::class)->usesLivewire()) {
            $this->markTestSkipped('This assertion belongs to the fallback dependency matrix.');
        }

        $this->assertFalse($this->app->make(Frontend::class)->usesLivewire());

        $this->get(config('translation.ui_url'))
            ->assertOk()
            ->assertSee('/vendor/translation/js/app.js', false)
            ->assertDontSee('/livewire/livewire', false);
    }
}
