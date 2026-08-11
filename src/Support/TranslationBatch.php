<?php

namespace Arm092\Translation\Support;

use Arm092\Translation\Contracts\TranslationBatchWriter;
use Arm092\Translation\Drivers\Database;
use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Events\TranslationAdded;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class TranslationBatch implements TranslationBatchWriter
{
    public function __construct(private Translation $driver, private ProtectedLocales $protected) {}

    public function write(array $rows, string $conflict = 'overwrite', bool $forceProtected = false): int
    {
        foreach ($rows as $row) {
            $this->protected->authorize($row['locale'], $forceProtected);
        }
        if ($this->driver instanceof Database && $conflict === 'overwrite') {
            $written = $this->driver->upsertTranslations($rows);
            foreach (array_unique(array_column($rows, 'locale')) as $locale) {
                app(TranslationCache::class)->forget($this->driver, $locale);
            }
            $this->dispatch($rows);

            return $written;
        }
        $operation = function () use ($rows, $conflict) {
            $writtenRows = [];
            foreach ($rows as $row) {
                $current = $this->value($row);
                if ($current !== null && $conflict === 'skip') {
                    continue;
                }
                if ($current !== null && $conflict === 'fail' && (string) $current !== (string) $row['value']) {
                    throw new \RuntimeException("Conflict for [{$row['locale']}:{$row['group']}.{$row['key']}].");
                }
                $row['type'] === 'single'
                    ? $this->driver->addSingleTranslation($row['locale'], $row['group'], $row['key'], $row['value'])
                    : $this->driver->addGroupTranslation($row['locale'], $row['group'], $row['key'], $row['value']);
                $writtenRows[] = $row;
            }

            return $writtenRows;
        };
        $writtenRows = $this->driver instanceof Database
            ? DB::connection(config('translation.database.connection'))->transaction($operation)
            : $operation();
        foreach ($writtenRows as $row) {
            app(TranslationCache::class)->forget($this->driver, $row['locale']);
        }
        $this->dispatch($writtenRows);

        return count($writtenRows);
    }

    private function value(array $row): mixed
    {
        $all = $this->driver->allTranslationsFor($row['locale']);

        return $all->get($row['type'], collect())->get($row['group'], collect())->get($row['key']);
    }

    private function dispatch(array $rows): void
    {
        foreach ($rows as $row) {
            Event::dispatch(new TranslationAdded($row['locale'], $row['group'], $row['key'], $row['value']));
        }
    }
}
