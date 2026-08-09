<?php

namespace JoeDixon\Translation\Tests\Livewire;

use JoeDixon\Translation\Support\Frontend;
use JoeDixon\Translation\TranslationBindingsServiceProvider;
use JoeDixon\Translation\TranslationServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class LivewireTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue(
            $this->app->make(Frontend::class)->usesLivewire(),
            'The Livewire suite requires livewire/livewire:^4.0.'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            TranslationServiceProvider::class,
            TranslationBindingsServiceProvider::class,
        ];
    }
}
