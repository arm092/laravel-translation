<?php

namespace Arm092\Translation\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ProtectedLocaleException extends HttpException
{
    public function __construct(string $locale)
    {
        parent::__construct(403, "Locale [$locale] is protected. Use --force-protected for an intentional CLI write.");
    }
}
