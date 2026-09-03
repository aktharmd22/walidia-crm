<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\LoyaltyRewardResource;
use App\Models\Client;
use App\Models\LoyaltyReward;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Gift vouchers, points and upgrades — the reason a client comes back rather than shops around.
 *
 * @extends ResourceController<LoyaltyReward>
 */
class LoyaltyRewardController extends ResourceController
{
    protected string $model = LoyaltyReward::class;

    protected string $name = 'loyalty-rewards';

    protected string $pages = 'Crm/Rewards';

    protected string $resource = LoyaltyRewardResource::class;

    protected ?string $routePrefix = 'crm.rewards';

    protected array $indexWith = ['client:id,full_name'];

    protected array $showWith = ['client', 'booking'];

    protected array $sortable = ['reference', 'expires_on', 'value', 'status'];

    protected string $defaultSort = '-created_at';

    protected array $filterable = ['type', 'status', 'client_id'];

    public function update(Request $request, LoyaltyReward $loyaltyReward): RedirectResponse
    {
        $this->authorize('update', $loyaltyReward);

        $loyaltyReward->update($this->validated($request));

        return back()->with('success', 'Updated.');
    }

    public function redeem(Request $request, LoyaltyReward $loyaltyReward): RedirectResponse
    {
        $this->authorize('update', $loyaltyReward);

        if (! $loyaltyReward->isRedeemable()) {
            return back()->withErrors(['gate' => 'This reward has expired or has already been used.']);
        }

        $data = $request->validate(['redeemed_booking_id' => ['nullable', 'integer', 'exists:bookings,id']]);

        $loyaltyReward->forceFill($data + ['status' => 'redeemed', 'redeemed_at' => now()])->save();
        $loyaltyReward->logActivity('system', 'Reward redeemed');

        return back()->with('success', 'Reward redeemed.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'clients' => Client::orderBy('full_name')->limit(500)->get(['id', 'full_name'])
                ->map(fn (Client $client): array => ['value' => $client->id, 'label' => (string) $client->full_name])
                ->all(),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', LoyaltyReward::class);

        // A voucher nobody can quote is a voucher nobody redeems.
        $reward = LoyaltyReward::create($this->validated($request) + [
            'code' => strtoupper('WY-'.bin2hex(random_bytes(4))),
        ]);

        return redirect()->route('crm.rewards.show', $reward)->with('success', 'Reward issued.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'type' => ['required', Rule::in(['voucher', 'points', 'upgrade', 'membership'])],
            'value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(config('walidia.currencies'))],
            'points' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'valid_from' => ['nullable', 'date'],
            'expires_on' => ['nullable', 'date', 'after:valid_from'],
            'status' => ['required', Rule::in(['issued', 'redeemed', 'expired', 'cancelled'])],
        ]);
    }
}
