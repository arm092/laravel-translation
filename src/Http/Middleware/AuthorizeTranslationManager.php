<?php

namespace JoeDixon\Translation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JoeDixon\Translation\Authorization\TranslationManagerAuthorizer;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeTranslationManager
{
    public function __construct(private TranslationManagerAuthorizer $authorizer)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->authorizer->authorize();

        return $next($request);
    }
}
