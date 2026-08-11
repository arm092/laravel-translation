<?php

namespace Arm092\Translation\Contracts;

interface TranslationBatchWriter
{
    public function write(array $rows, string $conflict = 'overwrite', bool $forceProtected = false): int;
}
