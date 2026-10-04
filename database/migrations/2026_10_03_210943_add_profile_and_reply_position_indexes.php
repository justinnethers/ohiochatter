<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the secondary indexes used by the profile page, reply position
 * lookups and per-thread reply queries.
 *
 * - replies (user_id, deleted_at, created_at): `$user->replies()->latest()`
 *   with SoftDeletes, plus the `whereIn('repped_id', $user->replies()->select('id'))`
 *   subqueries (covering, since InnoDB appends the primary key).
 * - replies (thread_id, deleted_at, id): the position subquery
 *   (`thread_id = ? AND deleted_at IS NULL AND id <= ?`), thread pagination
 *   and per-thread reply counts, all as covering range scans.
 * - threads (user_id, created_at): a user's latest threads and the thread id
 *   subqueries used for rep/neg totals.
 * - reps (repped_type, repped_id) and negs (negged_type, negged_id): lookups
 *   by morph target, which the existing unique indexes cannot serve.
 *
 * Every index is guarded so the migration is safe to re-run after a partial
 * failure (MySQL DDL is not transactional).
 */
return new class extends Migration
{
    /**
     * @var array<int, array{table: string, name: string, columns: array<int, string>}>
     */
    private array $indexes = [
        [
            'table' => 'replies',
            'name' => 'replies_user_id_deleted_at_created_at_index',
            'columns' => ['user_id', 'deleted_at', 'created_at'],
        ],
        [
            'table' => 'replies',
            'name' => 'replies_thread_id_deleted_at_id_index',
            'columns' => ['thread_id', 'deleted_at', 'id'],
        ],
        [
            'table' => 'threads',
            'name' => 'threads_user_id_created_at_index',
            'columns' => ['user_id', 'created_at'],
        ],
        [
            'table' => 'reps',
            'name' => 'reps_repped_type_repped_id_index',
            'columns' => ['repped_type', 'repped_id'],
        ],
        [
            'table' => 'negs',
            'name' => 'negs_negged_type_negged_id_index',
            'columns' => ['negged_type', 'negged_id'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $index) {
            if (Schema::hasIndex($index['table'], $index['name'])) {
                continue;
            }

            Schema::table($index['table'], function (Blueprint $table) use ($index): void {
                $table->index($index['columns'], $index['name']);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $index) {
            if (! Schema::hasIndex($index['table'], $index['name'])) {
                continue;
            }

            Schema::table($index['table'], function (Blueprint $table) use ($index): void {
                $table->dropIndex($index['name']);
            });
        }
    }
};
