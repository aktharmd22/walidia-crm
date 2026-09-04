<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A null occurrence key is not a key at all.
 *
 * The previous migration added `occurrence_key` as nullable and put it in the
 * unique index, which quietly destroyed the guarantee the index exists for:
 * in both MySQL and SQLite two NULLs are considered distinct, so a one-shot
 * rule — which left the key null — could queue against the same subject as
 * many times as the event fired. Every event-triggered automation in the
 * system was affected.
 *
 * One-shot rules now carry the literal 'once'. It collides with itself, which
 * is the entire point.
 */
return new class extends Migration
{
    public function up(): void
    {
        /*
         * Anything queued while the key was nullable may already be duplicated
         * — the dev database was, which is how the bug was caught. Keep the
         * first run of each pair and drop the rest before the index has to
         * hold them apart. The first is the right one to keep: it is the send
         * that would have happened had the constraint been working.
         */
        $duplicates = DB::table('workflow_runs')
            ->whereNull('occurrence_key')
            ->selectRaw('MIN(id) as keep_id, workflow_rule_id, subject_type, subject_id')
            ->groupBy('workflow_rule_id', 'subject_type', 'subject_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $group) {
            DB::table('workflow_runs')
                ->whereNull('occurrence_key')
                ->where('workflow_rule_id', $group->workflow_rule_id)
                ->where('subject_type', $group->subject_type)
                ->where('subject_id', $group->subject_id)
                ->where('id', '!=', $group->keep_id)
                ->delete();
        }

        DB::table('workflow_runs')->whereNull('occurrence_key')->update(['occurrence_key' => 'once']);

        Schema::table('workflow_runs', function (Blueprint $table): void {
            $table->string('occurrence_key', 20)->default('once')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('workflow_runs', function (Blueprint $table): void {
            $table->string('occurrence_key', 20)->nullable()->default(null)->change();
        });
    }
};
