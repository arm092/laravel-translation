<?php

namespace Arm092\Translation\Contracts;

interface TranslationImporter
{
    public function import(string $path, string $conflict = 'fail', bool $commit = false, bool $forceProtected = false): array;
}
