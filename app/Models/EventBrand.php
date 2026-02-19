<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'brand_id',
    ];

    /**
     * Relationship: Belongs to Event
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Relationship: Belongs to Brand
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
