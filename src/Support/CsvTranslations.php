<?php

namespace Arm092\Translation\Support;

use Arm092\Translation\Contracts\TranslationBatchWriter;
use Arm092\Translation\Contracts\TranslationExporter;
use Arm092\Translation\Contracts\TranslationImporter;
use Arm092\Translation\Drivers\Translation;
use Illuminate\Filesystem\Filesystem;

final class CsvTranslations implements TranslationExporter, TranslationImporter
{
    public function __construct(private Translation $driver, private TranslationBatchWriter $writer, private Filesystem $files) {}

    public function export(string $path, array $locales = []): int
    {
        $locales = $locales ?: array_keys($this->driver->allLanguages()->all());
        $records = [];
        foreach ($locales as $locale) {
            foreach ($this->driver->allTranslationsFor($locale) as $type => $groups) {
                foreach ($groups as $group => $values) {
                    foreach ($values as $key => $value) {
                        $namespace = str_contains($group, '::') ? strstr($group, '::', true) : '';
                        $plainGroup = str_contains($group, '::') ? substr(strstr($group, '::'), 2) : $group;
                        $id = implode("\0", [$type, $namespace, $plainGroup, $key]);
                        $records[$id] ??= ['type' => $type, 'namespace' => $namespace, 'group' => $plainGroup, 'key' => $key];
                        $records[$id][$locale] = $value;
                    }
                }
            }
        }
        ksort($records, SORT_NATURAL | SORT_FLAG_CASE);
        $this->files->ensureDirectoryExists(dirname($path));
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new \RuntimeException("Unable to write CSV [$path].");
        }
        fputcsv($handle, ['type', 'namespace', 'group', 'key', ...$locales]);
        foreach ($records as $record) {
            $row = [$record['type'], $record['namespace'], $record['group'], $record['key']];
            foreach ($locales as $locale) {
                $row[] = $this->escape((string) ($record[$locale] ?? ''));
            }
            fputcsv($handle, $row);
        }
        fclose($handle);

        return count($records);
    }

    public function import(string $path, string $conflict = 'fail', bool $commit = false, bool $forceProtected = false): array
    {
        if (! in_array($conflict, ['fail', 'skip', 'overwrite'], true)) {
            throw new \InvalidArgumentException("Invalid conflict policy [$conflict].");
        }
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException("Unable to read CSV [$path].");
        }
        $header = fgetcsv($handle);
        if (! is_array($header) || array_slice($header, 0, 4) !== ['type', 'namespace', 'group', 'key']) {
            throw new \UnexpectedValueException('CSV v1 header must start with type,namespace,group,key.');
        }
        $locales = array_slice($header, 4);
        $rows = [];
        while (($record = fgetcsv($handle)) !== false) {
            if (count($record) !== count($header)) {
                throw new \UnexpectedValueException('CSV row has an unexpected column count.');
            }
            [$type, $namespace, $group, $key] = array_slice($record, 0, 4);
            if (! in_array($type, ['group', 'single'], true) || $key === '') {
                throw new \UnexpectedValueException('CSV contains an invalid type or empty key.');
            }
            $resolvedGroup = $namespace !== '' ? $namespace.'::'.$group : $group;
            foreach ($locales as $index => $locale) {
                $rows[] = ['type' => $type, 'locale' => $locale, 'group' => $resolvedGroup, 'key' => $key, 'value' => $this->unescape((string) $record[$index + 4])];
            }
        }
        fclose($handle);
        $written = $commit ? $this->writer->write($rows, $conflict, $forceProtected) : 0;

        return ['rows' => count($rows), 'written' => $written, 'committed' => $commit];
    }

    private function escape(string $value): string
    {
        return preg_match('/^[=+\-@\']/', $value) ? "'".$value : $value;
    }

    private function unescape(string $value): string
    {
        return preg_match('/^\'[=+\-@\']/', $value) ? substr($value, 1) : $value;
    }
}
