<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model {
    protected $fillable = [
        'entrepreneur_id', 'place_id', 'name', 'description', 'price',
        'stock', 'duration', 'main_image', 'is_active'
    ];

    public function entrepreneur()
{
    return $this->belongsTo(User::class, 'entrepreneur_id');
}


    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }


    public function images(): HasMany {
        return $this->hasMany(ProductImage::class);
    }

       public function categories(): BelongsToMany {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function reservations(): HasMany {
        return $this->hasMany(Reservation::class);
    }


}
