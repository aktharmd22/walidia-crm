<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Issues human-facing identifiers under a row lock (D-013).
 *
 * The FTA requires gapless sequential tax invoice numbering, and `max(id) + 1`
 * does not survive two people clicking Issue at the same moment. Every number
 * is allocated inside the caller's transaction against a locked sequence row.
 */
class SequenceService
{
    /**
     * @param  string  $key  A key from config('walidia.sequences'), e.g. 'invoice'.
     */
    public function next(string $key, ?\DateTimeInterface $on = null): string
    {
        $config = config("walidia.sequences.{$key}");

        if (! is_array($config)) {
            throw new \InvalidArgumentException("No sequence configured for [{$key}].");
        }

        $prefix = (string) $config['prefix'];
        $period = (string) ($config['period'] ?? 'none');
        $padding = (int) ($config['padding'] ?? 4);
        $periodKey = $this->periodKey($period, $on);

        $value = DB::transaction(function () use ($key, $prefix, $period, $padding, $periodKey): int {
            $row = DB::table('sequences')
                ->where('key', $key)
                ->where('period_key', $periodKey)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                DB::table('sequences')->insert([
                    'key' => $key,
                    'prefix' => $prefix,
                    'period' => $period,
                    'period_key' => $periodKey,
                    'current_value' => 1,
                    'padding' => $padding,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return 1;
            }

            $next = (int) $row->current_value + 1;

            DB::table('sequences')
                ->where('id', $row->id)
                ->update(['current_value' => $next, 'updated_at' => now()]);

            return $next;
        });

        return $this->format($prefix, $periodKey, $value, $padding);
    }

    /** Peek at the next number without consuming it — for previews only. */
    public function peek(string $key, ?\DateTimeInterface $on = null): string
    {
        $config = config("walidia.sequences.{$key}", []);
        $prefix = (string) ($config['prefix'] ?? 'XX');
        $padding = (int) ($config['padding'] ?? 4);
        $periodKey = $this->periodKey((string) ($config['period'] ?? 'none'), $on);

        $current = (int) (DB::table('sequences')
            ->where('key', $key)
            ->where('period_key', $periodKey)
            ->value('current_value') ?? 0);

        return $this->format($prefix, $periodKey, $current + 1, $padding);
    }

    private function periodKey(string $period, ?\DateTimeInterface $on): string
    {
        $date = $on ? CarbonImmutable::instance($on) : now()->toImmutable();
        $date = $date->setTimezone(config('walidia.display_timezone'));

        return match ($period) {
            'yearly' => $date->format('Y'),
            'monthly' => $date->format('Y-m'),
            default => '',
        };
    }

    private function format(string $prefix, string $periodKey, int $value, int $padding): string
    {
        $number = str_pad((string) $value, $padding, '0', STR_PAD_LEFT);

        return $periodKey === ''
            ? "{$prefix}-{$number}"
            : "{$prefix}-{$periodKey}-{$number}";
    }
}
