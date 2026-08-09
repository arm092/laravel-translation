<?php

namespace Arm092\Translation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Arm092\Translation\Drivers\Translation;

class LanguageNotExists implements ValidationRule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
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
