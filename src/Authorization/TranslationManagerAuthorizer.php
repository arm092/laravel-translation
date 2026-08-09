<?php

namespace JoeDixon\Translation\Authorization;

use Illuminate\Support\Facades\Gate;

final class TranslationManagerAuthorizer
{
    public function authorize(): void
    {
        $gate = config('translation.authorization_gate');

        if ($gate === null) {
            return;
        }

        if (! is_string($gate) || $gate === '') {
            throw new \InvalidArgumentException('translation.authorization_gate must be a non-empty gate name or null.');
        }

        Gate::authorize($gate);
    }
}
