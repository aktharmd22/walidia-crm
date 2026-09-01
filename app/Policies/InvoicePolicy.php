<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * An issued tax invoice is evidence, not a document.
 *
 * It is never edited and never deleted at any permission level: it is voided
 * and credited, so the sequence stays gapless and the trail stays honest.
 */
class InvoicePolicy extends ResourcePolicy
{
    protected string $prefix = 'invoices';

    protected ?string $ownerColumn = null;

    public function update(User $user, Model $model): bool
    {
        return $model instanceof Invoice
            && $model->isEditable()
            && $user->can('invoices.update');
    }

    public function delete(User $user, Model $model): bool
    {
        // Deliberately absolute. Drafts are archived; issued invoices are not.
        return $model instanceof Invoice
            && ! $model->isIssued()
            && $user->can('invoices.delete');
    }

    public function issue(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.issue') && ! $invoice->isIssued();
    }

    public function void(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.void')
            && $invoice->isIssued()
            && ! in_array($invoice->status, ['void', 'credited'], true);
    }

    public function creditNote(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.credit-note') && $invoice->isIssued();
    }
}
