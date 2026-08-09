<?php

namespace Arm092\Translation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Arm092\Translation\Authorization\TranslationManagerAuthorizer;
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
