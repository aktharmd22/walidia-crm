<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GateOverrideFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The Override Register. Append-only: no route in this application
 * updates or deletes a row here.
 */
class GateOverride extends Model
{
    /** @use HasFactory<GateOverrideFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['failed_conditions' => 'array', 'created_at' => 'datetime'];
    }

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
