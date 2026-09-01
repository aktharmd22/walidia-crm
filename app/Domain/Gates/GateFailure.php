<?php

declare(strict_types=1);

namespace App\Domain\Gates;

/**
 * One failed condition, in the words the person in front of the screen needs:
 * what is missing, and where to go and fix it. Never a generic "not allowed".
 */
final class GateFailure
{
    public function __construct(
        public readonly string $rule,
        public readonly string $condition,
        public readonly string $message,
        public readonly string $severity = 'hard',
        public readonly ?string $resolutionLabel = null,
        public readonly ?string $resolutionUrl = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'rule' => $this->rule,
            'condition' => $this->condition,
            'message' => $this->message,
            'severity' => $this->severity,
            'resolution' => $this->resolutionUrl === null ? null : [
                'label' => $this->resolutionLabel ?? 'Open',
                'href' => $this->resolutionUrl,
            ],
        ];
    }
}
