<?php

namespace Arm092\Translation\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Events\TranslationAdded;
use Arm092\Translation\Exceptions\LanguageExistsException;
use Arm092\Translation\Language;
use Arm092\Translation\Translation as TranslationModel;
use Arm092\Translation\TranslationBindingsServiceProvider;
use Arm092\Translation\TranslationServiceProvider;
use Orchestra\Testbench\TestCase;

class DatabaseDriverTest extends TestCase
{
    use DatabaseMigrations;

    private $translation;

    private int $sequence = 0;

    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->translation = $this->app[Translation::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('translation.driver', 'database');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            TranslationServiceProvider::class,
            TranslationBindingsServiceProvider::class,
        ];
    }
    public function test_it_returns_all_languages()
    {
        $newLanguages = $this->createLanguages(2);
        $newLanguages = $newLanguages->mapWithKeys(function ($language) {
            return [$language->language => $language->name];
        })->toArray();
        $languages = $this->translation->allLanguages();

        $this->assertEquals($languages->count(), 3);
        $this->assertEquals($languages->toArray(), ['en' => 'en'] + $newLanguages);
    }
    public function test_it_returns_all_translations()
    {
        $default = Language::where('language', config('app.locale'))->first();
        $this->createLanguage(['language' => 'es', 'name' => 'Español']);
        $this->createTranslation(['language_id' => $default->id, 'group' => 'test', 'key' => 'hello', 'value' => 'Hello']);
        $this->createTranslation(['language_id' => $default->id, 'group' => 'test', 'key' => 'whats_up', 'value' => "What's up!"]);
        $this->createTranslation(['language_id' => $default->id, 'group' => 'single', 'key' => 'Hello', 'value' => 'Hello']);
        $this->createTranslation(['language_id' => $default->id, 'group' => 'single', 'key' => "What's up", 'value' => "What's up!"]);

        $translations = $this->translation->allTranslations();

        $this->assertEquals($translations->count(), 2);
        $this->assertEquals(['single' => ['single' => ['Hello' => 'Hello', "What's up" => "What's up!"]], 'group' => ['test' => ['hello' => 'Hello', 'whats_up' => "What's up!"]]], $translations->toArray()['en']);
        $this->assertArrayHasKey('en', $translations->toArray());
        $this->assertArrayHasKey('es', $translations->toArray());
    }
    public function test_it_returns_all_translations_for_a_given_language()
    {
        $default = Language::where('language', config('app.locale'))->first();
        $this->createTranslation(['language_id' => $default->id, 'group' => 'test', 'key' => 'hello', 'value' => 'Hello']);
        $this->createTranslation(['language_id' => $default->id, 'group' => 'test', 'key' => 'whats_up', 'value' => "What's up!"]);
        $this->createTranslation(['language_id' => $default->id, 'group' => 'single', 'key' => 'Hello', 'value' => 'Hello']);
        $this->createTranslation(['language_id' => $default->id, 'group' => 'single', 'key' => "What's up", 'value' => "What's up!"]);

        $translations = $this->translation->allTranslationsFor('en');
        $this->assertEquals($translations->count(), 2);
        $this->assertEquals(['single' => ['single' => ['Hello' => 'Hello', "What's up" => "What's up!"]], 'group' => ['test' => ['hello' => 'Hello', 'whats_up' => "What's up!"]]], $translations->toArray());
        $this->assertArrayHasKey('single', $translations->toArray());
        $this->assertArrayHasKey('group', $translations->toArray());
    }
    public function test_it_throws_an_exception_if_a_language_exists()
    {
        $this->expectException(LanguageExistsException::class);
        $this->translation->addLanguage('en');
    }
    public function test_it_can_add_a_new_language()
    {
        $this->assertDatabaseMissing(config('translation.database.languages_table'), [
            'language' => 'fr',
            'name' => 'Français',
        ]);

        $this->translation->addLanguage('fr', 'Français');
        $this->assertDatabaseHas(config('translation.database.languages_table'), [
            'language' => 'fr',
            'name' => 'Français',
        ]);
    }
    public function test_it_can_add_a_new_translation_to_a_new_group()
    {
        $this->translation->addGroupTranslation('es', 'test', 'hello', 'Hola!');

        $translations = $this->translation->allTranslationsFor('es');

        $this->assertEquals(['test' => ['hello' => 'Hola!']], $translations->toArray()['group']);
    }
    public function test_it_can_add_a_new_translation_to_an_existing_translation_group()
    {
        $translation = $this->createTranslation();

        $this->translation->addGroupTranslation($translation->language->language, "{$translation->group}", 'test', 'Testing');

        $translations = $this->translation->allTranslationsFor($translation->language->language);
        $this->assertSame([$translation->group => [$translation->key => $translation->value, 'test' => 'Testing']], $translations->toArray()['group']);
    }
    public function test_it_can_add_a_new_single_translation()
    {
        $this->translation->addSingleTranslation('es', 'single', 'Hello', 'Hola!');

        $translations = $this->translation->allTranslationsFor('es');

        $this->assertEquals(['single' => ['Hello' => 'Hola!']], $translations->toArray()['single']);
    }
    public function test_it_can_add_a_new_single_translation_to_an_existing_language()
    {
        $translation = $this->createTranslation(['group' => 'single']);

        $this->translation->addSingleTranslation($translation->language->language, 'single', 'Test', 'Testing');

        $translations = $this->translation->allTranslationsFor($translation->language->language);

        $this->assertEquals(['single' => ['Test' => 'Testing', $translation->key => $translation->value]], $translations->toArray()['single']);
    }
    public function test_it_can_get_a_collection_of_group_names_for_a_given_language()
    {
        $language = $this->createLanguage(['language' => 'en']);
        $this->createTranslation([
            'language_id' => $language->id,
            'group' => 'test',
        ]);

        $groups = $this->translation->getGroupsFor('en');

        $this->assertEquals($groups->toArray(), ['test']);
    }
    public function test_it_can_merge_a_language_with_the_base_language()
    {
        $default = Language::where('language', config('app.locale'))->first();
        $this->createTranslation(['language_id' => $default->id, 'group' => 'test', 'key' => 'hello', 'value' => 'Hello']);
        $this->createTranslation(['language_id' => $default->id, 'group' => 'test', 'key' => 'whats_up', 'value' => "What's up!"]);
        $this->createTranslation(['language_id' => $default->id, 'group' => 'single', 'key' => 'Hello', 'value' => 'Hello']);
        $this->createTranslation(['language_id' => $default->id, 'group' => 'single', 'key' => "What's up", 'value' => "What's up!"]);

        $this->translation->addGroupTranslation('es', 'test', 'hello', 'Hola!');
        $translations = $this->translation->getSourceLanguageTranslationsWith('es');

        $this->assertEquals($translations->toArray(), [
            'group' => [
                'test' => [
                    'hello' => ['en' => 'Hello', 'es' => 'Hola!'],
                    'whats_up' => ['en' => "What's up!", 'es' => ''],
                ],
            ],
            'single' => [
                'single' => [
                    'Hello' => [
                        'en' => 'Hello',
                        'es' => '',
                    ],
                    "What's up" => [
                        'en' => "What's up!",
                        'es' => '',
                    ],
                ],
            ],
        ]);
    }
    public function test_it_can_add_a_vendor_namespaced_translations()
    {
        $this->translation->addGroupTranslation('es', 'translation_test::test', 'hello', 'Hola!');

        $this->assertEquals($this->translation->allTranslationsFor('es')->toArray(), [
            'group' => [
                'translation_test::test' => [
                    'hello' => 'Hola!',
                ],
            ],
            'single' => [],
        ]);
    }
    public function test_it_can_add_a_nested_translation()
    {
        $this->translation->addGroupTranslation('en', 'test', 'test.nested', 'Nested!');

        $this->assertEquals($this->translation->getGroupTranslationsFor('en')->toArray(), [
            'test' => [
                'test.nested' => 'Nested!',
            ],
        ]);
    }
    public function test_it_can_add_nested_vendor_namespaced_translations()
    {
        $this->translation->addGroupTranslation('es', 'translation_test::test', 'nested.hello', 'Hola!');

        $this->assertEquals($this->translation->allTranslationsFor('es')->toArray(), [
            'group' => [
                'translation_test::test' => [
                    'nested.hello' => 'Hola!',
                ],
            ],
            'single' => [],
        ]);
    }
    public function test_it_can_merge_a_namespaced_language_with_the_base_language()
    {
        $this->translation->addGroupTranslation('en', 'translation_test::test', 'hello', 'Hello');
        $this->translation->addGroupTranslation('es', 'translation_test::test', 'hello', 'Hola!');
        $translations = $this->translation->getSourceLanguageTranslationsWith('es');

        $this->assertEquals($translations->toArray(), [
            'group' => [
                'translation_test::test' => [
                    'hello' => ['en' => 'Hello', 'es' => 'Hola!'],
                ],
            ],
            'single' => [],
        ]);
    }
    public function test_a_list_of_languages_can_be_viewed()
    {
        $newLanguages = $this->createLanguages(2);
        $response = $this->get(config('translation.ui_url'));

        $response->assertSee(config('app.locale'));
        foreach ($newLanguages as $language) {
            $response->assertSee($language->language);
        }
    }
    public function test_the_language_creation_page_can_be_viewed()
    {
        $this->translation->addGroupTranslation(config('app.locale'), 'translation::translation', 'add_language', 'Add a new language');
        $this->get(config('translation.ui_url').'/create')
            ->assertSee('Add a new language');
    }
    public function test_a_language_can_be_added()
    {
        $this->post(config('translation.ui_url'), ['locale' => 'de'])
            ->assertRedirect();

        $this->assertDatabaseHas('languages', ['language' => 'de']);
    }
    public function test_a_list_of_translations_can_be_viewed()
    {
        $default = Language::where('language', config('app.locale'))->first();
        $this->createTranslation(['language_id' => $default->id, 'group' => 'test', 'key' => 'hello', 'value' => 'Hello']);
        $this->createTranslation(['language_id' => $default->id, 'group' => 'test', 'key' => 'whats_up', 'value' => "What's up!"]);
        $this->createTranslation(['language_id' => $default->id, 'key' => 'Hello', 'value' => 'Hello!']);
        $this->createTranslation(['language_id' => $default->id, 'key' => "What's up", 'value' => 'Sup!']);

        $this->get(config('translation.ui_url').'/en/translations')
            ->assertSee('hello')
            ->assertSee('whats_up')
            ->assertSee('Hello')
            ->assertSee('Sup!');
    }
    public function test_the_translation_creation_page_can_be_viewed()
    {
        $this->translation->addGroupTranslation('en', 'translation::translation', 'add_translation', 'Add a translation');
        $this->get(config('translation.ui_url').'/'.config('app.locale').'/translations/create')
            ->assertSee('Add a translation');
    }
    public function test_a_new_translation_can_be_added()
    {
        $this->post(config('translation.ui_url').'/'.config('app.locale').'/translations', ['group' => 'single', 'key' => 'joe', 'value' => 'is cool'])
            ->assertRedirect();

        $this->assertDatabaseHas('translations', ['language_id' => 1, 'key' => 'joe', 'value' => 'is cool']);
    }
    public function test_a_translation_can_be_updated()
    {
        $default = Language::where('language', config('app.locale'))->first();
        $this->createTranslation(['language_id' => $default->id, 'group' => 'test', 'key' => 'hello', 'value' => 'Hello']);
        $this->assertDatabaseHas('translations', ['language_id' => 1, 'group' => 'test', 'key' => 'hello', 'value' => 'Hello']);

        $this->post(config('translation.ui_url').'/en', ['group' => 'test', 'key' => 'hello', 'value' => 'Hello there!'])
            ->assertStatus(200);

        $this->assertDatabaseHas('translations', ['language_id' => 1, 'group' => 'test', 'key' => 'hello', 'value' => 'Hello there!']);
    }
    public function test_adding_a_translation_fires_an_event_with_the_expected_data()
    {
        Event::fake();

        $data = ['key' => 'joe', 'value' => 'is cool'];
        $this->post(config('translation.ui_url').'/en/translations', $data);

        Event::assertDispatched(TranslationAdded::class, function ($event) use ($data) {
            return $event->language === 'en' &&
                    $event->group === 'single' &&
                    $event->value === $data['value'] &&
                    $event->key === $data['key'];
        });
    }
    public function test_updating_a_translation_fires_an_event_with_the_expected_data()
    {
        Event::fake();

        $data = ['group' => 'test', 'key' => 'hello', 'value' => 'Hello there!'];
        $this->post(config('translation.ui_url').'/en/translations', $data);

        Event::assertDispatched(TranslationAdded::class, function ($event) use ($data) {
            return $event->language === 'en' &&
                    $event->group === $data['group'] &&
                    $event->value === $data['value'] &&
                    $event->key === $data['key'];
        });
    }
    public function test_legacy_single_translations_are_isolated_to_the_requested_language()
    {
        $english = Language::where('language', 'en')->firstOrFail();
        $spanish = $this->createLanguage(['language' => 'es', 'name' => 'Español']);
        $this->createTranslation(['language_id' => $english->id, 'group' => null, 'key' => 'Hello', 'value' => 'Hello']);
        $this->createTranslation(['language_id' => $spanish->id, 'group' => null, 'key' => 'Hello', 'value' => 'Hola']);

        $translations = $this->translation->getSingleTranslationsFor('en');

        $this->assertSame('Hello', $translations->get('single')->get('Hello'));
        $this->assertDatabaseHas('translations', ['language_id' => $spanish->id, 'group' => null, 'value' => 'Hola']);
    }
    public function test_group_cache_is_invalidated_after_a_write()
    {
        $this->translation->addGroupTranslation('en', 'messages', 'first', 'First');
        $this->assertSame('First', $this->translation->getGroupTranslationsFor('en')->get('messages')->get('first'));

        $this->translation->addGroupTranslation('en', 'messages', 'second', 'Second');

        $this->assertSame('Second', $this->translation->getGroupTranslationsFor('en')->get('messages')->get('second'));
    }

    private function createLanguages(int $count): Collection
    {
        return collect(range(1, $count))->map(fn () => $this->createLanguage());
    }

    private function createLanguage(array $attributes = []): Language
    {
        $this->sequence++;

        return Language::create(array_merge([
            'language' => 'language-'.$this->sequence,
            'name' => 'Language '.$this->sequence,
        ], $attributes));
    }

    private function createTranslation(array $attributes = []): TranslationModel
    {
        $this->sequence++;
        $language = Language::query()->first() ?? $this->createLanguage();

        return TranslationModel::create(array_merge([
            'language_id' => $language->id,
            'group' => 'group-'.$this->sequence,
            'key' => 'key-'.$this->sequence,
            'value' => 'Value '.$this->sequence,
        ], $attributes));
    }
}
