<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignProduct extends Model
{
    use HasFactory;

    public function getDiscountAmountAttribute()
    {
        $amount = $this->discount;
        if ($this->discount_type == 'percentage') {
            $amount = ($this->discount * $this->product->price) / 100;
        }
        return get_price($amount);
    }
    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function campaign(){
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
