<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Member extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'member_id';

    /**
     * is_verified is deliberately NOT fillable: only InvitationService may set it.
     */
    protected $fillable = [
        'email',
        'student_number',
        'username',
        'password_hash',
        'membership_status',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'membership_status' => MembershipStatus::class,
            'is_verified' => 'boolean',
            'password_hash' => 'hashed',
        ];
    }

    /**
     * Authentication uses password_hash instead of the default password column.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash ?? '';
    }

    public function getRememberTokenName(): string
    {
        return ''; // no remember_token column
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'member_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'member_id');
    }

    public function idCards(): HasMany
    {
        return $this->hasMany(IdCard::class, 'member_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'member_roles', 'member_id', 'role_id', 'member_id', 'role_id')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function hasRole(string ...$names): bool
    {
        return $this->roles->pluck('role_name')->intersect($names)->isNotEmpty();
    }

    public function dashboardWidgets(string $view = 'dashboard'): array
    {
        return ViewConfiguration::query()
            ->whereIn('role_id', $this->roles()->pluck('roles.role_id'))
            ->where('view_name', $view)
            ->pluck('config_json')
            ->flatMap(fn (?array $config) => $config['widgets'] ?? [])
            ->unique()
            ->values()
            ->all();
    }
}

