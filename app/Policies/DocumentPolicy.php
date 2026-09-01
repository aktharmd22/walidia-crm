<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Document;
use App\Models\RecordAccessLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * A document inherits the visibility of what it is attached to: if you cannot
 * see the booking, you cannot see its contract. Downloads are logged.
 */
class DocumentPolicy extends ResourcePolicy
{
    protected string $prefix = 'documents';

    protected ?string $ownerColumn = null;

    public function view(User $user, Model $model): bool
    {
        return $user->can('documents.view') && $this->canSeeSubject($user, $model);
    }

    public function download(User $user, Document $document): bool
    {
        if (! $this->view($user, $document)) {
            return false;
        }

        if ($document->is_sensitive && ! $user->can('clients.view-vip')) {
            return false;
        }

        RecordAccessLog::create([
            'user_id' => $user->id,
            'subject_type' => $document->getMorphClass(),
            'subject_id' => $document->getKey(),
            'field_group' => 'document',
            'action' => 'download',
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
            'occurred_at' => now(),
        ]);

        return true;
    }

    public function update(User $user, Model $model): bool
    {
        // A signed document is evidence; it is superseded by a new version,
        // never edited in place.
        if ($model instanceof Document && $model->signed_at !== null) {
            return false;
        }

        return $user->can('documents.update') && $this->canSeeSubject($user, $model);
    }

    private function canSeeSubject(User $user, Model $document): bool
    {
        if (! $document instanceof Document || $document->subject_type === null) {
            return true;
        }

        $subject = $document->subject;

        return $subject === null || $user->can('view', $subject);
    }
}
