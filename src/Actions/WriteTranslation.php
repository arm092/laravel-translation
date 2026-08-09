<?php

namespace JoeDixon\Translation\Actions;

use Illuminate\Support\Facades\Event;
use JoeDixon\Translation\Drivers\Translation;
use JoeDixon\Translation\Events\TranslationAdded;

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

        Event::dispatch(new TranslationAdded($language, $resolvedGroup ?: 'single', $key, $resolvedValue));
    }
}
