<?php

declare(strict_types=1);

namespace App\Domain\Gates;

/**
 * Every condition a gate rule may refer to.
 *
 * Registered in one place so the rule editor can offer the real list, and so a
 * rule referring to a check that does not exist fails loudly rather than
 * passing silently.
 */
class GateCheckRegistry
{
    /** @var array<string, GateCheck> */
    private array $checks = [];

    /**
     * @param  iterable<GateCheck>  $checks
     */
    public function __construct(iterable $checks = [])
    {
        foreach ($checks as $check) {
            $this->register($check);
        }
    }

    public function register(GateCheck $check): void
    {
        $this->checks[$check->key()] = $check;
    }

    public function find(string $key): ?GateCheck
    {
        return $this->checks[$key] ?? null;
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        $keys = array_keys($this->checks);
        sort($keys);

        return $keys;
    }
}
