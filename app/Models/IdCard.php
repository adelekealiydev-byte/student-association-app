<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdCard extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_card_id';

    /**
     * is_eligible is not fillable: the database trigger derives it.
     */
    protected $fillable = [
        'member_id',
        'payment_id',
        'card_number',
        'qr_code_data',
    ];

    protected function casts(): array
    {
        return [
            'is_eligible' => 'boolean',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}

