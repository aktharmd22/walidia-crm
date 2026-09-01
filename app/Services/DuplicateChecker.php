<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * What makes two records the same person (Q9).
 *
 * Certain: the same normalised mobile number, or the same passport / Emirates
 * ID via the blind index. Those block creation and offer a merge.
 * Probable: the same name plus the same email domain — flagged for the
 * Duplicates screen, never merged automatically, because families share
 * numbers and companies share domains.
 */
class DuplicateChecker
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function findClient(array $data, ?int $ignoreId = null): ?Client
    {
        $query = Client::query()
            ->withoutOwnerScope()
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId));

        if (! empty($data['mobile'])) {
            $mobile = $this->normaliseMobile((string) $data['mobile']);

            $match = (clone $query)->whereRaw(
                "REPLACE(REPLACE(REPLACE(REPLACE(mobile, ' ', ''), '-', ''), '(', ''), ')', '') = ?",
                [$mobile],
            )->first();

            if ($match !== null) {
                return $match;
            }
        }

        foreach (['passport_number' => 'passport_hash', 'emirates_id' => 'emirates_id_hash'] as $field => $column) {
            if (empty($data[$field])) {
                continue;
            }

            $match = (clone $query)->where($column, Client::blindHash((string) $data[$field]))->first();

            if ($match !== null) {
                return $match;
            }
        }

        if (! empty($data['email'])) {
            $match = (clone $query)->where('email', $data['email'])->first();

            if ($match !== null) {
                return $match;
            }
        }

        return null;
    }

    /**
     * Probable duplicates for the Duplicates screen — scored, never actioned.
     *
     * @return Collection<int, array{lead: Lead, match: Client, score: int, reason: string}>
     */
    public function probableLeadDuplicates(int $limit = 100): Collection
    {
        $leads = Lead::query()
            ->withoutOwnerScope()
            ->whereNull('duplicate_of_id')
            ->whereNotNull('mobile')
            ->latest()
            ->limit($limit)
            ->get();

        /** @var Collection<int, array{lead: Lead, match: Client, score: int, reason: string}> $pairs */
        $pairs = new Collection;

        foreach ($leads as $lead) {
            $client = $this->findClient(['mobile' => $lead->mobile, 'email' => $lead->email]);

            if ($client === null) {
                continue;
            }

            $sameMobile = $lead->mobile !== null && $client->mobile !== null;

            $pairs->push([
                'lead' => $lead,
                'match' => $client,
                'score' => $sameMobile ? 95 : 70,
                'reason' => $sameMobile ? 'Same mobile number' : 'Same email address',
            ]);
        }

        return $pairs;
    }

    public function normaliseMobile(string $mobile): string
    {
        return preg_replace('/[^0-9+]/', '', $mobile) ?? $mobile;
    }
}
