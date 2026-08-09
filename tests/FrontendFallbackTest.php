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

    public function test_default_manager_uses_top_navigation_without_a_sidebar(): void
    {
        $this->get(config('translation.ui_url'))
            ->assertOk()
            ->assertSee('class="header"', false)
            ->assertSee('class="app-main"', false)
            ->assertDontSee('class="sidebar"', false);
    }

    public function test_select_partial_renders_one_custom_caret(): void
    {
        $html = view('translation::forms.select', [
            'name' => 'language',
            'items' => ['en' => 'English'],
            'selected' => 'en',
        ])->render();

        $this->assertSame(1, substr_count($html, '<select'));
        $this->assertSame(1, substr_count($html, 'class="caret"'));
        $this->assertStringContainsString('aria-label="Language"', $html);
    }
}
