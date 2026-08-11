<?php

namespace Arm092\Translation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Http\Requests\TranslationRequest;
use Arm092\Translation\Support\RouteNames;
use Arm092\Translation\Support\SourceLocale;

class LanguageTranslationController extends Controller
{
    private $translation;

    private $routeNames;

    private $sourceLocale;

    public function __construct(Translation $translation, RouteNames $routeNames, SourceLocale $sourceLocale)
    {
        $this->translation = $translation;
        $this->routeNames = $routeNames;
        $this->sourceLocale = $sourceLocale;
    }

    public function index(Request $request, $language)
    {
        if ($request->has('language') && $request->get('language') !== $language) {
            return redirect()
                ->route($this->routeNames->get('languages.translations.index'), ['language' => $request->get('language'), 'group' => $request->get('group'), 'filter' => $request->get('filter')]);
        }

        $sourceLocale = $this->sourceLocale->get();
        $languages = $this->translation->allLanguages();
        $groups = $this->translation->getGroupsFor($sourceLocale)->merge('single');
        $translations = $this->translation->filterTranslationsFor($language, $request->get('filter'));

        if ($request->has('group') && $request->get('group')) {
            if ($request->get('group') === 'single') {
                $translations = $translations->get('single');
                $translations = new Collection(['single' => $translations]);
            } else {
                $translations = $translations->get('group')->filter(function ($values, $group) use ($request) {
                    return $group === $request->get('group');
                });

                $translations = new Collection(['group' => $translations]);
            }
        }

        return view('translation::languages.translations.index', compact('language', 'languages', 'groups', 'translations', 'sourceLocale'));
    }

    public function create(Request $request, $language)
    {
        return view('translation::languages.translations.create', compact('language'));
    }

    public function store(TranslationRequest $request, $language)
    {
        $isGroupTranslation = $request->filled('group');

        $this->translation->add($request, $language, $isGroupTranslation);

        return redirect()
            ->route($this->routeNames->get('languages.translations.index'), $language)
            ->with('success', __('translation::translation.translation_added'));
    }

    public function update(TranslationRequest $request, $language)
    {
        $isGroupTranslation = ! Str::contains($request->get('group'), 'single');

        $this->translation->add($request, $language, $isGroupTranslation);

        return ['success' => true];
    }
}
