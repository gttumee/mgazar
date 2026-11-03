<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Land extends Model
{
   protected $fillable = [
        'title', 'description', 'cadastral_code', 'phone', 'price',
        'size_ha', 'district', 'rights_type', 'has_electricity',
        'has_water', 'has_fence', 'near_road', 'is_swamp',
        'is_sloped', 'images', 'latitude', 'longitude', 'status', 'user_id',
        'deal_type', 'views_count'
    ];

    protected function casts(): array
    {
        return [
            'has_electricity' => 'boolean',
            'has_water' => 'boolean',
            'has_fence' => 'boolean',
            'near_road' => 'boolean',
            'is_swamp' => 'boolean',
            'is_sloped' => 'boolean',
            'images' => 'array',
            'price' => 'float',
            'size_ha' => 'float',
            'status' => 'string',
            'deal_type' => 'string',
            'views_count' => 'integer',
        ];
    }

    public function offers(): HasMany
    {
        return $this->hasMany(LandOffer::class);
    }
}