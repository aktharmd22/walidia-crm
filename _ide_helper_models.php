<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * One timeline entry. Calls, messages, meetings, status changes and gate
 * evaluations all land here, so a client's history reads as one story.
 *
 * @property int $id
 * @property string $subject_type
 * @property int $subject_id
 * @property int|null $client_id
 * @property int|null $user_id
 * @property string $type
 * @property string|null $direction
 * @property string $summary
 * @property string|null $body
 * @property array<array-key, mixed>|null $meta
 * @property int|null $communication_id
 * @property \Carbon\CarbonImmutable $occurred_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\Client|null $client
 * @property-read \Illuminate\Database\Eloquent\Model $subject
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\ActivityFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity ofType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereCommunicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereOccurredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activity withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperActivity {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $subject_type
 * @property int $subject_id
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string|null $mime
 * @property int $size
 * @property string|null $checksum
 * @property int|null $uploaded_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model $subject
 * @property-read \App\Models\User|null $uploader
 * @method static \Database\Factories\AttachmentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereChecksum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAttachment {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $marina_id
 * @property string $code
 * @property numeric|null $max_loa_m
 * @property numeric|null $monthly_fee
 * @property string|null $notes
 * @property bool $is_active
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\Marina|null $marina
 * @method static \Database\Factories\BerthFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereMarinaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereMaxLoaM($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereMonthlyFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berth withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBerth {}
}

namespace App\Models{
/**
 * A charter, from contract to completion.
 *
 * `status` is the booking's own lifecycle; the deal's `stage` is the board
 * position (D-005). `operational_release_at` is the pivot the entire operations
 * side gates on — no crew is dispatched and no vendor is booked before Finance
 * sets it.
 *
 * @property int $id
 * @property string|null $reference
 * @property int|null $charter_proposal_id
 * @property int|null $charter_enquiry_id
 * @property int $client_id
 * @property int|null $company_id
 * @property int $yacht_id
 * @property int|null $deal_id
 * @property \Carbon\CarbonImmutable $starts_at
 * @property \Carbon\CarbonImmutable $ends_at
 * @property int|null $departure_marina_id
 * @property int|null $return_marina_id
 * @property int $guests_adults
 * @property int $guests_children
 * @property string|null $special_requests
 * @property string|null $itinerary
 * @property string $status
 * @property int|null $contract_document_id
 * @property \Carbon\CarbonImmutable|null $contract_signed_at
 * @property \Carbon\CarbonImmutable|null $operational_release_at
 * @property int|null $operational_release_by
 * @property \Carbon\CarbonImmutable|null $boarded_at
 * @property \Carbon\CarbonImmutable|null $completed_at
 * @property int|null $cancellation_policy_id
 * @property \Carbon\CarbonImmutable|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property numeric|null $cancellation_fee
 * @property numeric|null $apa_amount
 * @property string $currency
 * @property int|null $assigned_user_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User|null $assignee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\CancellationPolicy|null $cancellationPolicy
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\CostSheet|null $costSheet
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Marina|null $departureMarina
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \App\Models\CharterEnquiry|null $enquiry
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BookingGuest> $guests
 * @property-read int|null $guests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuestManifest> $manifests
 * @property-read int|null $manifests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Note> $notes
 * @property-read int|null $notes_count
 * @property-read \App\Models\PaymentSchedule|null $paymentSchedule
 * @property-read \App\Models\CharterProposal|null $proposal
 * @property-read \App\Models\Marina|null $returnMarina
 * @property-read \App\Models\SecurityDeposit|null $securityDeposit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\User|null $updater
 * @property-read \App\Models\Yacht|null $yacht
 * @method static \Database\Factories\BookingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking upcoming(int $days = 14)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereApaAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereAssignedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereBoardedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCancellationFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCancellationPolicyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCharterEnquiryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCharterProposalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereContractDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereContractSignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereDealId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereDepartureMarinaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereGuestsAdults($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereGuestsChildren($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereItinerary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereOperationalReleaseAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereOperationalReleaseBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereReturnMarinaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereSpecialRequests($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereYachtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking withoutOwnerScope()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBooking {}
}

namespace App\Models{
/**
 * Guest identity data, encrypted at rest and readable only with VIP
 * permission. The manifest and the boarding gate both read this table.
 *
 * @property int $id
 * @property int $booking_id
 * @property string $name
 * @property string|null $nationality
 * @property string|null $document_type
 * @property string|null $document_number
 * @property string|null $date_of_birth
 * @property bool $is_lead_guest
 * @property string|null $dietary
 * @property string|null $allergies
 * @property bool $id_verified
 * @property int|null $id_verified_by
 * @property \Carbon\CarbonImmutable|null $checked_in_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Booking|null $booking
 * @method static \Database\Factories\BookingGuestFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereAllergies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereCheckedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereDietary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereIdVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereIdVerifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereIsLeadGuest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereNationality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BookingGuest withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBookingGuest {}
}

namespace App\Models{
/**
 * Cancellation tiers as data: days before departure to fee percentage (Q10).
 *
 * @property int $id
 * @property string $name
 * @property array<array-key, mixed>|null $rules
 * @property string $applies_to
 * @property bool $is_default
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @method static \Database\Factories\CancellationPolicyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy whereAppliesTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy whereRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancellationPolicy withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCancellationPolicy {}
}

namespace App\Models{
/**
 * What the client actually asked for: dates, guests, budget, marinas, extras.
 *
 * Everything downstream — matching, proposals, the booking — reads this.
 *
 * @property int $id
 * @property string|null $reference
 * @property int|null $lead_id
 * @property int|null $client_id
 * @property int|null $company_id
 * @property int|null $deal_id
 * @property string|null $experience_type
 * @property string|null $occasion
 * @property \Carbon\CarbonImmutable|null $requested_date
 * @property array<array-key, mixed>|null $alternative_dates
 * @property numeric|null $duration_hours
 * @property string|null $start_time
 * @property string|null $end_time
 * @property int $guests_adults
 * @property int $guests_children
 * @property numeric|null $budget_min
 * @property numeric|null $budget_max
 * @property string $currency
 * @property int|null $pickup_marina_id
 * @property int|null $dropoff_marina_id
 * @property int|null $yacht_preference_id
 * @property string|null $itinerary_notes
 * @property array<array-key, mixed>|null $requested_extras
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Note> $notes
 * @property string $status
 * @property int|null $assigned_user_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User|null $assignee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \App\Models\Marina|null $dropoffMarina
 * @property-read \App\Models\Lead|null $lead
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CharterMatch> $matches
 * @property-read int|null $matches_count
 * @property-read int|null $notes_count
 * @property-read \App\Models\Marina|null $pickupMarina
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CharterProposal> $proposals
 * @property-read int|null $proposals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\CharterEnquiryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereAlternativeDates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereAssignedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereBudgetMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereBudgetMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereDealId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereDropoffMarinaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereDurationHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereExperienceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereGuestsAdults($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereGuestsChildren($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereItineraryNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereOccasion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry wherePickupMarinaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereRequestedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereRequestedExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry whereYachtPreferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry withoutOwnerScope()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterEnquiry withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCharterEnquiry {}
}

namespace App\Models{
/**
 * A scored yacht suggestion. The reasons are stored so a broker can defend the shortlist to a client.
 *
 * @property int $id
 * @property int $charter_enquiry_id
 * @property int $yacht_id
 * @property int $score
 * @property array<array-key, mixed>|null $reasons
 * @property bool $is_shortlisted
 * @property bool $is_sent
 * @property \Carbon\CarbonImmutable|null $sent_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\CharterEnquiry|null $enquiry
 * @property-read \App\Models\Yacht|null $yacht
 * @method static \Database\Factories\CharterMatchFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch whereCharterEnquiryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch whereIsSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch whereIsShortlisted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch whereReasons($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch whereYachtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterMatch withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCharterMatch {}
}

namespace App\Models{
/**
 * A priced offer to the client.
 *
 * Versioned rather than mutated: a new version supersedes the old one, so what
 * the client actually saw and accepted is still on file afterwards.
 *
 * @property int $id
 * @property string|null $reference
 * @property int $charter_enquiry_id
 * @property int|null $client_id
 * @property int $version
 * @property int|null $supersedes_id
 * @property \Carbon\CarbonImmutable|null $valid_until
 * @property string $currency
 * @property numeric $subtotal
 * @property numeric $discount
 * @property numeric $tax_amount
 * @property numeric $total
 * @property string|null $terms
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Note> $notes
 * @property int|null $pdf_document_id
 * @property string $status
 * @property \Carbon\CarbonImmutable|null $sent_at
 * @property \Carbon\CarbonImmutable|null $viewed_at
 * @property \Carbon\CarbonImmutable|null $responded_at
 * @property string|null $decline_reason
 * @property int|null $assigned_user_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \App\Models\CharterEnquiry|null $enquiry
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProposalItem> $items
 * @property-read int|null $items_count
 * @property-read int|null $notes_count
 * @property-read \App\Models\Document|null $pdf
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\CharterProposalFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereAssignedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereCharterEnquiryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereDeclineReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal wherePdfDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereRespondedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereSupersedesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal whereViewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal withoutOwnerScope()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CharterProposal withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCharterProposal {}
}

namespace App\Models{
/**
 * One line of an operations checklist. A blocking item — the safety briefing,
 * for instance — is read directly by the boarding gate.
 *
 * @property int $id
 * @property int $operations_checklist_id
 * @property int|null $checklist_template_item_id
 * @property string $key
 * @property string $title
 * @property string|null $section
 * @property int|null $responsible_user_id
 * @property \Carbon\CarbonImmutable|null $due_at
 * @property string $status
 * @property \Carbon\CarbonImmutable|null $completed_at
 * @property int|null $completed_by
 * @property string|null $note
 * @property string|null $photo_path
 * @property string|null $signature_path
 * @property bool $is_blocking
 * @property int $sort_order
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\OperationsChecklist|null $checklist
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereChecklistTemplateItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereCompletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereDueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereIsBlocking($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereOperationsChecklistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem wherePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereResponsibleUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereSection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecklistItem withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperChecklistItem {}
}

namespace App\Models{
/**
 * The single client record.
 *
 * One row can be a charter guest, a buyer, a seller and an owner at the same
 * time — client_type is an array, not an enum, because the alternative is four
 * records for one person and a reconciliation problem forever.
 *
 * @property int $id
 * @property string|null $reference
 * @property array<array-key, mixed>|null $client_type
 * @property string|null $salutation
 * @property string $first_name
 * @property string|null $last_name
 * @property string $full_name
 * @property string|null $full_name_ar
 * @property int|null $company_id
 * @property string|null $position
 * @property string|null $email
 * @property string|null $mobile
 * @property string|null $phone_alt
 * @property string $preferred_channel
 * @property string|null $nationality
 * @property string|null $address_line1
 * @property string|null $address_line2
 * @property string|null $city
 * @property string|null $emirate
 * @property string|null $country
 * @property \Carbon\CarbonImmutable|null $date_of_birth
 * @property string|null $passport_number
 * @property string|null $passport_hash
 * @property \Carbon\CarbonImmutable|null $passport_expiry
 * @property string|null $emirates_id
 * @property string|null $emirates_id_hash
 * @property string|null $trn
 * @property string $vip_level
 * @property string|null $dietary_preferences
 * @property string|null $allergies
 * @property string|null $notes_vip
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Note> $notes
 * @property int|null $source_id
 * @property int|null $assigned_user_id
 * @property string $kyc_status
 * @property \Carbon\CarbonImmutable|null $kyc_verified_at
 * @property int|null $kyc_verified_by
 * @property \Carbon\CarbonImmutable|null $kyc_expires_on
 * @property string $aml_status
 * @property \Carbon\CarbonImmutable|null $aml_screened_at
 * @property \Carbon\CarbonImmutable|null $marketing_consent_at
 * @property string|null $consent_channel
 * @property string $status
 * @property \Carbon\CarbonImmutable|null $approved_at
 * @property int|null $approved_by
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User|null $assignee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ClientContact> $contacts
 * @property-read int|null $contacts_count
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deal> $deals
 * @property-read int|null $deals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lead> $leads
 * @property-read int|null $leads_count
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Yacht> $ownedYachts
 * @property-read int|null $owned_yachts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\ClientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client ofType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAddressLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAddressLine2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAllergies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAmlScreenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAmlStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAssignedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereBlind(string $field, string $value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereConsentChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereDietaryPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereEmirate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereEmiratesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereEmiratesIdHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereFullNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereKycExpiresOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereKycStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereKycVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereKycVerifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereMarketingConsentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereNationality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereNotesVip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePassportExpiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePassportHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePassportNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePhoneAlt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePreferredChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereSalutation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereTrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereVipLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client withoutOwnerScope()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperClient {}
}

namespace App\Models{
/**
 * A PA, family office or captain who contacts us on the principal's behalf.
 *
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string|null $role
 * @property string|null $email
 * @property string|null $mobile
 * @property bool $is_primary
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\ClientContactFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientContact withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperClientContact {}
}

namespace App\Models{
/**
 * Corporate clients, DMCs, concierges, charter partners and co-brokers.
 *
 * Not scoped to an owner: a DMC is a shared relationship, and hiding it from
 * half the team creates duplicates.
 *
 * @property int $id
 * @property string|null $reference
 * @property string $legal_name
 * @property string|null $trade_name
 * @property string $type
 * @property string|null $trn
 * @property string|null $trn_hash
 * @property string|null $trade_licence_no
 * @property \Carbon\CarbonImmutable|null $licence_expiry
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $address_line1
 * @property string|null $address_line2
 * @property string|null $city
 * @property string|null $emirate
 * @property string|null $country
 * @property string|null $billing_email
 * @property int $payment_terms_days
 * @property numeric|null $commission_rate_default
 * @property string $status
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Note> $notes
 * @property int|null $assigned_user_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User|null $assignee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Client> $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CompanyContact> $contacts
 * @property-read int|null $contacts_count
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\CompanyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereAddressLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereAddressLine2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereAssignedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereBillingEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereBlind(string $field, string $value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCommissionRateDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereEmirate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereLegalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereLicenceExpiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company wherePaymentTermsDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereTradeLicenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereTradeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereTrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereTrnHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCompany {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $company_id
 * @property int|null $client_id
 * @property string $name
 * @property string|null $position
 * @property string|null $email
 * @property string|null $mobile
 * @property bool $is_primary
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\Company|null $company
 * @method static \Database\Factories\CompanyContactFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCompanyContact {}
}

namespace App\Models{
/**
 * The Cost & Offer table as one object with three phases (D-011).
 *
 * Quote → invoice → actuals → P&L is a single artifact, exactly as the client
 * already works. Splitting it into three documents loses the variance analysis
 * that makes it worth keeping.
 *
 * @property int $id
 * @property string|null $reference
 * @property int $booking_id
 * @property string $currency
 * @property numeric $exchange_rate
 * @property numeric $total_offer
 * @property numeric $total_cost
 * @property numeric $total_profit
 * @property numeric $margin_pct
 * @property string $status
 * @property \Carbon\CarbonImmutable|null $closed_at
 * @property int|null $closed_by
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Booking|null $booking
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CostSheetLine> $lines
 * @property-read int|null $lines_count
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\CostSheetFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereClosedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereMarginPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereTotalOffer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereTotalProfit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheet withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCostSheet {}
}

namespace App\Models{
/**
 * One line of the Cost & Offer table, in one of three phases:
 * quoted, invoiced or actual (D-011).
 *
 * @property int $id
 * @property int $cost_sheet_id
 * @property string $phase
 * @property string $section
 * @property string $category
 * @property string|null $description
 * @property numeric $quantity
 * @property numeric $unit_price
 * @property numeric $amount
 * @property numeric $tax_rate
 * @property string $tax_treatment
 * @property numeric $tax_amount
 * @property bool $is_taxable
 * @property int|null $vendor_id
 * @property int|null $crew_id
 * @property array<array-key, mixed>|null $meta
 * @property int $sort_order
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\CostSheet|null $costSheet
 * @method static \Database\Factories\CostSheetLineFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereCostSheetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereCrewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereIsTaxable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine wherePhase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereSection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereTaxTreatment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostSheetLine whereVendorId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCostSheetLine {}
}

namespace App\Models{
/**
 * One board for all three pipelines (D-005).
 *
 * `stage` is the deal's position on the board and is what the gate engine
 * guards; the underlying subject — an enquiry, a listing, a buyer requirement —
 * keeps its own lifecycle status. Conflating the two makes the board
 * undraggable without side effects.
 *
 * @property int $id
 * @property string|null $reference
 * @property int $pipeline_id
 * @property int $stage_id
 * @property int|null $client_id
 * @property int|null $company_id
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property int|null $yacht_id
 * @property string $title
 * @property numeric $value
 * @property string $currency
 * @property \Carbon\CarbonImmutable|null $expected_close_date
 * @property int|null $assigned_user_id
 * @property \Carbon\CarbonImmutable|null $stage_entered_at
 * @property int|null $lost_reason_id
 * @property string|null $lost_notes
 * @property string $status
 * @property \Carbon\CarbonImmutable|null $closed_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User|null $assignee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \App\Models\LostReason|null $lostReason
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Note> $notes
 * @property-read int|null $notes_count
 * @property-read \App\Models\Pipeline $pipeline
 * @property-read \App\Models\PipelineStage $stage
 * @property-read \Illuminate\Database\Eloquent\Model|null $subject
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\User|null $updater
 * @property-read \App\Models\Yacht|null $yacht
 * @method static \Database\Factories\DealFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal open()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereAssignedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereExpectedCloseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereLostNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereLostReasonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal wherePipelineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereStageEnteredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereStageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereYachtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal withoutOwnerScope()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDeal {}
}

namespace App\Models{
/**
 * The vault. Files live on a private disk and are never addressable without a
 * policy check plus a short-lived signed URL (D-015).
 *
 * @property int $id
 * @property string|null $reference
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string $category
 * @property string $title
 * @property string|null $description
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string|null $mime
 * @property int $size
 * @property string|null $checksum
 * @property int $version
 * @property \Carbon\CarbonImmutable|null $issued_on
 * @property \Carbon\CarbonImmutable|null $expires_on
 * @property int $reminder_days
 * @property string $visibility
 * @property bool $is_sensitive
 * @property bool $requires_signature
 * @property \Carbon\CarbonImmutable|null $signed_at
 * @property string $status
 * @property int|null $uploaded_by
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SignatureRequest> $signatureRequests
 * @property-read int|null $signature_requests_count
 * @property-read \Illuminate\Database\Eloquent\Model|null $subject
 * @property-read \App\Models\User|null $updater
 * @property-read \App\Models\User|null $uploader
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DocumentVersion> $versions
 * @property-read int|null $versions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document expiringWithin(int $days)
 * @method static \Database\Factories\DocumentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereChecksum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereExpiresOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereIsSensitive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereIssuedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereReminderDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereRequiresSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDocument {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string $type
 * @property string|null $business_line
 * @property string|null $body_html
 * @property array<array-key, mixed>|null $variables
 * @property int $version
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\DocumentTemplateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereBodyHtml($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereBusinessLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDocumentTemplate {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $document_id
 * @property int $version
 * @property string $path
 * @property int $size
 * @property string|null $checksum
 * @property string|null $note
 * @property int|null $uploaded_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Document|null $document
 * @property-read \App\Models\User|null $uploader
 * @method static \Database\Factories\DocumentVersionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereChecksum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentVersion whereVersion($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDocumentVersion {}
}

namespace App\Models{
/**
 * The rate captured at transaction date, with who captured it (D-002).
 *
 * @property int $id
 * @property string $base
 * @property string $quote
 * @property numeric $rate
 * @property \Carbon\CarbonImmutable $rate_date
 * @property string|null $source
 * @property int|null $captured_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @method static \Database\Factories\ExchangeRateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExchangeRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExchangeRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExchangeRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExchangeRate whereBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExchangeRate whereCapturedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExchangeRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExchangeRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExchangeRate whereQuote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExchangeRate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExchangeRate whereRateDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExchangeRate whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExchangeRate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperExchangeRate {}
}

namespace App\Models{
/**
 * Append-only record of every gate evaluation, pass or fail. This is
 * what makes "why was this charter allowed to sail" answerable.
 *
 * @property int $id
 * @property int|null $gate_rule_id
 * @property string $subject_type
 * @property int $subject_id
 * @property int|null $user_id
 * @property string|null $action_key
 * @property string $result
 * @property array<array-key, mixed>|null $failed_conditions
 * @property array<array-key, mixed>|null $context
 * @property \Carbon\CarbonImmutable $evaluated_at
 * @property-read \App\Models\GateRule|null $rule
 * @method static \Database\Factories\GateEvaluationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateEvaluation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateEvaluation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateEvaluation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateEvaluation whereActionKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateEvaluation whereContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateEvaluation whereEvaluatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateEvaluation whereFailedConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateEvaluation whereGateRuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateEvaluation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateEvaluation whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateEvaluation whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateEvaluation whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateEvaluation whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGateEvaluation {}
}

namespace App\Models{
/**
 * The Override Register. Append-only: no route in this application
 * updates or deletes a row here.
 *
 * @property int $id
 * @property int|null $gate_rule_id
 * @property int|null $gate_evaluation_id
 * @property string $subject_type
 * @property int $subject_id
 * @property int|null $user_id
 * @property string $reason
 * @property array<array-key, mixed>|null $failed_conditions
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property-read \App\Models\GateEvaluation|null $evaluation
 * @property-read \App\Models\GateRule|null $rule
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\GateOverrideFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride whereFailedConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride whereGateEvaluationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride whereGateRuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateOverride whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGateOverride {}
}

namespace App\Models{
/**
 * One guarded transition, as data (D-004).
 *
 * Editing a rule is audited and versioned, so "who loosened the boarding gate,
 * and when" is answerable — which is the whole reason the rules are data and
 * not conditionals.
 *
 * @property int $id
 * @property string $key
 * @property string $name_en
 * @property string|null $name_ar
 * @property string|null $description
 * @property string $subject_type
 * @property string $trigger_type
 * @property string|null $trigger_field
 * @property array<array-key, mixed>|null $trigger_from
 * @property string|null $trigger_to
 * @property string|null $action_key
 * @property string $severity
 * @property array<array-key, mixed> $conditions
 * @property string $block_message_en
 * @property string|null $block_message_ar
 * @property string|null $resolution_route
 * @property string|null $resolution_label
 * @property array<array-key, mixed>|null $creates_task
 * @property bool $is_overridable
 * @property string $override_permission
 * @property bool $requires_reason
 * @property bool $is_active
 * @property int $sort_order
 * @property int $version
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GateEvaluation> $evaluations
 * @property-read int|null $evaluations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GateOverride> $overrides
 * @property-read int|null $overrides_count
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\GateRuleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereActionKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereBlockMessageAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereBlockMessageEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereCreatesTask($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereIsOverridable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereOverridePermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereRequiresReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereResolutionLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereResolutionRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereSeverity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereTriggerField($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereTriggerFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereTriggerTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereTriggerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GateRule withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGateRule {}
}

namespace App\Models{
/**
 * The export a marina or maritime authority receives (Q25).
 *
 * @property int $id
 * @property int $booking_id
 * @property int|null $document_id
 * @property string $format
 * @property string|null $submitted_to
 * @property \Carbon\CarbonImmutable|null $submitted_at
 * @property \Carbon\CarbonImmutable|null $generated_at
 * @property int|null $generated_by
 * @property string $status
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\Booking|null $booking
 * @property-read \App\Models\Document|null $document
 * @method static \Database\Factories\GuestManifestFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest whereFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest whereGeneratedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest whereGeneratedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest whereSubmittedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuestManifest withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGuestManifest {}
}

namespace App\Models{
/**
 * An FTA-compliant tax invoice.
 *
 * Once issued it is never edited or deleted: it is voided and credited, because
 * a tax invoice number is a promise to the authority as much as to the client
 * (D-013). The number itself is gapless and never reissued.
 *
 * @property int $id
 * @property string|null $reference
 * @property string $type
 * @property int|null $client_id
 * @property int|null $company_id
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property int|null $cost_sheet_id
 * @property int|null $credit_note_of_id
 * @property \Carbon\CarbonImmutable|null $issue_date
 * @property \Carbon\CarbonImmutable|null $due_date
 * @property string|null $place_of_supply
 * @property string $tax_treatment
 * @property string $currency
 * @property numeric $exchange_rate
 * @property numeric $subtotal
 * @property numeric $discount
 * @property numeric $tax_amount
 * @property numeric $total
 * @property numeric $amount_paid
 * @property numeric $amount_due
 * @property string|null $supplier_trn
 * @property string|null $buyer_trn
 * @property string $status
 * @property \Carbon\CarbonImmutable|null $issued_at
 * @property \Carbon\CarbonImmutable|null $voided_at
 * @property string|null $void_reason
 * @property string|null $notes
 * @property int|null $pdf_document_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentAllocation> $allocations
 * @property-read int|null $allocations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\CostSheet|null $costSheet
 * @property-read \App\Models\User|null $creator
 * @property-read Invoice|null $creditNoteOf
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Model|null $subject
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\InvoiceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice overdue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereAmountDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereAmountPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereBuyerTrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCostSheetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreditNoteOfId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIssuedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePdfDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePlaceOfSupply($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSupplierTrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTaxTreatment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereVoidReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereVoidedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperInvoice {}
}

namespace App\Models{
/**
 * An invoice line, carrying its own tax treatment so an international charter and a UAE one can sit on one document.
 *
 * @property int $id
 * @property int $invoice_id
 * @property int|null $cost_sheet_line_id
 * @property string $description_en
 * @property string|null $description_ar
 * @property numeric $quantity
 * @property string|null $unit
 * @property numeric $unit_price
 * @property numeric $discount
 * @property numeric $tax_rate
 * @property string $tax_treatment
 * @property numeric $tax_amount
 * @property numeric $line_total
 * @property int $sort_order
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\CostSheetLine|null $costSheetLine
 * @property-read \App\Models\Invoice|null $invoice
 * @method static \Database\Factories\InvoiceItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereCostSheetLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereDescriptionAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereDescriptionEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereTaxTreatment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperInvoiceItem {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $reference
 * @property string $business_line
 * @property int|null $source_id
 * @property int|null $client_id
 * @property int|null $company_id
 * @property string $name
 * @property string|null $email
 * @property string|null $mobile
 * @property string|null $message
 * @property array<array-key, mixed>|null $meta
 * @property string $status
 * @property int|null $assigned_user_id
 * @property int|null $duplicate_of_id
 * @property int|null $duplicate_score
 * @property \Carbon\CarbonImmutable|null $duplicate_checked_at
 * @property \Carbon\CarbonImmutable|null $first_response_at
 * @property \Carbon\CarbonImmutable|null $sla_due_at
 * @property \Carbon\CarbonImmutable|null $next_follow_up_at
 * @property \Carbon\CarbonImmutable|null $converted_at
 * @property string|null $converted_to_type
 * @property int|null $converted_to_id
 * @property string|null $unqualified_reason
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User|null $assignee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Model|null $convertedTo
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read Lead|null $duplicateOf
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Note> $notes
 * @property-read int|null $notes_count
 * @property-read \App\Models\LeadSource|null $source
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\LeadFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereAssignedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereBusinessLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereConvertedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereConvertedToId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereConvertedToType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereDuplicateCheckedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereDuplicateOfId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereDuplicateScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereFirstResponseAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereNextFollowUpAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereSlaDueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereUnqualifiedReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead withoutOwnerScope()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lead withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperLead {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $channel
 * @property string|null $utm_key
 * @property bool $is_active
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lead> $leads
 * @property-read int|null $leads_count
 * @method static \Database\Factories\LeadSourceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource whereUtmKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeadSource withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperLeadSource {}
}

namespace App\Models{
/**
 * Settings → Lists. Every dropdown the business wants to change without a
 * deployment: experience types, incident categories, cabin types, and so on.
 *
 * @property int $id
 * @property string $list_key
 * @property string $value
 * @property string $label_en
 * @property string|null $label_ar
 * @property string|null $colour_token
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $is_system
 * @property array<array-key, mixed>|null $meta
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @method static \Database\Factories\ListOptionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption list(string $key)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption whereColourToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption whereLabelAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption whereLabelEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption whereListKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListOption withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperListOption {}
}

namespace App\Models{
/**
 * "Closed Lost" without a reason is unreportable, so the reason is a record.
 *
 * @property int $id
 * @property int|null $pipeline_id
 * @property string $label
 * @property int $sort_order
 * @property bool $is_active
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\Pipeline|null $pipeline
 * @method static \Database\Factories\LostReasonFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason wherePipelineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LostReason withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperLostReason {}
}

namespace App\Models{
/**
 * A marina carries its own timezone: Seychelles and the Maldives share a fleet
 * calendar with the UAE, and charter instants are derived from the departure
 * marina rather than assumed (D-010).
 *
 * @property int $id
 * @property string $name
 * @property string|null $name_ar
 * @property string $country
 * @property string|null $emirate
 * @property string|null $city
 * @property string $timezone
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $contact_name
 * @property string|null $contact_phone
 * @property string|null $contact_email
 * @property bool $requires_manifest
 * @property string|null $manifest_format
 * @property string|null $notes
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Berth> $berths
 * @property-read int|null $berths_count
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\User|null $updater
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Yacht> $yachts
 * @property-read int|null $yachts_count
 * @method static \Database\Factories\MarinaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereEmirate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereManifestFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereRequiresManifest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Marina withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMarina {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $subject_type
 * @property int $subject_id
 * @property int|null $user_id
 * @property string $body
 * @property bool $is_internal
 * @property bool $is_vip
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Model $subject
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\NoteFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereIsInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereIsVip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Note withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperNote {}
}

namespace App\Models{
/**
 * The pre-departure checklist for one charter.
 *
 * @property int $id
 * @property string|null $reference
 * @property int $booking_id
 * @property int|null $checklist_template_id
 * @property string $status
 * @property int $completion_pct
 * @property \Carbon\CarbonImmutable|null $started_at
 * @property \Carbon\CarbonImmutable|null $completed_at
 * @property int|null $completed_by
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\Booking|null $booking
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChecklistItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User|null $updater
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereChecklistTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereCompletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereCompletionPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperationsChecklist withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperOperationsChecklist {}
}

namespace App\Models{
/**
 * The revenue-share model behind every owner statement (Q22).
 *
 * @property int $id
 * @property string|null $reference
 * @property int $yacht_id
 * @property int $owner_client_id
 * @property string $type
 * @property string $revenue_share_model
 * @property numeric $owner_share_pct
 * @property numeric $company_share_pct
 * @property string $statement_frequency
 * @property \Carbon\CarbonImmutable|null $starts_on
 * @property \Carbon\CarbonImmutable|null $ends_on
 * @property bool $auto_renew
 * @property int $notice_days
 * @property int|null $document_id
 * @property array<array-key, mixed>|null $deductible_categories
 * @property string $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Document|null $document
 * @property-read \App\Models\Client|null $owner
 * @property-read \App\Models\User|null $updater
 * @property-read \App\Models\Yacht|null $yacht
 * @method static \Database\Factories\OwnerAgreementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereAutoRenew($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereCompanySharePct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereDeductibleCategories($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereEndsOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereNoticeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereOwnerClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereOwnerSharePct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereRevenueShareModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereStartsOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereStatementFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement whereYachtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerAgreement withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperOwnerAgreement {}
}

namespace App\Models{
/**
 * Money in.
 *
 * `cleared_at` is the field that matters: Operational Release and ownership
 * transfer both read cleared money, never money that has merely been promised
 * or shown as a screenshot of a transfer.
 *
 * @property int $id
 * @property string|null $reference
 * @property int|null $client_id
 * @property string $method
 * @property string|null $gateway
 * @property string|null $gateway_reference
 * @property numeric $amount
 * @property string $currency
 * @property numeric $exchange_rate
 * @property numeric $amount_aed
 * @property \Carbon\CarbonImmutable|null $received_at
 * @property \Carbon\CarbonImmutable|null $cleared_at
 * @property string $status
 * @property numeric|null $bank_charge_amount
 * @property numeric|null $bank_charge_vat
 * @property int|null $proof_document_id
 * @property \Carbon\CarbonImmutable|null $reconciled_at
 * @property int|null $reconciled_by
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentAllocation> $allocations
 * @property-read int|null $allocations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Receipt|null $receipt
 * @property-read \App\Models\User|null $updater
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment cleared()
 * @method static \Database\Factories\PaymentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmountAed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereBankChargeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereBankChargeVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereClearedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereGatewayReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereProofDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReceivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReconciledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReconciledBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPayment {}
}

namespace App\Models{
/**
 * Which invoice, and which scheduled instalment, a payment settled.
 *
 * @property int $id
 * @property int $payment_id
 * @property int|null $invoice_id
 * @property int|null $payment_schedule_item_id
 * @property numeric $amount
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \App\Models\Payment|null $payment
 * @property-read \App\Models\PaymentScheduleItem|null $scheduleItem
 * @method static \Database\Factories\PaymentAllocationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAllocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAllocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAllocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAllocation whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAllocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAllocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAllocation whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAllocation wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAllocation wherePaymentScheduleItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentAllocation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPaymentAllocation {}
}

namespace App\Models{
/**
 * The payment plan behind a booking: deposit, balance, APA.
 *
 * @property int $id
 * @property int|null $booking_id
 * @property int|null $invoice_id
 * @property string $name
 * @property numeric $total_amount
 * @property string $currency
 * @property string $status
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\Booking|null $booking
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentScheduleItem> $items
 * @property-read int|null $items_count
 * @method static \Database\Factories\PaymentScheduleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSchedule withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPaymentSchedule {}
}

namespace App\Models{
/**
 * One instalment: deposit, balance, APA. The deposit row is what the
 * Operational Release gate reads.
 *
 * @property int $id
 * @property int $payment_schedule_id
 * @property int $sequence
 * @property string $label
 * @property numeric|null $percentage
 * @property numeric $amount
 * @property \Carbon\CarbonImmutable|null $due_at
 * @property string $status
 * @property \Carbon\CarbonImmutable|null $paid_at
 * @property int|null $invoice_id
 * @property \Carbon\CarbonImmutable|null $reminder_sent_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentAllocation> $allocations
 * @property-read int|null $allocations_count
 * @property-read \App\Models\PaymentSchedule|null $schedule
 * @method static \Database\Factories\PaymentScheduleItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem whereDueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem wherePaymentScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem whereReminderSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentScheduleItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPaymentScheduleItem {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string|null $name_ar
 * @property bool $is_active
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deal> $deals
 * @property-read int|null $deals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PipelineStage> $stages
 * @property-read int|null $stages_count
 * @method static \Database\Factories\PipelineFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pipeline whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPipeline {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $pipeline_id
 * @property string $key
 * @property string $name
 * @property string|null $name_ar
 * @property int $sort_order
 * @property string $colour_token
 * @property int $probability
 * @property bool $is_won
 * @property bool $is_lost
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deal> $deals
 * @property-read int|null $deals_count
 * @property-read \App\Models\Pipeline $pipeline
 * @method static \Database\Factories\PipelineStageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereColourToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereIsLost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereIsWon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage wherePipelineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereProbability($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipelineStage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPipelineStage {}
}

namespace App\Models{
/**
 * A line on a proposal. Totals are computed, never posted from the browser.
 *
 * @property int $id
 * @property int $charter_proposal_id
 * @property int|null $yacht_id
 * @property string $type
 * @property string|null $category
 * @property string $description_en
 * @property string|null $description_ar
 * @property numeric $quantity
 * @property string|null $unit
 * @property numeric $unit_price
 * @property numeric $tax_rate
 * @property string $tax_treatment
 * @property numeric $tax_amount
 * @property numeric $line_total
 * @property int $sort_order
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\CharterProposal|null $proposal
 * @property-read \App\Models\Yacht|null $yacht
 * @method static \Database\Factories\ProposalItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereCharterProposalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereDescriptionAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereDescriptionEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereTaxTreatment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProposalItem whereYachtId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperProposalItem {}
}

namespace App\Models{
/**
 * Brokerage and management quote outside the charter proposal flow.
 *
 * @property int $id
 * @property string|null $reference
 * @property int|null $client_id
 * @property int|null $company_id
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string $business_line
 * @property \Carbon\CarbonImmutable|null $issued_on
 * @property \Carbon\CarbonImmutable|null $valid_until
 * @property string $currency
 * @property numeric $exchange_rate
 * @property numeric $subtotal
 * @property numeric $discount
 * @property numeric $tax_amount
 * @property numeric $total
 * @property string $status
 * @property int|null $converted_invoice_id
 * @property int|null $pdf_document_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuotationItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\QuotationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereBusinessLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereConvertedInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereIssuedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation wherePdfDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperQuotation {}
}

namespace App\Models{
/**
 * A quotation line.
 *
 * @property int $id
 * @property int $quotation_id
 * @property string $description_en
 * @property string|null $description_ar
 * @property numeric $quantity
 * @property numeric $unit_price
 * @property numeric $tax_rate
 * @property string $tax_treatment
 * @property numeric $tax_amount
 * @property numeric $line_total
 * @property int $sort_order
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Quotation|null $quotation
 * @method static \Database\Factories\QuotationItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem whereDescriptionAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem whereDescriptionEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem whereQuotationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem whereTaxTreatment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperQuotationItem {}
}

namespace App\Models{
/**
 * Issued against a cleared payment. A deal cannot close without one for every payment.
 *
 * @property int $id
 * @property string|null $reference
 * @property int $payment_id
 * @property int|null $client_id
 * @property \Carbon\CarbonImmutable|null $issued_at
 * @property numeric $amount
 * @property string $currency
 * @property int|null $pdf_document_id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\Payment|null $payment
 * @method static \Database\Factories\ReceiptFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt whereIssuedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt wherePdfDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Receipt withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperReceipt {}
}

namespace App\Models{
/**
 * Append-only. Written whenever a VIP field, a guest manifest or a private
 * document is read or exported. Nothing in the application updates or deletes
 * these rows.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $subject_type
 * @property int $subject_id
 * @property string $field_group
 * @property string $action
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\CarbonImmutable $occurred_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereFieldGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereOccurredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecordAccessLog whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperRecordAccessLog {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $module
 * @property string $name
 * @property array<array-key, mixed>|null $filters
 * @property array<array-key, mixed>|null $columns
 * @property bool $is_shared
 * @property bool $is_default
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\SavedViewFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereColumns($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereIsShared($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavedView withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSavedView {}
}

namespace App\Models{
/**
 * Held against damage. It is not released until the damage inspection is
 * closed — that is a hard gate, and this is the record it acts on.
 *
 * @property int $id
 * @property int $booking_id
 * @property numeric $amount
 * @property string $currency
 * @property string $method
 * @property \Carbon\CarbonImmutable|null $collected_at
 * @property int|null $collected_by
 * @property string|null $hold_reference
 * @property string $status
 * @property numeric|null $released_amount
 * @property \Carbon\CarbonImmutable|null $released_at
 * @property int|null $released_by
 * @property string|null $deduction_reason
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Booking|null $booking
 * @method static \Database\Factories\SecurityDepositFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereCollectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereCollectedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereDeductionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereHoldReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereReleasedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereReleasedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereReleasedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityDeposit withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSecurityDeposit {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $group
 * @property string $key
 * @property array<array-key, mixed>|null $value
 * @property bool $is_encrypted
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereIsEncrypted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValue($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSetting {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $document_id
 * @property string $provider
 * @property string|null $provider_ref
 * @property int|null $signer_client_id
 * @property string $signer_name
 * @property string $signer_email
 * @property \Carbon\CarbonImmutable|null $sent_at
 * @property \Carbon\CarbonImmutable|null $viewed_at
 * @property \Carbon\CarbonImmutable|null $signed_at
 * @property \Carbon\CarbonImmutable|null $declined_at
 * @property string|null $decline_reason
 * @property string|null $ip_address
 * @property array<array-key, mixed>|null $audit_trail
 * @property string $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Document|null $document
 * @property-read \App\Models\Client|null $signer
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\SignatureRequestFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereAuditTrail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereDeclineReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereDeclinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereProviderRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereSignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereSignerClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereSignerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereSignerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest whereViewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignatureRequest withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSignatureRequest {}
}

namespace App\Models{
/**
 * Seven-day, single-purpose, session-free client links (brief §4).
 *
 * The token is stored hashed, so a leaked database row cannot be replayed, and
 * each link grants exactly one capability on exactly one record — never a
 * session, and never sight of anything else.
 *
 * @property int $id
 * @property string $token_hash
 * @property string $purpose
 * @property string $subject_type
 * @property int $subject_id
 * @property int|null $client_id
 * @property \Carbon\CarbonImmutable $expires_at
 * @property int $max_uses
 * @property int $used_count
 * @property \Carbon\CarbonImmutable|null $last_used_at
 * @property string|null $last_ip
 * @property \Carbon\CarbonImmutable|null $revoked_at
 * @property int|null $created_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model $subject
 * @method static \Database\Factories\SignedLinkFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereLastIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereMaxUses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereRevokedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereTokenHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereUsedCount($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSignedLink {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $colour_token
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @method static \Database\Factories\TagFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereColourToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTag {}
}

namespace App\Models{
/**
 * The "Next Action" object from the flowcharts. Created by hand, by a workflow,
 * or by a soft gate that wants someone to look at something.
 *
 * @property int $id
 * @property string|null $reference
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string $title
 * @property string|null $description
 * @property string $type
 * @property string $priority
 * @property int|null $assigned_user_id
 * @property string|null $assigned_role
 * @property \Carbon\CarbonImmutable|null $due_at
 * @property string $status
 * @property \Carbon\CarbonImmutable|null $completed_at
 * @property int|null $completed_by
 * @property \Carbon\CarbonImmutable|null $escalate_at
 * @property int|null $escalated_to
 * @property \Carbon\CarbonImmutable|null $escalated_at
 * @property string $source
 * @property string|null $source_key
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\User|null $assignee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Model|null $subject
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\TaskFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task open()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task overdue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereAssignedRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereAssignedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereCompletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereDueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereEscalateAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereEscalatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereEscalatedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereSourceKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Task withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTask {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $business_line
 * @property int|null $lead_user_id
 * @property bool $is_active
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\User|null $lead
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @method static \Database\Factories\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereBusinessLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereLeadUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTeam {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $avatar_path
 * @property string|null $job_title
 * @property string $locale
 * @property string $timezone
 * @property string $chrome
 * @property string $accent
 * @property bool $is_active
 * @property \Carbon\CarbonImmutable|null $last_login_at
 * @property string|null $last_login_ip
 * @property \Carbon\CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property \Carbon\CarbonImmutable|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $ledTeams
 * @property-read int|null $led_teams_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SavedView> $savedViews
 * @property-read int|null $saved_views_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAccent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatarPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereChrome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

namespace App\Models{
/**
 * Never hardcode 5%: the rate, the treatment and the dates it applies
 * are data the finance team can correct without a deployment (Q5).
 *
 * @property int $id
 * @property string $code
 * @property string $label
 * @property numeric $rate_pct
 * @property string $treatment
 * @property \Carbon\CarbonImmutable|null $effective_from
 * @property \Carbon\CarbonImmutable|null $effective_to
 * @property bool $is_default
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @method static \Database\Factories\VatRateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate whereEffectiveFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate whereEffectiveTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate whereRatePct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate whereTreatment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VatRate withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperVatRate {}
}

namespace App\Models{
/**
 * One hull, three capability flags (D-003).
 *
 * The same 40-metre yacht is routinely chartered, listed for sale and managed
 * at the same time. Splitting that across three tables guarantees three
 * versions of its specs, photos and availability — so the commercial terms live
 * in profiles hanging off this record instead.
 *
 * @property int $id
 * @property string|null $reference
 * @property string $name
 * @property string|null $name_ar
 * @property bool $is_charter_fleet
 * @property bool $is_for_sale
 * @property bool $is_managed
 * @property string|null $builder
 * @property string|null $model
 * @property int|null $year_built
 * @property int|null $year_refit
 * @property numeric|null $loa_m
 * @property numeric|null $beam_m
 * @property numeric|null $draft_m
 * @property int|null $gross_tonnage
 * @property string|null $hull_material
 * @property string|null $exterior_designer
 * @property string|null $interior_designer
 * @property string|null $engines
 * @property int|null $engine_hours
 * @property int|null $cruising_speed_kn
 * @property int|null $max_speed_kn
 * @property int|null $fuel_capacity_l
 * @property int|null $water_capacity_l
 * @property int|null $capacity_static
 * @property int|null $capacity_cruising
 * @property int|null $cabins
 * @property int|null $berths
 * @property int|null $crew_count
 * @property string|null $flag_country
 * @property string|null $registration_no
 * @property string|null $imo_no
 * @property string|null $mmsi
 * @property int|null $home_marina_id
 * @property int|null $berth_id
 * @property int|null $owner_client_id
 * @property string|null $description
 * @property string|null $description_ar
 * @property string $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\YachtAvailabilityBlock> $availabilityBlocks
 * @property-read int|null $availability_blocks_count
 * @property-read \App\Models\Berth|null $berth
 * @property-read \App\Models\YachtCharterProfile|null $charterProfile
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \App\Models\Marina|null $homeMarina
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\YachtInventoryItem> $inventory
 * @property-read int|null $inventory_count
 * @property-read \App\Models\YachtManagementProfile|null $managementProfile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\YachtMedia> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Note> $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OwnerAgreement> $ownerAgreements
 * @property-read int|null $owner_agreements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Client> $owners
 * @property-read int|null $owners_count
 * @property-read \App\Models\YachtSaleProfile|null $saleProfile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\User|null $updater
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht charterFleet()
 * @method static \Database\Factories\YachtFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht forSale()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht managed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereBeamM($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereBerthId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereBerths($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereBuilder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereCabins($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereCapacityCruising($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereCapacityStatic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereCrewCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereCruisingSpeedKn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereDescriptionAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereDraftM($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereEngineHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereEngines($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereExteriorDesigner($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereFlagCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereFuelCapacityL($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereGrossTonnage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereHomeMarinaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereHullMaterial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereImoNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereInteriorDesigner($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereIsCharterFleet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereIsForSale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereIsManaged($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereLoaM($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereMaxSpeedKn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereMmsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereOwnerClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereRegistrationNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereWaterCapacityL($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereYearBuilt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht whereYearRefit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Yacht withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperYacht {}
}

namespace App\Models{
/**
 * The single writer of fleet occupancy.
 *
 * Bookings, option holds, maintenance windows and owner use all create a block
 * here, so "is this yacht free?" is one question against one table rather than
 * four joins that will eventually disagree.
 *
 * @property int $id
 * @property int $yacht_id
 * @property \Carbon\CarbonImmutable $starts_at
 * @property \Carbon\CarbonImmutable $ends_at
 * @property string $type
 * @property string|null $source_type
 * @property int|null $source_id
 * @property \Carbon\CarbonImmutable|null $expires_at
 * @property string|null $note
 * @property int|null $created_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Model|null $source
 * @property-read \App\Models\Yacht|null $yacht
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock effective()
 * @method static \Database\Factories\YachtAvailabilityBlockFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock overlapping(\DateTimeInterface $from, \DateTimeInterface $to)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock whereYachtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtAvailabilityBlock withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperYachtAvailabilityBlock {}
}

namespace App\Models{
/**
 * Charter commerce, kept off the hull record so specs and pricing evolve apart.
 *
 * @property int $id
 * @property int $yacht_id
 * @property numeric|null $hourly_rate
 * @property numeric|null $half_day_rate
 * @property numeric|null $full_day_rate
 * @property numeric|null $overnight_rate
 * @property numeric|null $weekly_rate
 * @property numeric $peak_multiplier
 * @property string $currency
 * @property int $min_hours
 * @property numeric|null $apa_percentage
 * @property array<array-key, mixed>|null $included_extras
 * @property int|null $cancellation_policy_id
 * @property bool $is_bookable
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Yacht|null $yacht
 * @method static \Database\Factories\YachtCharterProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereApaPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereCancellationPolicyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereFullDayRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereHalfDayRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereIncludedExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereIsBookable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereMinHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereOvernightRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile wherePeakMultiplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereWeeklyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtCharterProfile whereYachtId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperYachtCharterProfile {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $yacht_id
 * @property string $category
 * @property string $name
 * @property int $quantity
 * @property string $condition
 * @property \Carbon\CarbonImmutable|null $last_checked_at
 * @property int|null $checked_by
 * @property string|null $notes
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\Yacht|null $yacht
 * @method static \Database\Factories\YachtInventoryItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereCheckedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereLastCheckedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem whereYachtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtInventoryItem withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperYachtInventoryItem {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $yacht_id
 * @property int|null $agreement_id
 * @property int|null $technical_manager_id
 * @property numeric|null $budget_annual
 * @property string $reporting_cadence
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\User|null $technicalManager
 * @property-read \App\Models\Yacht|null $yacht
 * @method static \Database\Factories\YachtManagementProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereAgreementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereBudgetAnnual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereReportingCadence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereTechnicalManagerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtManagementProfile whereYachtId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperYachtManagementProfile {}
}

namespace App\Models{
/**
 * is_public decides what the website sync is allowed to publish (Q17).
 *
 * @property int $id
 * @property int $yacht_id
 * @property string $collection
 * @property string $disk
 * @property string $path
 * @property string|null $original_name
 * @property string|null $mime
 * @property int $size
 * @property string|null $alt_en
 * @property string|null $alt_ar
 * @property int $sort_order
 * @property bool $is_public
 * @property int|null $uploaded_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\Yacht|null $yacht
 * @method static \Database\Factories\YachtMediaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereAltAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereAltEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia whereYachtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtMedia withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperYachtMedia {}
}

namespace App\Models{
/**
 * Ownership can be shared, so this is a record rather than a foreign key.
 *
 * @property int $id
 * @property int $yacht_id
 * @property int $client_id
 * @property numeric $ownership_percentage
 * @property bool $is_primary
 * @property \Carbon\CarbonImmutable|null $since
 * @property \Carbon\CarbonImmutable|null $until
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\Yacht|null $yacht
 * @method static \Database\Factories\YachtOwnerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereOwnershipPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereSince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner whereYachtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtOwner withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperYachtOwner {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $yacht_id
 * @property numeric|null $asking_price
 * @property string $currency
 * @property string $price_visibility
 * @property string|null $vat_status
 * @property bool $is_price_negotiable
 * @property int|null $last_valuation_id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Yacht|null $yacht
 * @method static \Database\Factories\YachtSaleProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereAskingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereIsPriceNegotiable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereLastValuationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile wherePriceVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereVatStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YachtSaleProfile whereYachtId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperYachtSaleProfile {}
}

