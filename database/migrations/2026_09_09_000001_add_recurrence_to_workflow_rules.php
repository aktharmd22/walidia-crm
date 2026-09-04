<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rules that come round every year.
 *
 * The flowcharts ask for a birthday greeting and an annual charter reminder.
 * Neither fits the scheduler as it stood: it matched an anchor date against a
 * window around today, so a client born in 1974 was never due, and the run
 * table was unique on (rule, subject) with no date, so even if it had fired it
 * would have fired once and never again.
 *
 * Two columns fix both. `recurrence` says an anchor should be read as an
 * anniversary — month and day, ignore the year — and `occurrence_key` carries
 * the year into the uniqueness constraint, so one send per client per year
 * while a one-shot rule keeps a null key and stays one-shot.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('workflow_rules', 'recurrence')) {
            Schema::table('workflow_rules', function (Blueprint $table): void {
                $table->string('recurrence', 20)->nullable()->after('anchor_field');
            });
        }

        Schema::table('workflow_runs', function (Blueprint $table): void {
            if (! Schema::hasColumn('workflow_runs', 'occurrence_key')) {
                $table->string('occurrence_key', 20)->nullable()->after('subject_id');
            }
        });

        /*
         * The old unique index is the only index on workflow_rule_id, so the
         * foreign key leans on it and MySQL refuses to drop it. Create the
         * replacement first — it leads with the same column, so the constraint
         * moves across — and only then drop the one it replaced.
         */
        Schema::table('workflow_runs', function (Blueprint $table): void {
            $table->unique(
                ['workflow_rule_id', 'subject_type', 'subject_id', 'occurrence_key'],
                'workflow_run_occurrence',
            );
        });

        Schema::table('workflow_runs', function (Blueprint $table): void {
            $table->dropUnique('workflow_run_once');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_runs', function (Blueprint $table): void {
            $table->unique(['workflow_rule_id', 'subject_type', 'subject_id'], 'workflow_run_once');
        });

        Schema::table('workflow_runs', function (Blueprint $table): void {
            $table->dropUnique('workflow_run_occurrence');
            $table->dropColumn('occurrence_key');
        });

        Schema::table('workflow_rules', function (Blueprint $table): void {
            $table->dropColumn('recurrence');
        });
    }
};
