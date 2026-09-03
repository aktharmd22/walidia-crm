<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\TracksBlame;
use Database\Factories\MessageTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * What to say, in both languages, with the merge fields it expects.
 */
class MessageTemplate extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<MessageTemplateFactory> */
    use HasFactory;

    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'merge_fields' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Fill the template. Unknown fields are left as they are rather than
     * blanked, so a broken merge is visible in a preview instead of silently
     * sending a sentence with a hole in it.
     *
     * @param  array<string, string|int|float|null>  $values
     */
    public function render(array $values, string $locale = 'en'): string
    {
        $body = $locale === 'ar' && $this->body_ar !== null ? $this->body_ar : $this->body_en;

        foreach ($values as $field => $value) {
            $body = str_replace('{{'.$field.'}}', (string) $value, $body);
        }

        return $body;
    }

    /**
     * @param  array<string, string|int|float|null>  $values
     */
    public function renderSubject(array $values, string $locale = 'en'): ?string
    {
        $subject = $locale === 'ar' && $this->subject_ar !== null ? $this->subject_ar : $this->subject_en;

        if ($subject === null) {
            return null;
        }

        foreach ($values as $field => $value) {
            $subject = str_replace('{{'.$field.'}}', (string) $value, $subject);
        }

        return $subject;
    }
}
