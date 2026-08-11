<?php

namespace Arm092\Translation\Support;

use Arm092\Translation\Exceptions\ProtectedLocaleException;

final class ProtectedLocales
{
    public function contains(string $locale): bool
    {
        return in_array($locale, (array) config('translation.protected_locales', []), true);
    }

    public function authorize(string $locale, bool $force = false): void
    {
        if ($this->contains($locale) && ! $force) {
            throw new ProtectedLocaleException($locale);
        }
    }
}
