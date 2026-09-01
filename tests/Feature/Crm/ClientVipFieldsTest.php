<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\RecordAccessLog;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    seedRoles();
});

/*
|--------------------------------------------------------------------------
| Field-level protection (brief §4)
|--------------------------------------------------------------------------
|
| These assert on the response payload, not on the rendered screen: a field
| the user may not see must never be serialised at all, because "hidden in the
| UI" is not hidden.
|
*/

it('omits VIP fields from the payload for a user without VIP access', function (): void {
    $sales = userWithRole(Roles::SALES);

    $client = Client::factory()->vip()->create([
        'assigned_user_id' => $sales->id,
        'passport_number' => 'X1234567',
        'allergies' => 'Shellfish',
    ]);

    $this->actingAs($sales)
        ->get("/clients/{$client->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('record.vip_fields_hidden', true)
            ->missing('record.passport_number')
            ->missing('record.allergies')
            ->missing('record.emirates_id')
            ->missing('record.notes_vip'));
});

it('includes VIP fields for a user who holds the permission', function (): void {
    $sales = userWithRole(Roles::SALES);
    $sales->givePermissionTo('clients.view-vip');

    $client = Client::factory()->vip()->create([
        'assigned_user_id' => $sales->id,
        'passport_number' => 'X1234567',
        'allergies' => 'Shellfish',
    ]);

    $this->actingAs($sales->fresh())
        ->get("/clients/{$client->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('record.passport_number', 'X1234567')
            ->where('record.allergies', 'Shellfish'));
});

it('logs every VIP field access against the user who made it', function (): void {
    $sales = userWithRole(Roles::SALES);
    $sales->givePermissionTo('clients.view-vip');

    $client = Client::factory()->vip()->create(['assigned_user_id' => $sales->id]);

    $this->actingAs($sales->fresh())->get("/clients/{$client->id}")->assertOk();

    $log = RecordAccessLog::where('subject_type', 'client')->where('subject_id', $client->id)->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($sales->id)
        ->and($log->field_group)->toBe('vip')
        ->and($log->action)->toBe('view');
});

it('does not log ordinary client reads — only protected ones', function (): void {
    $sales = userWithRole(Roles::SALES);
    $sales->givePermissionTo('clients.view-vip');

    $client = Client::factory()->create(['assigned_user_id' => $sales->id, 'vip_level' => 'none']);

    $this->actingAs($sales->fresh())->get("/clients/{$client->id}")->assertOk();

    expect(RecordAccessLog::count())->toBe(0);
});

it('encrypts identity fields at rest', function (): void {
    $client = Client::factory()->create(['passport_number' => 'X1234567']);

    $raw = (string) DB::table('clients')->where('id', $client->id)->value('passport_number');

    expect($raw)->not->toBe('X1234567')
        ->and($raw)->not->toContain('X1234567')
        ->and($client->fresh()->passport_number)->toBe('X1234567');
});

it('finds a client by passport through the blind index without storing it in clear', function (): void {
    $client = Client::factory()->create(['passport_number' => 'X1234567']);

    $found = Client::withoutOwnerScope()->whereBlind('passport_number', 'x123 4567')->first();

    expect($found?->id)->toBe($client->id);
});

it('refuses VIP field input from a user who cannot read those fields', function (): void {
    $sales = userWithRole(Roles::SALES);

    $this->actingAs($sales)->post('/clients', [
        'first_name' => 'Test',
        'client_type' => ['charter_guest'],
        'preferred_channel' => 'whatsapp',
        'vip_level' => 'none',
        'status' => 'active',
        'passport_number' => 'SHOULD-NOT-SAVE',
    ])->assertRedirect();

    expect(Client::withoutOwnerScope()->latest('id')->first()->passport_number)->toBeNull();
});
