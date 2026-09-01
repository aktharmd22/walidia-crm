<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GateRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Seeds "what unlocks what" from docs/phase-0/06-gate-rules.json — the same
 * file the client reviewed, so the rules in the database and the rules in the
 * plan cannot drift apart.
 *
 * Rules whose checks belong to a later phase are seeded inactive rather than
 * omitted: they are visible in the rule editor from day one, and switching them
 * on is a toggle rather than a deployment.
 */
class GateRuleSeeder extends Seeder
{
    /** Rules whose conditions depend on tables that arrive in later phases. */
    private const PENDING_PHASES = [
        'soft.weather.unchecked',
        'soft.option_hold.expiring',
    ];

    public function run(): void
    {
        $path = base_path('docs/phase-0/06-gate-rules.json');

        if (! File::exists($path)) {
            $this->command->warn('Gate rule definitions not found — skipping.');

            return;
        }

        /** @var array{rules: list<array<string, mixed>>} $definitions */
        $definitions = json_decode((string) File::get($path), true, 512, JSON_THROW_ON_ERROR);

        $active = 0;
        $pending = 0;

        foreach ($definitions['rules'] as $rule) {
            $key = (string) $rule['key'];
            $isPending = in_array($key, self::PENDING_PHASES, true);

            GateRule::updateOrCreate(
                ['key' => $key],
                [
                    'name_en' => $rule['name_en'],
                    'name_ar' => $rule['name_ar'] ?? null,
                    'subject_type' => $rule['subject_type'],
                    'trigger_type' => $rule['trigger_type'],
                    'trigger_field' => $rule['trigger_field'] ?? null,
                    'trigger_from' => $rule['trigger_from'] ?? null,
                    'trigger_to' => $rule['trigger_to'] ?? null,
                    'action_key' => $rule['action_key'] ?? null,
                    'severity' => $rule['severity'],
                    'conditions' => $rule['conditions'],
                    'block_message_en' => $rule['block_message_en'],
                    'resolution_route' => $rule['resolution_route'] ?? null,
                    'resolution_label' => $rule['resolution_label'] ?? null,
                    'creates_task' => $rule['creates_task'] ?? null,
                    'is_overridable' => $rule['is_overridable'] ?? true,
                    'override_permission' => $rule['override_permission'] ?? 'gates.override',
                    'requires_reason' => $rule['requires_reason'] ?? true,
                    'sort_order' => $rule['sort_order'] ?? 0,
                    'is_active' => ($rule['is_active'] ?? true) && ! $isPending,
                ],
            );

            $isPending ? $pending++ : $active++;
        }

        $this->command->info("Seeded {$active} active gate rules and {$pending} awaiting a later phase.");
    }
}
