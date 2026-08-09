<?php

namespace Arm092\Translation\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Arm092\Translation\Actions\WriteTranslation;

abstract class Translation
{
    /**
     * Find all of the translations in the app without translation for a given language.
     *
     * @param  string  $language
     * @return array
     */
    public function findMissingTranslations($language)
    {
        return $this->recursiveDifference(
            $this->scanner->findTranslations(),
            $this->allTranslationsFor($language)
        );
    }

    /**
     * Save all of the translations in the app without translation for a given language.
     *
     * @param  string  $language
     * @return void
     */
    public function saveMissingTranslations($language = false)
    {
        $languages = $language ? [$language => $language] : $this->allLanguages();

        foreach ($languages as $language => $name) {
            $missingTranslations = $this->findMissingTranslations($language);

            foreach ($missingTranslations as $type => $groups) {
                foreach ($groups as $group => $translations) {
                    foreach ($translations as $key => $value) {
                        if (Str::contains($group, 'single')) {
                            $this->addSingleTranslation($language, $group, $key);
                        } else {
                            $this->addGroupTranslation($language, $group, $key);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get all translations for a given language merged with the source language.
     *
     * @param  string  $language
     * @return Collection
     */
    public function getSourceLanguageTranslationsWith($language)
    {
        $sourceTranslations = $this->allTranslationsFor($this->sourceLanguage);
        $languageTranslations = $this->allTranslationsFor($language);

        return $sourceTranslations->map(function ($groups, $type) use ($language, $languageTranslations) {
            return $groups->map(function ($translations, $group) use ($type, $language, $languageTranslations) {
                $translations = $translations->toArray();
                array_walk($translations, function (&$value, $key) use ($type, $group, $language, $languageTranslations) {
                    $value = [
                        $this->sourceLanguage => $value,
                        $language => $languageTranslations->get($type, collect())->get($group, collect())->get($key),
                    ];
                });

                return $translations;
            });
        });
    }

    /**
     * Filter all keys and translations for a given language and string.
     *
     * @param  string  $language
     * @param  string  $filter
     * @return Collection
     */
    public function filterTranslationsFor($language, $filter)
    {
        $allTranslations = $this->getSourceLanguageTranslationsWith($language);
        if (! $filter) {
            return $allTranslations;
        }

        return $allTranslations->map(function ($groups, $type) use ($language, $filter) {
            return $groups->map(function ($keys, $group) use ($language, $filter) {
                return collect($keys)->filter(function ($translations, $key) use ($group, $language, $filter) {
                    return $this->stringsContain([$group, $key, $translations[$language], $translations[$this->sourceLanguage]], $filter);
                });
            })->filter(function ($keys) {
                return $keys->isNotEmpty();
            });
        });
    }

    public function add(Request $request, $language, $isGroupTranslation)
    {
        app(WriteTranslation::class)->handle(
            $this,
            $language,
            $request->get('namespace'),
            $request->get('group'),
            $request->get('key'),
            $request->get('value'),
            $isGroupTranslation,
        );
    }

    protected function assertValidLocale(string $locale): void
    {
        if (! preg_match('/\A[A-Za-z0-9]+(?:[-_][A-Za-z0-9]+)*\z/D', $locale)) {
            throw new \InvalidArgumentException("Invalid locale [$locale].");
        }
    }

    protected function assertValidGroup(string $group): void
    {
        $segments = explode('::', $group);

        if (count($segments) > 2) {
            throw new \InvalidArgumentException("Invalid translation group [$group].");
        }

        foreach ($segments as $segment) {
            if (! preg_match('/\A[A-Za-z0-9_-]+\z/D', $segment)) {
                throw new \InvalidArgumentException("Invalid translation group [$group].");
            }
        }
    }

    private function recursiveDifference(iterable $expected, iterable $actual): array
    {
        $actual = $actual instanceof Collection ? $actual->all() : (array) $actual;
        $difference = [];

        foreach ($expected as $key => $value) {
            if (is_iterable($value)) {
                if (! array_key_exists($key, $actual) || ! is_iterable($actual[$key])) {
                    $difference[$key] = $value;
                    continue;
                }

                $nested = $this->recursiveDifference($value, $actual[$key]);
                if ($nested !== []) {
                    $difference[$key] = $nested;
                }
            } elseif (! array_key_exists($key, $actual)) {
                $difference[$key] = $value;
            }
        }

        return $difference;
    }

    private function stringsContain(iterable $haystacks, string $needle): bool
    {
        foreach ($haystacks as $haystack) {
            if (is_iterable($haystack)) {
                if ($this->stringsContain($haystack, $needle)) {
                    return true;
                }
            } elseif (Str::contains(mb_strtolower((string) $haystack), mb_strtolower($needle))) {
                return true;
            }
        }

        return false;
    }
}
