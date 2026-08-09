<?php

namespace JoeDixon\Translation\Tests\Livewire;

use Livewire\Livewire;

class FrontendSelectionTest extends LivewireTestCase
{
    public function test_livewire_assets_replace_the_package_alpine_bundle_on_every_manager_page(): void
    {
        $this->get(config('translation.ui_url'))
            ->assertOk()
            ->assertSee('livewire.min.js', false)
            ->assertSee('data-update-uri=', false)
            ->assertDontSee('/vendor/translation/js/app.js', false);
    }

    public function test_the_translation_input_alias_is_registered(): void
    {
        Livewire::test('translation-manager::translation-input', [
            'initialTranslation' => 'Hello',
            'language' => 'en',
            'group' => 'messages',
            'translationKey' => 'hello',
        ])->assertSet('value', 'Hello')
            ->assertSeeHtml('wire:blur="save"')
            ->assertSeeHtml('wire:loading')
            ->assertSeeHtml('aria-live="polite"');
    }
}
