<?php

namespace JoeDixon\Translation\Support;

use Composer\InstalledVersions;

final class Frontend
{
    private ?bool $usesLivewire = null;

    public function usesLivewire(): bool
    {
        return $this->usesLivewire ??= $this->detectLivewireFour();
    }

    private function detectLivewireFour(): bool
    {
        if (! class_exists(\Livewire\Livewire::class)
            || ! InstalledVersions::isInstalled('livewire/livewire')
            || ! app()->bound('livewire')
            || ! app()->bound('livewire.finder')) {
            return false;
        }

        $version = InstalledVersions::getVersion('livewire/livewire');

        return $version !== null
            && version_compare($version, '4.0.0', '>=')
            && version_compare($version, '5.0.0', '<');
    }
}
