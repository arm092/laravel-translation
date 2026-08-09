<?php

namespace Arm092\Translation\Support;

final class TranslationInputRules
{
    /**
     * Get the validation rules shared by HTTP and Livewire translation writes.
     *
     * @return array<string, array<int, string>>
     */
    public static function get(): array
    {
        return [
            'language' => ['required', 'string', 'max:35', 'regex:/\A[A-Za-z0-9]+(?:[-_][A-Za-z0-9]+)*\z/D'],
            'namespace' => ['nullable', 'string', 'max:100', 'regex:/\A[A-Za-z0-9_-]+\z/D'],
            'group' => ['nullable', 'string', 'max:100', 'regex:/\A[A-Za-z0-9_-]+(?:::[A-Za-z0-9_-]+)?\z/D'],
            'key' => ['required', 'string'],
            'value' => ['present', 'nullable', 'string'],
        ];
    }
}
