<?php

namespace Arm092\Translation\Support;

use Arm092\Translation\Drivers\Translation;

final class TranslationCache
{
    public function forget(Translation $translation, ?string $locale = null): void
    {
        $translation->forgetCachedTranslations($locale);

        if (! app()->bound('translator')) {
            return;
        }

        $translator = app('translator');

        if (method_exists($translator, 'setLoaded')) {
            $translator->setLoaded([]);
        }
    }
}
