<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'category_id',
        'include_subcategories',
    ];

    protected $casts = [
        'include_subcategories' => 'boolean',
    ];

    /**
     * Relationship: Belongs to Event
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Relationship: Belongs to Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
