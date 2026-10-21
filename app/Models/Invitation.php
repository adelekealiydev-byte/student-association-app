<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    use HasFactory;

    protected $primaryKey = 'invitation_id';

    protected $fillable = [
        'member_id',
        'invitation_token',
        'status',
        'expires_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvitationStatus::class,
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereIn('status', [InvitationStatus::Pending, InvitationStatus::Sent]);
    }
}

