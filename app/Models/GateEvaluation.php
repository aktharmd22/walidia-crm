<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GateEvaluationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only record of every gate evaluation, pass or fail. This is
 * what makes "why was this charter allowed to sail" answerable.
 *
 * @property string $result
 * @property list<array<string, mixed>>|null $failed_conditions
 */
class GateEvaluation extends Model
{
    /** @use HasFactory<GateEvaluationFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = false;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'failed_conditions' => 'array',
        'context' => 'array',
        'evaluated_at' => 'datetime',
    ];

    /** @return BelongsTo<GateRule, $this> */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(GateRule::class, 'gate_rule_id');
    }
}
