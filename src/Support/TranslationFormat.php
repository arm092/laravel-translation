<?php

namespace Arm092\Translation\Support;

use Arm092\Translation\Contracts\TranslationBatchWriter;
use Arm092\Translation\Contracts\TranslationFormatter;
use Arm092\Translation\Drivers\Translation;

final class TranslationFormat implements TranslationFormatter
{
    public function __construct(private Translation $driver, private TranslationBatchWriter $writer) {}

    public function format(array $locales = [], bool $write = false, bool $forceProtected = false): array
    {
        $locales = $locales ?: array_keys($this->driver->allLanguages()->all());
        $rows = [];
        foreach ($locales as $locale) {
            foreach ($this->driver->allTranslationsFor($locale) as $type => $groups) {
                foreach ($groups as $group => $values) {
                    foreach ($values as $key => $value) {
                        $rows[] = compact('locale', 'type', 'group', 'key', 'value');
                    }
                }
            }
        }
        usort($rows, fn ($a, $b) => strnatcasecmp(implode('.', [$a['locale'], $a['type'], $a['group'], $a['key']]), implode('.', [$b['locale'], $b['type'], $b['group'], $b['key']])));
        if ($write) {
            $this->writer->write($rows, 'overwrite', $forceProtected);
        }

        return ['translations' => count($rows), 'written' => $write];
    }
}
