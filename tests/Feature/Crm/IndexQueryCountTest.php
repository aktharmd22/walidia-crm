<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\Task;
use App\Models\Yacht;
use App\Support\Roles;
use Database\Seeders\PipelineSeeder;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Query-count ceilings (brief §12)
|--------------------------------------------------------------------------
|
| "Never N+1" is only true if something checks. Each index screen is loaded
| with twenty-five rows and its queries counted: the number must not grow with
| the row count, which is what these ceilings enforce.
|
*/

beforeEach(function (): void {
    seedRoles();
});

function countQueries(callable $callback): int
{
    $count = 0;
    DB::listen(function () use (&$count): void {
        $count++;
    });

    $callback();

    return $count;
}

it('loads the clients index without an N+1', function (): void {
    $sales = userWithRole(Roles::SALES);
    Client::factory()->count(25)->create([
        'assigned_user_id' => $sales->id,
        'company_id' => Company::factory(),
    ]);

    $queries = countQueries(fn () => $this->actingAs($sales)->get('/clients')->assertOk());

    expect($queries)->toBeLessThan(20);
});

it('loads the leads index without an N+1', function (): void {
    $sales = userWithRole(Roles::SALES);
    Lead::factory()->count(25)->create(['assigned_user_id' => $sales->id]);

    $queries = countQueries(fn () => $this->actingAs($sales)->get('/leads')->assertOk());

    expect($queries)->toBeLessThan(20);
});

it('loads the fleet index without an N+1', function (): void {
    $ops = userWithRole(Roles::OPERATIONS);
    Yacht::factory()->count(25)->create();

    $queries = countQueries(fn () => $this->actingAs($ops)->get('/fleet/yachts')->assertOk());

    expect($queries)->toBeLessThan(20);
});

it('loads the tasks index without an N+1', function (): void {
    $sales = userWithRole(Roles::SALES);
    Task::factory()->count(25)->create(['assigned_user_id' => $sales->id]);

    $queries = countQueries(fn () => $this->actingAs($sales)->get('/tasks')->assertOk());

    expect($queries)->toBeLessThan(25);
});

it('loads the pipeline board without an N+1', function (): void {
    $this->seed(PipelineSeeder::class);

    $sales = userWithRole(Roles::SALES);
    $pipeline = Pipeline::with('stages')->where('key', 'charter')->first();

    foreach ($pipeline->stages->take(6) as $stage) {
        Deal::factory()->count(4)->create([
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'client_id' => Client::factory(),
            'assigned_user_id' => $sales->id,
        ]);
    }

    $queries = countQueries(fn () => $this->actingAs($sales)->get('/deals/board')->assertOk());

    expect($queries)->toBeLessThan(25);
});
