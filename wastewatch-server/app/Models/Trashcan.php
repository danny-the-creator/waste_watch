<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trashcan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tag',
        'description',
        'location',
        'fill_level',
        'lid_blocked',
        'service_lid_blocked',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    
    public function client_key(): HasOne
    {
        return $this->hasOne(ClientKey::class);
    }
}
