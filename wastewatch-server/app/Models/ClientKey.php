<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id', 'public_key', 'trashcan_id'
    ];


    public function trashcan(): BelongsTo
    {
        return $this->belongsTo(Trashcan::class);
    }
}
