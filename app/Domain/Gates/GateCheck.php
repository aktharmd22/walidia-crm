<?php

declare(strict_types=1);

namespace App\Domain\Gates;

use Illuminate\Database\Eloquent\Model;

/**
 * A single, individually tested condition.
 *
 * Adding a new *kind* of check is a code change; adding, retargeting,
 * reordering or disabling a *rule* is a data change (D-004).
 */
interface GateCheck
{
    /** The key a gate_rules row refers to, e.g. 'payment.deposit_cleared'. */
    public function key(): string;

    /**
     * @param  array<string, mixed>  $params
     */
    public function passes(Model $subject, array $params): bool;

    /**
     * What is missing, in the words of the person who has to fix it.
     *
     * @param  array<string, mixed>  $params
     */
    public function failureMessage(Model $subject, array $params): string;

    /**
     * Where to go and fix it: ['label' => 'Open payment schedule', 'url' => '/…'].
     *
     * @param  array<string, mixed>  $params
     * @return array{label: string, url: string}|null
     */
    public function resolution(Model $subject, array $params): ?array;
}
