<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'member_id',
        'event_id',
        'payment_type',
        'status',
        'amount',
        'academic_term',
        'provider_reference',
    ];

    protected function casts(): array
    {
        return [
            'payment_type' => PaymentType::class,
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function idCard(): HasOne
    {
        return $this->hasOne(IdCard::class, 'payment_id');
    }

    public function scopeCompletedDues(Builder $q): Builder
    {
        return $q->where('payment_type', PaymentType::Dues)
            ->where('status', PaymentStatus::Completed);
    }
}

