<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViewConfiguration extends Model
{
    use HasFactory;

    protected $primaryKey = 'view_config_id';

    protected $fillable = [
        'role_id',
        'view_name',
        'config_json',
    ];

    protected function casts(): array
    {
        return [
            'config_json' => 'array',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}

