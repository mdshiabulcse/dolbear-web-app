<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'map',
        'description',
        'image',
        'image_id',

    ];

    protected $casts = [
        'image' => 'array',
    ];

    protected $appends = [
        'image_id',
        'image',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($store) {
            if ($store->productStocks()->exists()) {
                throw new \Exception('Store cannot be deleted because it has associated stock.');
            }
        });
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

    public function productStocks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductStock::class, 'store_id', 'id');
    }
}
