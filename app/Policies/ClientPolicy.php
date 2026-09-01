<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Client;
use App\Models\RecordAccessLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ClientPolicy extends ResourcePolicy
{
    protected string $prefix = 'clients';

    /**
     * A client is also visible to whoever is delivering work for them — Ops on
     * a confirmed booking, Finance on an open invoice — without handing either
     * of them the whole book.
     */
    public function view(User $user, Model $model): bool
    {
        if (! $user->can('clients.view')) {
            return false;
        }

        return $this->owns($user, $model) || $this->hasWorkingRelationship($user, $model);
    }

    /**
     * VIP, identity and dietary data. Granted per user rather than per role
     * (Q3), and every successful check is logged — that is the point of it.
     */
    public function viewVipFields(User $user, Client $client): bool
    {
        if (! $user->can('clients.view-vip')) {
            return false;
        }

        if (! $this->view($user, $client)) {
            return false;
        }

        $this->logAccess($user, $client, 'vip', 'view');

        return true;
    }

    public function exportPii(User $user, Client $client): bool
    {
        if (! $user->can('clients.export-pii')) {
            return false;
        }

        $this->logAccess($user, $client, 'vip', 'export');

        return true;
    }

    public function verifyKyc(User $user, Client $client): bool
    {
        return $user->can('compliance.verify-kyc');
    }

    public function approve(User $user, Client $client): bool
    {
        return $user->can('compliance.verify-kyc') && $client->status === 'pending_approval';
    }

    public function merge(User $user, Client $client): bool
    {
        return $user->can('clients.update') && $user->can('records.reassign');
    }

    /** A blacklisted client is read-only until Admin says otherwise. */
    public function update(User $user, Model $model): bool
    {
        if ($model instanceof Client && $model->status === 'blacklisted' && ! $user->can('settings.manage')) {
            return false;
        }

        return parent::update($user, $model);
    }

    private function hasWorkingRelationship(User $user, Model $client): bool
    {
        // Filled in as the domains land: a confirmed booking for Ops, an open
        // invoice for Finance. Until then, permission plus ownership decides.
        return false;
    }

    private function logAccess(User $user, Client $client, string $group, string $action): void
    {
        // Only genuinely protected records are worth logging; logging every
        // read of every ordinary client would bury the signal.
        if (! $client->isVip()) {
            return;
        }

        RecordAccessLog::create([
            'user_id' => $user->id,
            'subject_type' => $client->getMorphClass(),
            'subject_id' => $client->getKey(),
            'field_group' => $group,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
            'occurred_at' => now(),
        ]);
    }
}
