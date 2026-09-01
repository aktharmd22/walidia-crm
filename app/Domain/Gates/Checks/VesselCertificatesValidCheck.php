<?php

declare(strict_types=1);

namespace App\Domain\Gates\Checks;

use App\Domain\Gates\GateCheck;
use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Yacht;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * The vessel's own paperwork.
 *
 * An expired safety certificate is not an administrative detail — it is a
 * charter that cannot leave the marina, and a fine if it tries.
 */
class VesselCertificatesValidCheck implements GateCheck
{
    public function key(): string
    {
        return 'yacht.certificates_valid';
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function passes(Model $subject, array $params): bool
    {
        $yachtId = $this->yachtId($subject);

        if ($yachtId === null) {
            return true;
        }

        return $this->problems($yachtId, (int) ($params['within_days'] ?? 0))->isEmpty();
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function failureMessage(Model $subject, array $params): string
    {
        $yachtId = $this->yachtId($subject);

        if ($yachtId === null) {
            return 'No yacht is attached to this record.';
        }

        $names = $this->problems($yachtId, (int) ($params['within_days'] ?? 0))
            ->map(fn (Certificate $certificate): string => $certificate->name)
            ->all();

        return $names === []
            ? 'Vessel certificates are not valid.'
            : 'Certificate problem: '.implode(', ', $names).'.';
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{label: string, url: string}|null
     */
    public function resolution(Model $subject, array $params): ?array
    {
        $yachtId = $this->yachtId($subject);

        return $yachtId === null ? null : [
            'label' => 'Open certificates',
            'url' => route('management.certificates.index', ['yacht_id' => $yachtId]),
        ];
    }

    /**
     * Only the certificates that actually stop a charter — the rest are
     * chased by the reminder engine, not by a blocked button.
     *
     * @return Collection<int, Certificate>
     */
    private function problems(int $yachtId, int $withinDays): Collection
    {
        return Certificate::query()
            ->where('yacht_id', $yachtId)
            ->where('blocks_charter', true)
            ->whereNotNull('expires_on')
            ->whereDate('expires_on', '<=', now()->addDays($withinDays))
            ->get();
    }

    private function yachtId(Model $subject): ?int
    {
        if ($subject instanceof Yacht) {
            return (int) $subject->getKey();
        }

        if ($subject instanceof Booking) {
            return $subject->yacht_id === null ? null : (int) $subject->yacht_id;
        }

        $yachtId = $subject->getAttribute('yacht_id');

        return $yachtId === null ? null : (int) $yachtId;
    }
}
