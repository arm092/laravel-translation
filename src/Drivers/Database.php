<?php

namespace Arm092\Translation\Drivers;

use Illuminate\Support\Collection;
use Arm092\Translation\Exceptions\LanguageExistsException;
use Arm092\Translation\Language;
use Arm092\Translation\Translation as TranslationModel;
use Throwable;

class Database extends Translation implements DriverInterface
{
    protected $sourceLanguage;

    protected $scanner;

    protected array $groupTranslationCache = [];

    protected array $languageCache = [];

    public function __construct($sourceLanguage, $scanner)
    {
        $this->sourceLanguage = $sourceLanguage;
        $this->scanner = $scanner;
    }

    /**
     * Get all languages from the application.
     *
     * @return Collection
     */
    public function allLanguages()
    {
        return Language::all()->mapWithKeys(function ($language) {
            return [$language->language => $language->name ?: $language->language];
        });
    }

    /**
     * Get all group translations from the application.
     *
     * @return array
     */
    public function allGroup($language)
    {
        $groups = TranslationModel::getGroupsForLanguage($language);

        return $groups->map(function ($translation) {
            return $translation->group;
        });
    }

    /**
     * Get all the translations from the application.
     *
     * @return Collection
     */
    public function allTranslations()
    {
        return $this->allLanguages()->mapWithKeys(function ($name, $language) {
            return [$language => $this->allTranslationsFor($language)];
        });
    }

    /**
     * Get all translations for a particular language.
     *
     * @param  string  $language
     * @return Collection
     */
    public function allTranslationsFor($language)
    {
        return Collection::make([
            'group' => $this->getGroupTranslationsFor($language),
            'single' => $this->getSingleTranslationsFor($language),
        ]);
    }

    /**
     * Add a new language to the application.
     *
     * @param  string  $language
     * @return void
     */
    public function addLanguage($language, $name = null)
    {
        $this->assertValidLocale($language);
        if ($this->languageExists($language)) {
            throw new LanguageExistsException(__('translation::errors.language_exists', ['language' => $language]));
        }

        $this->languageCache[$language] = Language::create([
            'language' => $language,
            'name' => $name,
        ]);
    }

    /**
     * Add a new group type translation.
     *
     * @param  string  $language
     * @param  string  $key
     * @param  string  $value
     * @return void
     */
    public function addGroupTranslation($language, $group, $key, $value = '')
    {
        $this->assertValidLocale($language);
        $this->assertValidGroup($group);
        if (! $this->languageExists($language)) {
            $this->addLanguage($language);
        }

        $this->getLanguage($language)
            ->translations()
            ->updateOrCreate([
                'group' => $group,
                'key' => $key,
            ], [
                'group' => $group,
                'key' => $key,
                'value' => $value,
            ]);

        unset($this->groupTranslationCache[$language]);
    }

    /**
     * Add a new single type translation.
     *
     * @param  string  $language
     * @param  string  $key
     * @param  string  $value
     * @return void
     */
    public function addSingleTranslation($language, $vendor, $key, $value = '')
    {
        $this->assertValidLocale($language);
        $this->assertValidGroup($vendor);
        if (! $this->languageExists($language)) {
            $this->addLanguage($language);
        }

        $this->getLanguage($language)
            ->translations()
            ->updateOrCreate([
                'group' => $vendor,
                'key' => $key,
            ], [
                'key' => $key,
                'value' => $value,
            ]);
    }

    /**
     * Get all of the single translations for a given language.
     *
     * @param  string  $language
     * @return Collection
     */
    public function getSingleTranslationsFor($language)
    {
        $languageModel = $this->getLanguage($language);

        if ($languageModel === null) {
            return collect();
        }

        $translations = $languageModel->translations()
            ->where(function ($query) {
                $query->where('group', 'like', '%single')
                    ->orWhereNull('group');
            })
            ->get()
            ->groupBy('group');

        // if there is no group, this is a legacy translation so we need to
        // update to 'single'. We do this here so it only happens once.
        if ($this->hasLegacyGroups($translations->keys())) {
            $languageModel->translations()->whereNull('group')->update(['group' => 'single']);
            // if any legacy groups exist, rerun the method so we get the
            // updated keys.
            return $this->getSingleTranslationsFor($language);
        }

        return $translations->map(function ($translations, $group) {
            return $translations->mapWithKeys(function ($translation) {
                return [$translation->key => $translation->value];
            });
        });
    }

    /**
     * Get all of the group translations for a given language.
     *
     * @param  string  $language
     * @return Collection
     */
    public function getGroupTranslationsFor($language)
    {
        if (isset($this->groupTranslationCache[$language])) {
            return $this->groupTranslationCache[$language];
        }

        $languageModel = $this->getLanguage($language);

        if (is_null($languageModel)) {
            return collect();
        }

        $translations = $languageModel
            ->translations()
            ->whereNotNull('group')
            ->where('group', 'not like', '%single')
            ->get()
            ->groupBy('group');

        $result = $translations->map(function ($translations) {
            return $translations->mapWithKeys(function ($translation) {
                return [$translation->key => $translation->value];
            });
        });

        $this->groupTranslationCache[$language] = $result;

        return $result;
    }

    /**
     * Determine whether or not a language exists.
     *
     * @param  string  $language
     * @return bool
     */
    public function languageExists($language)
    {
        return $this->getLanguage($language) ? true : false;
    }

    /**
     * Get a collection of group names for a given language.
     *
     * @param  string  $language
     * @return Collection
     */
    public function getGroupsFor($language)
    {
        return $this->allGroup($language);
    }

    /**
     * Get a language from the database.
     *
     * @param  string  $language
     * @return Language
     */
    private function getLanguage($language)
    {
        if (array_key_exists($language, $this->languageCache)) {
            return $this->languageCache[$language];
        }

        // Some constallation of composer packages can lead to our code being executed
        // as a dependency of running migrations. That's why we need to be able to
        // handle the case where the database is empty / our tables don't exist:
        try {
            $result = Language::where('language', $language)->first();
        } catch (Throwable) {
            $result = null;
        }

        $this->languageCache[$language] = $result;

        return $result;
    }

    /**
     * Determine if a set of single translations contains any legacy groups.
     * Previously, this was handled by setting the group value to NULL, now
     * we use 'single' to cater for vendor JSON language files.
     *
     * @param  Collection  $groups
     * @return bool
     */
    private function hasLegacyGroups($groups)
    {
        return $groups->filter(function ($key) {
            return $key === '';
        })->count() > 0;
    }
}
