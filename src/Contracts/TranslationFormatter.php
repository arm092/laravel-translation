<?php

namespace Arm092\Translation\Contracts;

interface TranslationFormatter
{
    public function format(array $locales = [], bool $write = false, bool $forceProtected = false): array;
}
