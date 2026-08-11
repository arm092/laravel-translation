<?php

namespace Arm092\Translation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Support\RouteNames;
use Arm092\Translation\Http\Requests\LanguageRequest;

class LanguageController extends Controller
{
    private $translation;

    private $routeNames;

    public function __construct(Translation $translation, RouteNames $routeNames)
    {
        $this->translation = $translation;
        $this->routeNames = $routeNames;
    }

    public function index(Request $request)
    {
        $languages = $this->translation->allLanguages();

        return view('translation::languages.index', compact('languages'));
    }

    public function create()
    {
        return view('translation::languages.create');
    }

    public function store(LanguageRequest $request)
    {
        $this->translation->addLanguage($request->locale, $request->name);

        return redirect()
            ->route($this->routeNames->get('languages.index'))
            ->with('success', __('translation::translation.language_added'));
    }
}
