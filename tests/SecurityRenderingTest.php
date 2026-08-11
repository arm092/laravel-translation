<?php

namespace Arm092\Translation\Tests;

use Mockery;
use Illuminate\Translation\Translator as LaravelTranslator;
use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Support\TranslationCache;
use Arm092\Translation\TranslationBindingsServiceProvider;
use Arm092\Translation\TranslationServiceProvider;
use Orchestra\Testbench\TestCase;

class SecurityRenderingTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            TranslationServiceProvider::class,
            TranslationBindingsServiceProvider::class,
        ];
    }

    public function test_fallback_editor_escapes_template_and_html_payloads(): void
    {
        $payload = '{{ alert("template") }}<script>alert("html")</script>" onfocus="alert(1)';
        $html = view('translation::components.translation-input', [
            'initialTranslation' => $payload,
            'language' => 'en',
            'group' => 'messages',
            'translationKey' => 'unsafe',
            'endpoint' => '/languages/en',
        ])->render();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('data-value="'.$payload.'"', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('&quot; onfocus=&quot;', $html);
    }

    public function test_translation_cache_forgets_driver_and_framework_loaded_values(): void
    {
        $driver = new class extends Translation
        {
            public ?string $forgottenLocale = null;

            public function forgetCachedTranslations(?string $locale = null): void
            {
                $this->forgottenLocale = $locale;
            }
        };
        $translator = Mockery::mock(LaravelTranslator::class);
        $translator->shouldReceive('setLoaded')->once()->with([]);
        $this->app->instance('translator', $translator);

        $this->app->make(TranslationCache::class)->forget($driver, 'en');

        $this->assertSame('en', $driver->forgottenLocale);
    }
}
