<?php

namespace Arm092\Translation\Actions;

use Illuminate\Support\Facades\Event;
use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Events\TranslationAdded;
use Arm092\Translation\Support\TranslationCache;

final class WriteTranslation
{
    public function handle(
        Translation $translation,
        string $language,
        ?string $namespace,
        ?string $group,
        string $key,
        ?string $value,
        bool $isGroupTranslation,
    ): void {
        $resolvedGroup = ($namespace ? $namespace.'::' : '').($group ?? '');
        $resolvedValue = $value ?? '';

        if ($isGroupTranslation) {
            $translation->addGroupTranslation($language, $resolvedGroup, $key, $resolvedValue);
        } else {
            $translation->addSingleTranslation($language, $resolvedGroup ?: 'single', $key, $resolvedValue);
        }

        app(TranslationCache::class)->forget($translation, $language);
        Event::dispatch(new TranslationAdded($language, $resolvedGroup ?: 'single', $key, $resolvedValue));
    }
}
