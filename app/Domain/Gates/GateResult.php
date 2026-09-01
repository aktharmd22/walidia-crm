<?php

declare(strict_types=1);

namespace App\Domain\Gates;

/**
 * The single object the UI, the controllers and the tests all read.
 *
 * There is no second code path where a guarded transition happens without one
 * of these — every transition Action takes the evaluator in its constructor.
 */
final class GateResult
{
    /**
     * @param  list<GateFailure>  $failures
     */
    public function __construct(
        public readonly string $verdict,   // pass · warn · block
        public readonly array $failures = [],
        public readonly bool $overridable = false,
    ) {}

    public static function pass(): self
    {
        return new self('pass');
    }

    /**
     * @param  list<GateFailure>  $failures
     */
    public static function warn(array $failures): self
    {
        return new self('warn', $failures);
    }

    /**
     * @param  list<GateFailure>  $failures
     */
    public static function block(array $failures, bool $overridable): self
    {
        return new self('block', $failures, $overridable);
    }

    public function passed(): bool
    {
        return $this->verdict !== 'block';
    }

    public function blocked(): bool
    {
        return $this->verdict === 'block';
    }

    public function hasWarnings(): bool
    {
        return $this->verdict === 'warn';
    }

    /** The first failure message, for a flash or an exception. */
    public function summary(): string
    {
        return $this->failures[0]->message ?? 'This action is blocked.';
    }

    /**
     * @return array{verdict: string, overridable: bool, failures: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'verdict' => $this->verdict,
            'overridable' => $this->overridable,
            'failures' => array_map(fn (GateFailure $failure): array => $failure->toArray(), $this->failures),
        ];
    }
}
