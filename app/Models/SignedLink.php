<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\SignedLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * Seven-day, single-purpose, session-free client links (brief §4).
 *
 * The token is stored hashed, so a leaked database row cannot be replayed, and
 * each link grants exactly one capability on exactly one record — never a
 * session, and never sight of anything else.
 *
 * @property int $id
 * @property string $token_hash
 * @property string $purpose
 * @property string $subject_type
 * @property int $subject_id
 * @property int|null $client_id
 * @property CarbonImmutable $expires_at
 * @property int $max_uses
 * @property int $used_count
 * @property CarbonImmutable|null $last_used_at
 * @property string|null $last_ip
 * @property CarbonImmutable|null $revoked_at
 * @property int|null $created_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Model $subject
 *
 * @method static \Database\Factories\SignedLinkFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereLastIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereMaxUses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereRevokedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereTokenHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SignedLink whereUsedCount($value)
 *
 * @mixin \Eloquent
 */
class SignedLink extends Model
{
    /** @use HasFactory<SignedLinkFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Issues a link and returns the plaintext token exactly once — it is never
     * recoverable from the database afterwards.
     *
     * @return array{link: SignedLink, token: string}
     */
    public static function issue(Model $subject, string $purpose, ?int $clientId = null, ?int $days = null): array
    {
        $token = Str::random(48);

        $link = static::create([
            'token_hash' => hash('sha256', $token),
            'purpose' => $purpose,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'client_id' => $clientId,
            'expires_at' => now()->addDays($days ?? (int) config('walidia.signed_link_days')),
            'created_by' => auth()->id(),
        ]);

        return ['link' => $link, 'token' => $token];
    }

    public static function resolve(string $token, string $purpose): ?self
    {
        return static::where('token_hash', hash('sha256', $token))
            ->where('purpose', $purpose)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->whereColumn('used_count', '<', 'max_uses')
            ->first();
    }

    public function registerUse(?string $ip): void
    {
        $this->forceFill([
            'used_count' => $this->used_count + 1,
            'last_used_at' => now(),
            'last_ip' => $ip,
        ])->save();
    }
}
