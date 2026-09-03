<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CrewAssignment;
use App\Models\Listing;
use App\Models\OwnerStatement;
use App\Models\SignedLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The portals: what an owner, a partner broker or a crew member is allowed to
 * see without an account.
 *
 * Three rules hold across all of them, and they are enforced here rather than
 * trusted to each screen:
 *
 * 1. A token opens one purpose. An owner-statement link does not open a crew
 *    sheet, even if the token is valid — `resolve()` matches on purpose.
 * 2. The payload is built by hand, field by field. No API Resource, no model
 *    serialisation, so a column added later cannot leak by default.
 * 3. Nothing here writes. There is no action a portal visitor can take that
 *    changes a business record.
 *
 * An invalid, expired, revoked or exhausted token gets a 404, never a 403: a
 * 403 confirms the link once existed, which is more than a stranger holding a
 * guessed URL should learn.
 */
class PortalController extends Controller
{
    /** What an owner sees: one statement, their own numbers, nothing else. */
    public function ownerStatement(Request $request, string $token): Response
    {
        $link = $this->open($request, $token, 'owner.statement');

        /** @var OwnerStatement|null $statement */
        $statement = $link->subject;

        if (! $statement instanceof OwnerStatement) {
            throw new NotFoundHttpException;
        }

        $statement->loadMissing(['yacht:id,name', 'agreement:id,reference,scope']);

        return Inertia::render('Portal/OwnerStatement', [
            'statement' => [
                'reference' => $statement->reference,
                'yacht' => $statement->yacht?->name,
                'scope' => $statement->agreement?->scope,
                'period_start' => $statement->period_start?->toDateString(),
                'period_end' => $statement->period_end?->toDateString(),
                'charter_revenue' => $statement->charter_revenue,
                'management_fee' => $statement->management_fee,
                'operating_costs' => $statement->operating_costs,
                'maintenance_costs' => $statement->maintenance_costs,
                'crew_costs' => $statement->crew_costs,
                'net_to_owner' => $statement->net_to_owner,
                'currency' => $statement->currency,
                'issued_at' => $statement->issued_at?->toIso8601String(),
                'status' => $statement->status,
            ],
            'expires_at' => $link->expires_at?->toIso8601String(),
        ]);
    }

    /**
     * What a crew member sees on their phone at the marina: where to be, when,
     * and on which yacht. No guest names, no client details, no money.
     */
    public function crewAssignment(Request $request, string $token): Response
    {
        $link = $this->open($request, $token, 'crew.assignment');

        /** @var CrewAssignment|null $assignment */
        $assignment = $link->subject;

        if (! $assignment instanceof CrewAssignment) {
            throw new NotFoundHttpException;
        }

        $assignment->loadMissing(['crew:id,full_name,role', 'booking:id,reference,starts_at,ends_at,yacht_id', 'booking.yacht:id,name']);

        return Inertia::render('Portal/CrewAssignment', [
            'assignment' => [
                'crew' => $assignment->crew?->full_name,
                'role' => $assignment->role ?? $assignment->crew?->role,
                'yacht' => $assignment->booking?->yacht?->name,
                'reference' => $assignment->booking?->reference,
                'starts_at' => $assignment->starts_at->toIso8601String(),
                'ends_at' => $assignment->ends_at->toIso8601String(),
                'status' => $assignment->status,
                'dispatched' => $assignment->isDispatched(),
            ],
            'expires_at' => $link->expires_at?->toIso8601String(),
        ]);
    }

    /**
     * What a partner broker sees: the listing as it would be presented, and
     * nothing about the seller. The reserve price never appears here.
     */
    public function listing(Request $request, string $token): Response
    {
        $link = $this->open($request, $token, 'partner.listing');

        /** @var Listing|null $listing */
        $listing = $link->subject;

        if (! $listing instanceof Listing) {
            throw new NotFoundHttpException;
        }

        $listing->loadMissing('yacht:id,name,builder,year_built,loa_m,capacity_cruising');

        return Inertia::render('Portal/Listing', [
            'listing' => [
                'reference' => $listing->reference,
                'yacht' => $listing->yacht?->name,
                'builder' => $listing->yacht?->builder,
                'year_built' => $listing->yacht?->year_built,
                'loa_m' => $listing->yacht?->loa_m,
                'guests' => $listing->yacht?->capacity_cruising,
                'asking_price' => $listing->asking_price,
                'currency' => $listing->currency,
                'commission_rate' => $listing->commission_rate,
                'mandate_type' => $listing->mandate_type,
                'requires_nda' => $listing->requires_nda,
                'requires_proof_of_funds' => $listing->requires_proof_of_funds,
                'marketing_summary' => $listing->marketing_summary,
                'status' => $listing->status,
            ],
            'expires_at' => $link->expires_at?->toIso8601String(),
        ]);
    }

    /**
     * Resolve a token for exactly one purpose, record the use, and hand back
     * the link. Everything that can go wrong looks identical from outside.
     */
    private function open(Request $request, string $token, string $purpose): SignedLink
    {
        $link = SignedLink::resolve($token, $purpose);

        if ($link === null) {
            // The token itself never reaches the log — only that a link of this
            // purpose was refused, which is what an operator needs to know.
            Log::info('Portal link refused', ['purpose' => $purpose]);

            throw new NotFoundHttpException;
        }

        $link->registerUse($request->ip());

        return $link;
    }
}
