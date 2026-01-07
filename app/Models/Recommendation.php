<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image', 
        'image_id',
        'description',
        'status',
    ];

    protected $casts = [
        'image' => 'array',
    ];

    protected $appends = [
        'name',
        'image_id',
        'image',
        'description',
        'status',
    ];

    public function getNameAttribute()
    {
        return $this->attributes['name'] ?? '';
    }

    public function getDescriptionAttribute()
    {
        return $this->attributes['description'] ?? '';
    }

    public function getStatusAttribute()
    {
        return $this->attributes['status'] ?? '';
    }

    public function getImageAttribute()
    {
        $imageData = json_decode($this->attributes['image'], true) ?? [];

        if (isset($imageData['image_400x492']) && @is_file_exists($imageData['image_400x492'] , $imageData['storage'])) {
            return @get_media($imageData['image_400x492'],$imageData['storage']);
        } 

        return asset('images/default/default-image-835x200.png');
    }
    
    public function getImageIdAttribute()
    {
        
        return $this->attributes['image_id'] ?? null;
    }

    
}

