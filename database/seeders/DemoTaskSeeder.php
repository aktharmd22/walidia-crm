<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * A working week's tasks.
 *
 * Written as the team would actually write them — a named yacht, a named
 * client, the thing that has to happen — because a demo full of "Task 1" tells
 * you nothing about whether the screen works. The spread matters too: two
 * overdue, some due today, the rest ahead, so the overdue styling, the due
 * grouping and the empty states all have something to show.
 */
class DemoTaskSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::orderBy('id')->get()->keyBy(fn (User $user): string => explode(' ', $user->name)[0]);

        if ($users->isEmpty()) {
            $this->command?->warn('No users to assign tasks to — run DevUsersSeeder first.');

            return;
        }

        $pick = fn (string $first): int => (int) ($users->get($first)?->id ?? $users->first()?->id);

        // owner, title, type, priority, hours from now, detail
        $tasks = [
            ['Dana', 'Call HH Sheikh Ahmed about the Eid weekend charter', 'follow_up', 'high', -30,
                'He asked for the 42m with the beach club. Confirm guest numbers before we hold the date.'],
            ['Dana', 'Chase the signed charter agreement for BK-2026-0042', 'follow_up', 'urgent', -6,
                'Sent Tuesday. Operational Release is blocked until it comes back.'],
            ['Dana', 'Approve the revised cost sheet for the Dubai Marina overnight', 'approval', 'high', 3,
                'Catering came in above the quote; the margin needs a second look before it goes to the client.'],
            ['Dana', 'Review next week&rsquo;s charter board', 'next_action', 'normal', 26,
                'Four charters, two yachts. Check the crew is not double-booked on Friday.'],
            ['Dana', 'Send the owner statement for Lady Walidia', 'next_action', 'normal', 52,
                'September figures are ready. Issue it once the maintenance invoice is in.'],

            ['Amal', 'Qualify the Instagram enquiry from the Riyadh family office', 'follow_up', 'high', -2,
                'Budget looks serious. Get the guest count and the dates before proposing anything.'],
            ['Amal', 'Prepare the proposal for the Al Futtaim corporate day', 'next_action', 'high', 20,
                'Sixty guests, sunset departure. They asked for a quote by Thursday.'],
            ['Amal', 'Follow up the boat show leads', 'follow_up', 'normal', 72,
                'Eleven cards from the stand. Nobody has been called yet.'],

            ['Rami', 'Renew the safety equipment certificate on the 38m', 'compliance', 'urgent', 8,
                'Expires in eleven days. A lapse stops the charter at the marina gate.'],
            ['Rami', 'Confirm the berth allocation with Dubai Harbour', 'ops', 'high', 12,
                'Two arrivals on Saturday afternoon within an hour of each other.'],
            ['Rami', 'Brief the crew on the Nakheel charter', 'ops', 'normal', 30,
                'Guest has a shellfish allergy — the chef needs it in writing, not in passing.'],
            ['Rami', 'Chase Marco&rsquo;s seaman book renewal', 'compliance', 'high', 4,
                'Expired last week. He cannot be dispatched until it is back.'],

            ['Noor', 'Reconcile the September bank statement', 'next_action', 'normal', 44,
                'Three transfers unmatched, all from the same DMC.'],
            ['Noor', 'Release the security deposit on BK-2026-0031', 'approval', 'high', 6,
                'Damage inspection closed clear. The client has asked twice.'],
            ['Noor', 'Issue the VAT return working papers', 'compliance', 'normal', 96,
                'Quarter ends Friday. The out-of-scope lines need checking first.'],
        ];

        $created = 0;

        foreach ($tasks as [$owner, $title, $type, $priority, $hours, $detail]) {
            $task = Task::firstOrCreate(
                ['title' => html_entity_decode($title)],
                [
                    'description' => html_entity_decode($detail),
                    'type' => $type,
                    'priority' => $priority,
                    'assigned_user_id' => $pick($owner),
                    'due_at' => now()->addHours($hours),
                    'status' => 'open',
                    'source' => 'seed',
                ],
            );

            if ($task->wasRecentlyCreated) {
                $created++;
            }
        }

        // A few already done, so "open" is a filter that visibly does something.
        foreach ([
            ['Dana', 'Send the thank-you note to the Al Maktoum party'],
            ['Amal', 'Log the feedback from the birthday charter'],
            ['Rami', 'File the fuel receipts for August'],
        ] as [$owner, $title]) {
            $task = Task::firstOrCreate(
                ['title' => $title],
                [
                    'type' => 'next_action',
                    'priority' => 'normal',
                    'assigned_user_id' => $pick($owner),
                    'due_at' => now()->subDays(3),
                    'status' => 'done',
                    'completed_at' => now()->subDays(2),
                    'completed_by' => $pick($owner),
                    'source' => 'seed',
                ],
            );

            if ($task->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->command?->info("Seeded {$created} tasks across ".$users->count().' users.');
    }
}
