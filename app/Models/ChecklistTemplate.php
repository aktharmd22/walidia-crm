<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ChecklistTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * The standing shape of an operations checklist.
 *
 * A template is the company's method written down once: the steps a charter
 * goes through, in order, with the blocking ones marked. Bookings instantiate
 * a copy, so editing the template never rewrites history on a charter that has
 * already sailed.
 */
class ChecklistTemplate extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<ChecklistTemplateFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** @return HasMany<ChecklistTemplateItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ChecklistTemplateItem::class)->orderBy('sort_order');
    }

    /**
     * Stamp this template onto a booking.
     *
     * The items are copied, not referenced: a charter's checklist is a record
     * of what was actually asked of the crew that day.
     */
    public function applyTo(Booking $booking): OperationsChecklist
    {
        $checklist = OperationsChecklist::create([
            'booking_id' => $booking->getKey(),
            'checklist_template_id' => $this->getKey(),
            'status' => 'open',
            'started_at' => now(),
        ]);

        foreach ($this->items as $item) {
            $checklist->items()->create([
                'checklist_template_item_id' => $item->getKey(),
                'key' => $item->key,
                'title' => $item->title_en,
                'section' => $item->section,
                'due_at' => $item->offset_hours === 0
                    ? null
                    : $booking->starts_at->addHours($item->offset_hours),
                'status' => 'pending',
                'is_blocking' => $item->is_blocking,
                'sort_order' => $item->sort_order,
            ]);
        }

        return $checklist;
    }
}
