<?php

use Arm092\Translation\Http\Controllers\LanguageController;
use Arm092\Translation\Http\Controllers\LanguageTranslationController;
use Arm092\Translation\Http\Controllers\QualityController;
use Arm092\Translation\Http\Middleware\AuthorizeTranslationManager;
use Illuminate\Support\Facades\Route;

$routeConfig = config('translation.route_group_config', []);
$middleware = (array) ($routeConfig['middleware'] ?? []);
$routeConfig['middleware'] = [...$middleware, AuthorizeTranslationManager::class];

Route::group($routeConfig, function () {
    Route::get(config('translation.ui_url'), [LanguageController::class, 'index'])
        ->name('languages.index');

    Route::get(config('translation.ui_url').'/create', [LanguageController::class, 'create'])
        ->name('languages.create');

    Route::post(config('translation.ui_url'), [LanguageController::class, 'store'])
        ->name('languages.store');

    Route::get(config('translation.ui_url').'/{language}/translations', [LanguageTranslationController::class, 'index'])
        ->name('languages.translations.index');

    Route::post(config('translation.ui_url').'/{language}', [LanguageTranslationController::class, 'update'])
        ->name('languages.translations.update');

    Route::get(config('translation.ui_url').'/{language}/translations/create', [LanguageTranslationController::class, 'create'])
        ->name('languages.translations.create');

    Route::post(config('translation.ui_url').'/{language}/translations', [LanguageTranslationController::class, 'store'])
        ->name('languages.translations.store');

    Route::get(config('translation.ui_url').'/quality/dashboard', [QualityController::class, 'index'])
        ->name('quality.index');
    Route::get(config('translation.ui_url').'/quality/export', [QualityController::class, 'export'])
        ->name('quality.export');
});
