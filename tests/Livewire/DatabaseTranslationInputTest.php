<?php

namespace JoeDixon\Translation\Tests\Livewire;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use JoeDixon\Translation\Language;
use JoeDixon\Translation\Livewire\TranslationInput;
use JoeDixon\Translation\Translation;
use Livewire\Livewire;

class DatabaseTranslationInputTest extends LivewireTestCase
{
    use DatabaseMigrations;

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('translation.driver', 'database');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    public function test_livewire_writes_are_language_scoped_and_invalidate_the_group_cache(): void
    {
        $english = Language::query()->where('language', 'en')->firstOrFail();
        $spanish = Language::query()->create(['language' => 'es', 'name' => 'Español']);

        Translation::query()->create([
            'language_id' => $english->id,
            'group' => 'messages',
            'key' => 'hello',
            'value' => 'Hello',
        ]);
        Translation::query()->create([
            'language_id' => $spanish->id,
            'group' => 'messages',
            'key' => 'hello',
            'value' => 'Hola',
        ]);

        $driver = $this->app->make(\JoeDixon\Translation\Drivers\Translation::class);
        $this->assertSame('Hola', $driver->getGroupTranslationsFor('es')->get('messages')->get('hello'));

        Livewire::test(TranslationInput::class, [
            'initialTranslation' => 'Hola',
            'language' => 'es',
            'group' => 'messages',
            'translationKey' => 'hello',
        ])->set('value', 'Buenos días')->call('save')->assertSet('status', 'saved');

        $this->assertDatabaseHas('translations', [
            'language_id' => $english->id,
            'group' => 'messages',
            'key' => 'hello',
            'value' => 'Hello',
        ]);
        $this->assertDatabaseHas('translations', [
            'language_id' => $spanish->id,
            'group' => 'messages',
            'key' => 'hello',
            'value' => 'Buenos días',
        ]);
        $this->assertSame('Buenos días', $driver->getGroupTranslationsFor('es')->get('messages')->get('hello'));
    }
}
