<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ClientJourney;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ClientJourney
 */
class ClientJourneyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => $this->whenLoaded('client', fn (): ?string => $this->client?->full_name),
            'booking_id' => $this->booking_id,
            'transaction_id' => $this->transaction_id,
            'type' => $this->type,
            'thank_you_sent_at' => $this->thank_you_sent_at?->toIso8601String(),
            'feedback_requested_at' => $this->feedback_requested_at?->toIso8601String(),
            'review_requested_at' => $this->review_requested_at?->toIso8601String(),
            'survey_sent_at' => $this->survey_sent_at?->toIso8601String(),
            'satisfaction_score' => $this->satisfaction_score,
            'survey_response' => $this->survey_response,
            'complaint_raised' => $this->complaint_raised,
            'complaint_detail' => $this->complaint_detail,
            'complaint_resolved_at' => $this->complaint_resolved_at?->toIso8601String(),
            'complaint_resolution' => $this->complaint_resolution,
            'has_open_complaint' => $this->hasOpenComplaint(),
            'follow_ups_sent' => $this->follow_ups_sent,
            'upsell_interests' => $this->upsell_interests,
            'status' => $this->status,
            'status_tone' => match (true) {
                $this->hasOpenComplaint() => 'danger',
                $this->status === 'complete' => 'success',
                $this->status === 'lapsed' => 'neutral',
                default => 'info',
            },
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'url' => route('crm.journeys.show', $this->id),
        ];
    }
}
