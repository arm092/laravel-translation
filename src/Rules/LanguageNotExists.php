<?php

namespace Arm092\Translation\Rules;

use Arm092\Translation\Drivers\Translation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LanguageNotExists implements ValidationRule
{
    /**
     * Determine if the validation rule passes.
     *
     * @return bool
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $translation = app()->make(Translation::class);

        if ($translation->languageExists($value)) {
            $fail('translation::translation.language_exists')->translate();
        }
    }
}
