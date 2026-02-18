<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventProduct extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'event_id',
        'product_id',
        'event_price',
        'discount_amount',
        'discount_type',
        'product_priority',
        'event_stock',
        'event_stock_sold',
        'is_active',
        'status',
        'badge_text',
        'badge_color',
        'created_by',
        'updated_by',
        // Additional columns for campaign system
        'is_inherited',
        'parent_event_id',
        'final_price',
    ];

    protected $casts = [
        'event_price' => 'float',
        'discount_amount' => 'float',
        'product_priority' => 'integer',
        'event_stock' => 'integer',
        'event_stock_sold' => 'integer',
        'is_active' => 'boolean',
        'is_inherited' => 'boolean',
    ];

    /**
     * Relationship: Belongs to Event
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Relationship: Belongs to Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relationship: Created by user
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Updated by user
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope: Get active event products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1)->where('status', 'active');
    }

    /**
     * Scope: Get event products with available stock
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('event_stock')
                ->orWhereRaw('event_stock > event_stock_sold');
        });
    }

    /**
     * Attribute: Get final price for product in this event
     */
    public function getFinalPriceAttribute()
    {
        $product = $this->product;

        if (!$product) {
            return 0;
        }

        // Use event price if set
        if ($this->event_price !== null) {
            return $this->event_price;
        }

        // Calculate discount from product price
        if ($this->discount_amount > 0) {
            if ($this->discount_type == 'percentage') {
                return $product->price - ($product->price * ($this->discount_amount / 100));
            } else {
                return $product->price - $this->discount_amount;
            }
        }

        return $product->price;
    }

    /**
     * Attribute: Get discount amount for display
     */
    public function getDiscountAmountAttribute($value)
    {
        if ($this->discount_type == 'percentage' && $value > 0) {
            $product = $this->product;
            if ($product) {
                return ($product->price * $value) / 100;
            }
        }
        return $value;
    }

    /**
     * Attribute: Get formatted discount for display
     */
    public function getFormattedDiscountAttribute()
    {
        if ($this->event_price !== null && $this->event_price > 0) {
            $product = $this->product;
            if ($product) {
                $discount = $product->price - $this->event_price;
                $percentage = ($discount / $product->price) * 100;
                return number_format($percentage, 0) . '%';
            }
        }

        if ($this->discount_amount > 0) {
            if ($this->discount_type == 'percentage') {
                return number_format($this->discount_amount, 0) . '%';
            } else {
                $product = $this->product;
                if ($product) {
                    $percentage = ($this->discount_amount / $product->price) * 100;
                    return number_format($percentage, 0) . '%';
                }
            }
        }

        return '';
    }

    /**
     * Attribute: Check if stock is available
     */
    public function getIsStockAvailableAttribute()
    {
        // If event_stock is null, use product stock
        if ($this->event_stock === null) {
            $product = $this->product;
            return $product ? $product->current_stock > 0 : false;
        }

        return $this->event_stock > $this->event_stock_sold;
    }

    /**
     * Attribute: Get remaining stock
     */
    public function getRemainingStockAttribute()
    {
        if ($this->event_stock === null) {
            $product = $this->product;
            return $product ? $product->current_stock : 0;
        }

        return $this->event_stock - $this->event_stock_sold;
    }

    /**
     * Attribute: Check if product is sold out
     */
    public function getIsSoldOutAttribute()
    {
        return !$this->is_stock_available;
    }

    /**
     * Method: Increment sold stock
     */
    public function incrementSoldStock($quantity = 1)
    {
        if ($this->event_stock !== null) {
            $this->increment('event_stock_sold', $quantity);
        }
    }

    /**
     * Method: Check if can purchase
     */
    public function canPurchase($quantity = 1)
    {
        if ($this->event_stock === null) {
            $product = $this->product;
            return $product && $product->current_stock >= $quantity;
        }

        return ($this->event_stock - $this->event_stock_sold) >= $quantity;
    }
}