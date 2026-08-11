<?php

namespace Arm092\Translation;

use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\Arr;
use Arm092\Translation\Drivers\Translation;

class ContractDatabaseLoader implements Loader
{
    private $translation;

    private $fallback;

    public function __construct(Translation $translation, ?Loader $fallback = null)
    {
        $this->translation = $translation;
        $this->fallback = $fallback;
    }

    /**
     * Load the messages for the given locale.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     */
    public function load($locale, $group, $namespace = null)
    {
        $fallback = $this->fallback?->load($locale, $group, $namespace) ?? [];

        if ($group == '*' && $namespace == '*') {
            $database = $this->translation->getSingleTranslationsFor($locale)->get('single', collect())->toArray();

            return array_replace($fallback, $database);
        }

        if (is_null($namespace) || $namespace == '*') {
            $database = $this->translation->getGroupTranslationsFor($locale)->get($group, collect())->toArray();

            return array_replace_recursive($fallback, Arr::undot($database));
        }

        $database = $this->translation->getGroupTranslationsFor($locale)->get("{$namespace}::{$group}", collect())->toArray();

        return array_replace_recursive($fallback, Arr::undot($database));
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param  string  $namespace
     * @param  string  $hint
     * @return void
     */
    public function addNamespace($namespace, $hint)
    {
        $this->fallback?->addNamespace($namespace, $hint);
    }

    /**
     * Add a new JSON path to the loader.
     *
     * @param  string  $path
     * @return void
     */
    public function addJsonPath($path)
    {
        $this->fallback?->addJsonPath($path);
    }

    /**
     * Get an array of all the registered namespaces.
     *
     * @return array
     */
    public function namespaces()
    {
        return $this->fallback?->namespaces() ?? [];
    }
}
