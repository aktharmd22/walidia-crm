<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Document;
use App\Models\Note;
use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * The polymorphic record furniture every business object carries (D-006):
 * a timeline, notes, tasks, documents and attachments.
 */
trait HasTimeline
{
    /** @return MorphMany<Activity, $this> */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject')->latest('occurred_at');
    }

    /** @return MorphMany<Note, $this> */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'subject')->latest();
    }

    /** @return MorphMany<Task, $this> */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'subject');
    }

    /** @return MorphMany<Document, $this> */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'subject');
    }

    /** @return MorphMany<Attachment, $this> */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'subject');
    }

    /**
     * Records one timeline entry. Status changes, gate evaluations, sent
     * proposals and logged calls all land here so the client's history reads
     * as one story rather than five tables.
     *
     * @param  array<string, mixed>  $meta
     */
    public function logActivity(
        string $type,
        string $summary,
        ?string $body = null,
        array $meta = [],
        ?string $direction = null,
    ): Activity {
        return $this->activities()->create([
            'type' => $type,
            'direction' => $direction,
            'summary' => $summary,
            'body' => $body,
            'meta' => $meta === [] ? null : $meta,
            'client_id' => $this->timelineClientId(),
            'user_id' => auth()->id(),
            'occurred_at' => now(),
        ]);
    }

    /** Overridden by models that hang off a client, so the 360° view unions cheaply. */
    public function timelineClientId(): ?int
    {
        return property_exists($this, 'client_id') || array_key_exists('client_id', $this->getAttributes())
            ? $this->getAttribute('client_id')
            : null;
    }

    /**
     * Open tasks on this record, oldest due first — the "Next Action" list.
     *
     * @return Collection<int, Task>
     */
    public function openTasks(): Collection
    {
        return $this->tasks()->where('status', 'open')->orderBy('due_at')->get();
    }
}
