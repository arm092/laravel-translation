<?php

namespace Arm092\Translation\Scanning;

final class ScanResult
{
    /** @param TranslationOccurrence[] $occurrences @param DynamicTranslationUsage[] $dynamic */
    public function __construct(
        public readonly array $occurrences = [],
        public readonly array $dynamic = [],
    ) {}

    public function keys(): array
    {
        return array_values(array_unique(array_map(fn ($item) => $item->key, $this->occurrences)));
    }

    public function toArray(): array
    {
        return [
            'occurrences' => array_map(fn ($item) => $item->toArray(), $this->occurrences),
            'dynamic' => array_map(fn ($item) => $item->toArray(), $this->dynamic),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            array_map(fn ($item) => new TranslationOccurrence(...$item), $data['occurrences'] ?? []),
            array_map(fn ($item) => new DynamicTranslationUsage(...$item), $data['dynamic'] ?? []),
        );
    }
}
