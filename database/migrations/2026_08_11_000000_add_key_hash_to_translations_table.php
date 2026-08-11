<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('translation.database.connection');
        $table = config('translation.database.translations_table');
        $schema = Schema::connection($connection);
        if (! $schema->hasTable($table)) {
            return;
        }

        if (! $schema->hasColumn($table, 'key_hash')) {
            $schema->table($table, fn (Blueprint $blueprint) => $blueprint->string('key_hash', 64)->nullable()->after('key'));
        }

        $query = DB::connection($connection)->table($table);
        $query->whereNull('group')->update(['group' => 'single']);
        $query->orderBy('id')->chunkById(500, function ($rows) use ($connection, $table) {
            foreach ($rows as $row) {
                DB::connection($connection)->table($table)->where('id', $row->id)->update(['key_hash' => hash('sha256', $row->key)]);
            }
        });

        $duplicates = DB::connection($connection)->table($table)
            ->select('language_id', 'group', 'key_hash', DB::raw('MAX(id) as keep_id'), DB::raw('COUNT(*) as aggregate'))
            ->groupBy('language_id', 'group', 'key_hash')->havingRaw('COUNT(*) > 1')->get();
        foreach ($duplicates as $duplicate) {
            DB::connection($connection)->table($table)->where('language_id', $duplicate->language_id)->where('group', $duplicate->group)->where('key_hash', $duplicate->key_hash)->where('id', '<>', $duplicate->keep_id)->delete();
        }

        $schema->table($table, fn (Blueprint $blueprint) => $blueprint->unique(['language_id', 'group', 'key_hash'], 'translations_language_group_key_hash_unique'));
    }

    public function down(): void
    {
        $connection = config('translation.database.connection');
        $table = config('translation.database.translations_table');
        $schema = Schema::connection($connection);
        if (! $schema->hasColumn($table, 'key_hash')) {
            return;
        }
        $schema->table($table, function (Blueprint $blueprint) {
            $blueprint->dropUnique('translations_language_group_key_hash_unique');
            $blueprint->dropColumn('key_hash');
        });
    }
};
