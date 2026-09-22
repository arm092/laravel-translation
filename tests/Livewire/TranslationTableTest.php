<?php

namespace Arm092\Translation\Tests\Livewire;

use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Livewire\TranslationTable;
use Illuminate\Filesystem\Filesystem;
use Livewire\Livewire;

class TranslationTableTest extends LivewireTestCase
{
    private string $languagePath;

    protected function setUp(): void
    {
        $this->languagePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-translation-table-'.bin2hex(random_bytes(8));

        parent::setUp();

        $filesystem = new Filesystem;
        $filesystem->ensureDirectoryExists($this->languagePath.'/en');
        $filesystem->ensureDirectoryExists($this->languagePath.'/es');
        $filesystem->put($this->languagePath.'/en/messages.php', "<?php\n\nreturn ['hello' => 'Hello', 'welcome' => 'Welcome'];\n");
        $filesystem->put($this->languagePath.'/es/messages.php', "<?php\n\nreturn ['hello' => 'Hola', 'welcome' => 'Bienvenido'];\n");

        $this->app['path.lang'] = $this->languagePath;
        $this->app->forgetInstance(Translation::class);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->languagePath);

        parent::tearDown();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('translation.driver', 'file');
        $app['config']->set('app.locale', 'en');
    }

    public function test_search_filters_the_livewire_table_without_page_navigation(): void
    {
        Livewire::test(TranslationTable::class, ['language' => 'es'])
            ->assertSeeHtml('wire:model.live.debounce.300ms="filter"')
            ->assertSee('hello')
            ->assertSee('welcome')
            ->set('filter', 'welcome')
            ->assertDontSee('hello')
            ->assertSee('welcome');
    }

    public function test_group_and_page_size_filters_are_livewire_properties(): void
    {
        Livewire::test(TranslationTable::class, ['language' => 'es'])
            ->assertSeeHtml('wire:model.live="group"')
            ->assertSeeHtml('wire:model.live="perPage"');
    }
}
