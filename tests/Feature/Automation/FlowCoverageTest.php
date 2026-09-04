<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\WorkflowRule;
use Database\Seeders\AutomationSeeder;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Support\Facades\DB;

/**
 * The flowcharts, as assertions.
 *
 * An audit is only worth what it is worth the next time someone edits a
 * seeder. These pin the steps the two flowcharts name so a rule cannot quietly
 * go missing again — three of them already had.
 */
beforeEach(function (): void {
    $this->seed(ReferenceDataSeeder::class);
    $this->seed(AutomationSeeder::class);
});

it('has a rule for every automation the flowcharts name', function (string $key): void {
    expect(WorkflowRule::where('key', $key)->where('is_active', true)->exists())
        ->toBeTrue("workflow rule {$key} is missing");
})->with([
    // Charter §19 — sales, operations, finance, client.
    'sales.lead_follow_up',
    'charter.crew_notice',
    'charter.marina_notice',
    'charter.vendor_notice',
    'finance.payment_reminder',
    'finance.deposit_refund',
    'charter.booking_confirmation',
    'charter.reminder',
    'charter.weather_update',
    'post.thank_you',
    'post.feedback',
    'client.birthday',
    // Charter §15 — the post-charter sequence.
    'post.review',
    'post.follow_up_30',
    'post.follow_up_90',
    'post.annual',
    // Brokerage §10 — 7, 30, 90 and 180 days.
    'brokerage.listing_renewal',
    'brokerage.post_sale_7',
    'brokerage.post_sale_30',
    'brokerage.post_sale_90',
    'brokerage.post_sale_180',
]);

it('carries every lead source both flowcharts open with', function (string $channel): void {
    expect(DB::table('lead_sources')->where('channel', $channel)->exists())
        ->toBeTrue("lead source channel {$channel} is missing");
})->with(['website', 'whatsapp', 'phone', 'email', 'referral', 'social']);

it('carries every vendor category charter §11 lists', function (string $value): void {
    expect(DB::table('list_options')->where('list_key', 'vendor_category')->where('value', $value)->exists())
        ->toBeTrue("vendor category {$value} is missing");
})->with([
    'catering', 'entertainment', 'flowers', 'photography',
    'videography', 'hotel', 'transfers', 'watersports', 'security',
]);

it('queues an annual rule once a year, not once ever', function (): void {
    $client = Client::factory()->create([
        'date_of_birth' => now()->subYears(40)->format('Y-m-d'),
        'status' => 'active',
    ]);

    $this->artisan('walidia:automation', ['--queue-only' => true])->assertSuccessful();
    $this->artisan('walidia:automation', ['--queue-only' => true])->assertSuccessful();

    $runs = DB::table('workflow_runs')
        ->join('workflow_rules', 'workflow_rules.id', '=', 'workflow_runs.workflow_rule_id')
        ->where('workflow_rules.key', 'client.birthday')
        ->where('workflow_runs.subject_id', $client->id)
        ->get();

    // Twice through the scheduler, one run — and it is stamped with the year,
    // so next year's turn is a different row rather than a blocked duplicate.
    expect($runs)->toHaveCount(1)
        ->and($runs->first()->occurrence_key)->toBe((string) now()->year);
});
