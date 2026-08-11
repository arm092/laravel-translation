<?php

namespace Arm092\Translation\Scanning;

final class DynamicTranslationUsage
{
    public function __construct(
        public readonly string $expression,
        public readonly string $file,
        public readonly int $line,
        public readonly bool $ambiguous = false,
    ) {}

    public function toArray(): array
    {
        return ['expression' => $this->expression, 'file' => $this->file, 'line' => $this->line, 'ambiguous' => $this->ambiguous];
    }
}
