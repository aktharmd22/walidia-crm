<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Services\SequenceService;

/**
 * Assigns the human-facing reference on create, from the locking sequence
 * service. Models declare their sequence key with sequenceKey().
 */
trait HasReference
{
    public static function bootHasReference(): void
    {
        static::creating(function ($model): void {
            if ($model->assignsReferenceOnCreate() && blank($model->reference)) {
                $model->reference = app(SequenceService::class)->next($model->sequenceKey());
            }
        });
    }

    abstract public function sequenceKey(): string;

    /**
     * Most records take their reference the moment they exist. Documents whose
     * number is a legal promise — a tax invoice — take theirs at the moment
     * they are issued, so drafts never consume a number (D-013).
     */
    public function assignsReferenceOnCreate(): bool
    {
        return true;
    }
}
