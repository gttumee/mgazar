<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class LandOffer extends Model
{
    protected $fillable = ['land_id', 'price', 'phone', 'message', 'status'];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'status' => 'string',
        ];
    }

    public function land(): BelongsTo
    {
        return $this->belongsTo(Land::class);
    }
}
