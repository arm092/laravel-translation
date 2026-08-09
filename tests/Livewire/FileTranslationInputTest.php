<?php

namespace Arm092\Translation\Tests\Livewire;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Events\TranslationAdded;
use Arm092\Translation\Livewire\TranslationInput;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

class FileTranslationInputTest extends LivewireTestCase
{
    private string $languagePath;

    protected function setUp(): void
    {
        $this->languagePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-translation-livewire-'.bin2hex(random_bytes(8));

        parent::setUp();

        $filesystem = new Filesystem();
        $filesystem->ensureDirectoryExists($this->languagePath.'/en');
        $filesystem->ensureDirectoryExists($this->languagePath.'/es');
        $filesystem->ensureDirectoryExists($this->languagePath.'/vendor/translation_test');
        $filesystem->put($this->languagePath.'/en/messages.php', "<?php\n\nreturn ['hello' => 'Hello'];\n");
        $filesystem->put($this->languagePath.'/es/messages.php', "<?php\n\nreturn ['hello' => 'Hola'];\n");
        $filesystem->put($this->languagePath.'/vendor/translation_test/es.json', '{"Hello":"Hola vendor"}');

        $this->app['path.lang'] = $this->languagePath;
        $this->app->forgetInstance(Translation::class);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->deleteDirectory($this->languagePath);

        parent::tearDown();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('translation.driver', 'file');
    }

    public function test_a_changed_translation_is_saved_and_dispatches_the_existing_event(): void
    {
        Event::fake();

        Livewire::test(TranslationInput::class, $this->parameters())
            ->set('value', 'Buenos días')
            ->call('save')
            ->assertSet('persistedValue', 'Buenos días')
            ->assertSet('status', 'saved');

        $translation = $this->app->make(Translation::class);

        $this->assertSame('Buenos días', $translation->getGroupTranslationsFor('es')->get('messages')->get('hello'));
        Event::assertDispatched(TranslationAdded::class, fn (TranslationAdded $event) => $event->language === 'es'
            && $event->group === 'messages'
            && $event->key === 'hello'
            && $event->value === 'Buenos días');
    }

    public function test_an_unchanged_translation_is_not_written(): void
    {
        Event::fake();

        Livewire::test(TranslationInput::class, $this->parameters())
            ->call('save')
            ->assertSet('status', 'idle');

        Event::assertNotDispatched(TranslationAdded::class);
    }

    public function test_locked_translation_identifiers_cannot_be_changed_by_the_client(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(TranslationInput::class, $this->parameters())
            ->set('language', '../outside');
    }

    public function test_invalid_groups_are_rejected_during_mount(): void
    {
        Livewire::test(TranslationInput::class, [
            ...$this->parameters(),
            'group' => '../outside',
        ])->assertHasErrors(['group']);

        Livewire::test(TranslationInput::class, [
            ...$this->parameters(),
            'language' => '../outside',
        ])->assertHasErrors(['language']);
    }

    public function test_gate_is_checked_during_mount(): void
    {
        config()->set('translation.authorization_gate', 'manage-translations');
        Gate::define('manage-translations', fn (): bool => false);

        $this->expectException(AuthorizationException::class);

        (new TranslationInput())->mount(...array_values($this->parameters()));
    }

    public function test_gate_is_checked_during_each_save(): void
    {
        config()->set('translation.authorization_gate', 'manage-translations');
        $allowed = true;
        Gate::define('manage-translations', function ($user = null) use (&$allowed): bool {
            return $allowed;
        });

        $component = new TranslationInput();
        $component->mount(...array_values($this->parameters()));
        $component->value = 'Denied';
        $allowed = false;

        $this->expectException(AuthorizationException::class);

        $component->save();
    }

    public function test_null_gate_preserves_unrestricted_livewire_saves(): void
    {
        config()->set('translation.authorization_gate', null);

        Livewire::test(TranslationInput::class, $this->parameters())
            ->set('value', 'Allowed')
            ->call('save')
            ->assertSet('status', 'saved');
    }

    public function test_named_gate_allows_livewire_saves_when_authorized(): void
    {
        config()->set('translation.authorization_gate', 'manage-translations');
        Gate::define('manage-translations', fn ($user = null): bool => true);

        Livewire::test(TranslationInput::class, $this->parameters())
            ->set('value', 'Allowed by gate')
            ->call('save')
            ->assertSet('status', 'saved');
    }

    public function test_write_failures_are_exposed_as_an_error_state(): void
    {
        $component = Livewire::test(TranslationInput::class, $this->parameters())
            ->set('value', 'Failure');

        config()->set('translation.driver', 'unsupported');
        $this->app->forgetInstance(Translation::class);

        $component->call('save')
            ->assertSet('status', 'error')
            ->assertSet('persistedValue', 'Hola');
    }

    public function test_vendor_namespaced_single_translations_keep_http_fallback_semantics(): void
    {
        Livewire::test(TranslationInput::class, [
            'initialTranslation' => 'Hola vendor',
            'language' => 'es',
            'group' => 'translation_test::single',
            'translationKey' => 'Hello',
        ])->set('value', 'Vendor JSON')->call('save')->assertSet('status', 'saved');

        $this->assertSame(
            'Vendor JSON',
            $this->app->make(Translation::class)
                ->getSingleTranslationsFor('es')
                ->get('translation_test::single')
                ->get('Hello')
        );
    }

    public function test_livewire_assets_are_auto_injected_when_the_translation_page_renders_a_component(): void
    {
        $this->get(config('translation.ui_url').'/es/translations')
            ->assertOk()
            ->assertSee('data-update-uri=', false)
            ->assertDontSee('/vendor/translation/js/app.js', false);
    }

    /**
     * @return array<string, string>
     */
    private function parameters(): array
    {
        return [
            'initialTranslation' => 'Hola',
            'language' => 'es',
            'group' => 'messages',
            'translationKey' => 'hello',
        ];
    }
}
