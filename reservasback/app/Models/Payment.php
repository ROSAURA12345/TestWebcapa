<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'reservation_id',
        'image_url',
        'note',
        'status',
        'confirmed_at',
        'rejected_at',
    ];

    public function reservation(): BelongsTo {
        return $this->belongsTo(Reservation::class);
    }
}
