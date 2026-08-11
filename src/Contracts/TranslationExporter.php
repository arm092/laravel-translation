<?php

namespace Arm092\Translation\Contracts;

interface TranslationExporter
{
    public function export(string $path, array $locales = []): int;
}
