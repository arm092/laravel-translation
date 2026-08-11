<?php

namespace Arm092\Translation\Support;

final class SourceLocale
{
    public function get(): string
    {
        return (string) (config('translation.source_locale') ?: config('app.locale'));
    }
}
