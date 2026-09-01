<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use Illuminate\Support\Facades\DB;

/**
 * Lead → client → deal, in one transaction.
 *
 * The lead is never deleted or overwritten: it keeps its source, its response
 * times and its history, and points at what it became. That is what makes
 * source-effectiveness reporting possible a year later.
 */
class LeadConverter
{
    public function __construct(private readonly DuplicateChecker $duplicates) {}

    public function convert(Lead $lead, ?int $existingClientId = null, bool $createDeal = true): Client
    {
        return DB::transaction(function () use ($lead, $existingClientId, $createDeal): Client {
            $client = $existingClientId !== null
                ? Client::withoutOwnerScope()->findOrFail($existingClientId)
                : $this->findOrCreateClient($lead);

            $lead->forceFill([
                'client_id' => $client->id,
                'status' => 'registered',
                'converted_at' => now(),
                'converted_to_type' => $client->getMorphClass(),
                'converted_to_id' => $client->getKey(),
            ])->save();

            $lead->logActivity('system', "Converted to client {$client->reference}");
            $client->logActivity('system', "Created from lead {$lead->reference}");

            if ($createDeal) {
                $this->openDeal($lead, $client);
            }

            return $client;
        });
    }

    private function findOrCreateClient(Lead $lead): Client
    {
        $existing = $this->duplicates->findClient([
            'mobile' => $lead->mobile,
            'email' => $lead->email,
        ]);

        if ($existing !== null) {
            return $existing;
        }

        $names = preg_split('/\s+/', trim($lead->name), 2) ?: [$lead->name];

        return Client::create([
            'first_name' => $names[0],
            'last_name' => $names[1] ?? null,
            'email' => $lead->email,
            'mobile' => $lead->mobile,
            'company_id' => $lead->company_id,
            'source_id' => $lead->source_id,
            'assigned_user_id' => $lead->assigned_user_id,
            'client_type' => [$lead->business_line === 'brokerage' ? 'buyer' : 'charter_guest'],
            'preferred_channel' => 'whatsapp',
            'status' => 'active',
        ]);
    }

    private function openDeal(Lead $lead, Client $client): ?Deal
    {
        $pipelineKey = match ($lead->business_line) {
            'brokerage' => 'buyer',
            default => 'charter',
        };

        $pipeline = Pipeline::with('stages')->where('key', $pipelineKey)->first();
        $stage = $pipeline?->stages->firstWhere('key', 'qualified') ?? $pipeline?->stages->first();

        if ($pipeline === null || $stage === null) {
            return null;
        }

        return Deal::create([
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'client_id' => $client->id,
            'company_id' => $client->company_id,
            'title' => $client->full_name.' — '.$pipeline->name,
            'assigned_user_id' => $lead->assigned_user_id,
            'subject_type' => $lead->getMorphClass(),
            'subject_id' => $lead->getKey(),
            'status' => 'open',
        ]);
    }
}
