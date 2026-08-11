<?php

namespace Arm092\Translation\Scanning;

final class TranslationOccurrence
{
    public function __construct(
        public readonly string $key,
        public readonly string $file,
        public readonly int $line,
        public readonly int $count = 1,
    ) {}

    public function toArray(): array
    {
        return ['key' => $this->key, 'file' => $this->file, 'line' => $this->line, 'count' => $this->count];
    }
}
