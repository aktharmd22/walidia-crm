<?php

declare(strict_types=1);

namespace App\Http\Controllers\Charter;

use App\Domain\Charter\CharterMatcher;
use App\Http\Controllers\ResourceController;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\CharterEnquiryResource;
use App\Models\CharterEnquiry;
use App\Models\Client;
use App\Models\ListOption;
use App\Models\Marina;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @extends ResourceController<CharterEnquiry>
 */
class CharterEnquiryController extends ResourceController
{
    protected string $model = CharterEnquiry::class;

    protected string $name = 'charter-enquiries';

    protected string $pages = 'Charter/Enquiries';

    protected string $resource = CharterEnquiryResource::class;

    protected ?string $routePrefix = 'charter.enquiries';

    protected array $indexWith = ['client:id,full_name', 'assignee:id,name', 'pickupMarina:id,name'];

    protected array $showWith = ['client', 'assignee', 'pickupMarina', 'dropoffMarina', 'matches.yacht.charterProfile', 'proposals'];

    protected array $sortable = ['reference', 'requested_date', 'status', 'created_at'];

    protected string $defaultSort = '-created_at';

    protected array $filterable = ['status', 'experience_type', 'assigned_user_id'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CharterEnquiry::class);

        $enquiry = CharterEnquiry::create($this->validated($request) + [
            'status' => 'new',
            'assigned_user_id' => $request->input('assigned_user_id', $request->user()->id),
        ]);

        $enquiry->logActivity('system', 'Enquiry logged');

        return redirect()->route('charter.enquiries.show', $enquiry)
            ->with('success', "Enquiry {$enquiry->reference} created.");
    }

    public function update(Request $request, CharterEnquiry $enquiry): RedirectResponse
    {
        $this->authorize('update', $enquiry);

        $enquiry->update($this->validated($request));

        return back()->with('success', 'Enquiry updated.');
    }

    /**
     * The matching screen. Scores are recomputed on demand rather than cached,
     * because availability moves and a stale shortlist is worse than none.
     */
    public function matching(Request $request, CharterEnquiry $enquiry, CharterMatcher $matcher): Response
    {
        $this->authorize('view', $enquiry);

        if ($request->boolean('refresh') || $enquiry->matches()->count() === 0) {
            $matcher->match($enquiry);
        }

        $enquiry->load('matches.yacht.charterProfile', 'matches.yacht.homeMarina');

        return Inertia::render('Charter/Enquiries/Matching', [
            'record' => CharterEnquiryResource::make($enquiry)->resolve(),
            'matches' => $enquiry->matches->map(fn ($match): array => [
                'id' => $match->id,
                'score' => $match->score,
                'reasons' => $match->reasons ?? [],
                'is_shortlisted' => $match->is_shortlisted,
                'yacht' => [
                    'id' => $match->yacht->id,
                    'name' => $match->yacht->name,
                    'builder' => $match->yacht->builder,
                    'loa_m' => $match->yacht->loa_m,
                    'capacity_cruising' => $match->yacht->capacity_cruising,
                    'marina' => $match->yacht->homeMarina?->name,
                    'day_rate' => $match->yacht->charterProfile?->full_day_rate,
                    'currency' => $match->yacht->charterProfile?->currency ?? 'AED',
                    'url' => route('fleet.yachts.show', $match->yacht->id),
                ],
            ]),
        ]);
    }

    public function shortlist(Request $request, CharterEnquiry $enquiry): RedirectResponse
    {
        $this->authorize('update', $enquiry);

        $data = $request->validate([
            'match_id' => ['required', 'integer'],
            'shortlisted' => ['required', 'boolean'],
        ]);

        $enquiry->matches()->whereKey($data['match_id'])->update(['is_shortlisted' => $data['shortlisted']]);

        return back();
    }

    /* ── hooks ──────────────────────────────────────────────────────────── */

    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'clients' => Client::query()->orderBy('full_name')->limit(500)->get(['id', 'full_name'])
                ->map(fn (Client $client): array => ['value' => $client->id, 'label' => $client->full_name]),
            'marinas' => Marina::where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ->map(fn (Marina $marina): array => ['value' => $marina->id, 'label' => $marina->name]),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ->map(fn (User $user): array => ['value' => $user->id, 'label' => $user->name]),
            'experienceTypes' => ListOption::options('experience_type'),
            'occasions' => ListOption::options('occasion'),
        ];
    }

    protected function showProps(Request $request, Model $record): array
    {
        /** @var CharterEnquiry $record */
        return [
            'timeline' => ActivityResource::collection(
                $record->activities()->with('user:id,name')->limit(25)->get(),
            )->resolve(),
            'proposals' => $record->proposals->map(fn ($proposal): array => [
                'id' => $proposal->id,
                'reference' => $proposal->reference,
                'version' => $proposal->version,
                'total' => $proposal->total,
                'currency' => $proposal->currency,
                'status' => $proposal->status,
                'status_tone' => $proposal->statusTone(),
                'valid_until' => $proposal->valid_until?->toDateString(),
                'url' => route('charter.proposals.show', $proposal->id),
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
            'experience_type' => ['nullable', 'string', 'max:48'],
            'occasion' => ['nullable', 'string', 'max:48'],
            'requested_date' => ['nullable', 'date'],
            'alternative_dates' => ['nullable', 'array'],
            'duration_hours' => ['nullable', 'numeric', 'min:1', 'max:720'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'guests_adults' => ['required', 'integer', 'min:0', 'max:500'],
            'guests_children' => ['required', 'integer', 'min:0', 'max:500'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'pickup_marina_id' => ['nullable', 'integer', 'exists:marinas,id'],
            'dropoff_marina_id' => ['nullable', 'integer', 'exists:marinas,id'],
            'yacht_preference_id' => ['nullable', 'integer', 'exists:yachts,id'],
            'itinerary_notes' => ['nullable', 'string', 'max:5000'],
            'requested_extras' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', Rule::in(['new', 'matching', 'proposed', 'won', 'lost', 'cancelled'])],
        ]);
    }
}
