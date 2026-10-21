<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $primaryKey = 'role_id';

    protected $fillable = [
        'role_name',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'member_roles', 'role_id', 'member_id', 'role_id', 'member_id')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function viewConfigurations(): HasMany
    {
        return $this->hasMany(ViewConfiguration::class, 'role_id');
    }
}

