<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Encrypted columns cannot be queried, but "find the client holding this
 * passport" is a real duplicate-check workflow. Each declared field gets an
 * HMAC-SHA256 sibling column, indexed, supporting exact match only (D-007).
 */
trait HasBlindIndex
{
    public static function bootHasBlindIndex(): void
    {
        static::saving(function ($model): void {
            foreach ($model->blindIndexes() as $field => $indexColumn) {
                if (! $model->isDirty($field)) {
                    continue;
                }

                $value = $model->getAttribute($field);
                $model->setAttribute($indexColumn, $value === null || $value === ''
                    ? null
                    : static::blindHash((string) $value));
            }
        });
    }

    /**
     * Field => index column.
     *
     * @return array<string, string>
     */
    abstract public function blindIndexes(): array;

    public static function blindHash(string $value): string
    {
        $normalised = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value) ?? '');

        return hash_hmac('sha256', $normalised, (string) config('app.key'));
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWhereBlind(Builder $query, string $field, string $value): Builder
    {
        $column = $this->blindIndexes()[$field] ?? null;

        if ($column === null) {
            throw new \InvalidArgumentException("[{$field}] has no blind index.");
        }

        return $query->where($column, static::blindHash($value));
    }
}
