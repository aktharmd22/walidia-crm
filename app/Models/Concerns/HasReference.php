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
            if (blank($model->reference)) {
                $model->reference = app(SequenceService::class)->next($model->sequenceKey());
            }
        });
    }

    abstract public function sequenceKey(): string;
}
