<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar_path',
        'job_title',
        'locale',
        'timezone',
        'chrome',
        'accent',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Never audit credentials or 2FA material.
     *
     * @var list<string>
     */
    protected array $auditExclude = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /* ── relations ──────────────────────────────────────────────────────── */

    /** @return BelongsToMany<Team, $this> */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withPivot('role_in_team')->withTimestamps();
    }

    /** @return HasMany<Team, $this> */
    public function ledTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'lead_user_id');
    }

    /** @return HasMany<SavedView, $this> */
    public function savedViews(): HasMany
    {
        return $this->hasMany(SavedView::class);
    }

    /* ── visibility ─────────────────────────────────────────────────────── */

    /**
     * Users whose records this user may see through the team, used by the
     * ScopedToOwner global scope (D-017). Cached per request.
     *
     * @return list<int>
     */
    public function teamMemberIds(): array
    {
        /** @var Collection<int, int> $ids */
        $ids = once(fn (): Collection => $this->ledTeams()
            ->with('members:id')
            ->get()
            ->flatMap(fn (Team $team): Collection => $team->members->pluck('id'))
            ->unique()
            ->values());

        return $ids->all();
    }

    public function hasTwoFactorConfirmed(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null;
    }
}
