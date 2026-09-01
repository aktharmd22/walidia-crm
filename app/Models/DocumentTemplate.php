<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\TracksBlame;
use Carbon\CarbonImmutable;
use Database\Factories\DocumentTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string $type
 * @property string|null $business_line
 * @property string|null $body_html
 * @property array<array-key, mixed>|null $variables
 * @property int $version
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read User|null $creator
 * @property-read User|null $updater
 *
 * @method static \Database\Factories\DocumentTemplateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereBodyHtml($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereBusinessLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentTemplate withoutTrashed()
 *
 * @mixin \Eloquent
 */
class DocumentTemplate extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<DocumentTemplateFactory> */
    use HasFactory;

    use SoftDeletes;
    use TracksBlame;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];
}
