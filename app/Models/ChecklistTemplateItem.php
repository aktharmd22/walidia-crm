<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ChecklistTemplateItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One step in a template.
 *
 * `is_blocking` is the load-bearing column: the gate engine reads checklist
 * items by key, so a step marked blocking is one a charter cannot proceed past.
 */
class ChecklistTemplateItem extends Model
{
    /** @use HasFactory<ChecklistTemplateItemFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'requires_photo' => 'boolean',
        'requires_signature' => 'boolean',
        'is_blocking' => 'boolean',
    ];

    /** @return BelongsTo<ChecklistTemplate, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id');
    }
}
