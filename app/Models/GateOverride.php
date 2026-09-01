<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\GateOverrideFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The Override Register. Append-only: no route in this application
 * updates or deletes a row here.
 *
 * @property CarbonImmutable|null $created_at
 * @property string $subject_type
 * @property string $reason
 * @property list<array<string, mixed>>|null $failed_conditions
 * @property-read GateRule|null $rule
 * @property-read User|null $user
 */
class GateOverride extends Model
{
    /** @use HasFactory<GateOverrideFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = false;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'failed_conditions' => 'array',
        'created_at' => 'datetime',
    ];

    /** @return BelongsTo<GateRule, $this> */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(GateRule::class, 'gate_rule_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<GateEvaluation, $this> */
    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(GateEvaluation::class, 'gate_evaluation_id');
    }
}
