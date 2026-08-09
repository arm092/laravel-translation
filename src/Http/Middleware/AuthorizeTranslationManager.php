<?php

namespace JoeDixon\Translation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeTranslationManager
{
    public function handle(Request $request, Closure $next): Response
    {
        $gate = config('translation.authorization_gate');

        if ($gate !== null) {
            if (! is_string($gate) || $gate === '') {
                throw new \InvalidArgumentException('translation.authorization_gate must be a non-empty gate name or null.');
            }

            Gate::authorize($gate);
        }

        return $next($request);
    }
}
