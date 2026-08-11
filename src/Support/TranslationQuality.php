<?php

namespace Arm092\Translation\Support;

use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Scanner;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class TranslationQuality
{
    public function __construct(private Scanner $scanner, private Translation $translation) {}

    public function missing(string $locale): array
    {
        $stored = $this->storedKeys($locale);

        return array_values(array_filter($this->scanner->scan()->keys(), fn ($key) => ! in_array($key, $stored, true) && ! collect($stored)->contains(fn ($storedKey) => str_starts_with($storedKey, $key.'.'))));
    }

    public function unused(string $locale): array
    {
        $used = $this->scanner->scan()->keys();

        return array_values(array_filter($this->storedKeys($locale), fn ($key) => ! collect($used)->contains(fn ($usedKey) => $key === $usedKey || str_starts_with($key, $usedKey.'.'))));
    }

    public function storedKeys(string $locale): array
    {
        $translations = $this->translation->allTranslationsFor($locale);
        $keys = [];
        foreach ($translations->get('group', []) as $group => $values) {
            foreach (Arr::dot($values instanceof Collection ? $values->all() : $values) as $key => $value) {
                $keys[] = $group.'.'.$key;
            }
        }
        foreach ($translations->get('single', []) as $values) {
            foreach ($values as $key => $value) {
                $keys[] = $key;
            }
        }

        return array_values(array_unique($keys));
    }
}
