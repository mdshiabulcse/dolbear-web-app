<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = [
        'image_190x230',
        'image_40x40',
        'image_72x72',
        'image_110x122',
        'special_discount_check',
        'discount_percentage',
        'product_name'
        ];

    protected $casts = [
        'attribute_sets'        => 'array',
        'thumbnail'             => 'array',
        'images'                => 'array',
        'meta_image'            => 'array',
        'colors'                => 'array',
        'selected_variants'     => 'array',
        'selected_variants_ids' => 'array',
        'contact_info'          => 'array',
        'product_file'          => 'array',
        'description_images'    => 'array',
    ];

    protected $attributes = [
        'attribute_sets'    => '[]',
        'thumbnail'         => '[]',
        'images'            => '[]',
        'meta_image'        => '[]',
        'colors'            => '[]',
        'selected_variants' => '[]',
        'selected_variants_ids' => '[]',
        'contact_info'      => '[]',
    ];

    protected $fillable = ['code', 'name', 'user_id', 'brand_id', 'category_id', 'created_by', 'slug', 'price', 'purchase_cost',
        'barcode', 'video_provider', 'video_url', 'current_stock', 'minimum_order_quantity', 'is_approved', 'is_catalog',
        'external_link', 'is_refundable', 'cash_on_delivery', 'attribute_sets','images', 'meta_image', 'colors',
        'selected_variants', 'selected_variants_ids', 'contact_info','status','question','warrenty', 'free_shipping'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = 1;
        });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function brand(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Brand::class)->with('currentLanguage');
    }

    public function productLanguages()
    {
        return $this->hasMany(ProductLanguage::class);
    }

    public function cart()
    {
        return $this->hasMany(Cart::class)->where('user_id',authId());
    }


    public function getTitleAttribute()
    {
        return @$this->translate->name;
    }


    public function stock()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function firstStock(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductStock::class);
    }

    public function getTranslation($field, $lang = 'en')
    {
        $product_translation  = $this->hasMany(ProductLanguage::class)->where('lang', $lang)->first();

        if (blank($product_translation)):
            $product_translation = $this->hasMany(ProductLanguage::class)->where('lang', 'en')->first();
        endif;

        return $product_translation->$field;
    }

    public function getTranslateAttribute()
    {
        $product_translation  = $this->hasMany(ProductLanguage::class)->where('lang', languageCheck())->first();

        if (blank($product_translation)):
            $product_translation = $this->hasMany(ProductLanguage::class)->where('lang', 'en')->first();
        endif;

        return $product_translation;
    }

    public function campaign()
    {
        return $this->hasOne(CampaignProduct::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sellerProfile()
    {
        return $this->hasOne(SellerProfile::class,'user_id','user_id');
    }

    public function vatTaxes($product)
    {
        return VatTax::find(explode(',',$product->vat_taxes));
    }

    public function orders()
    {
        return $this->hasMany(OrderDetail::class,'product_id');
    }

    public function monthlyOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderDetail::class,'product_id')
            ->whereBetween('created_at',[Carbon::now()->subDays(30)->format('Y-m-d').' 00:00:00',Carbon::now()->format('Y-m-d').' 23:59:59']);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function viewedProducts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductView::class);
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class)->where('status', 1);
    }

    public function userCompare(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CompareProduct::class)->where('user_id',authId());
    }

    public function userWishlist()
    {
        return $this->hasOne(Wishlist::class)->where('user_id',authId());
    }

    public function wishlists(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function userReview(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class)->where('user_id',authId());
    }

    public function orderDetails(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function campaingProduct(){
        return $this->hasOne(CampaignProduct::class);
    }

    /**
     * Relationship: Product belongs to many events
     * Same product can be in multiple events
     */
    public function eventProducts()
    {
        return $this->hasMany(EventProduct::class);
    }

    /**
     * Relationship: Get active events for this product
     */
    public function activeEvents()
    {
        return $this->hasManyThrough(
            Event::class,
            EventProduct::class,
            'product_id',
            'id',
            'id',
            'event_id'
        )->where('event_products.is_active', 1)
            ->where('event_products.status', 'active')
            ->where('events.status', 'active')
            ->where('events.is_active', 1)
            ->orderBy('events.event_priority', 'asc');
    }

    /**
     * Relationship: Get currently active event with highest priority
     */
    public function currentActiveEvent()
    {
        return $this->hasOneThrough(
            Event::class,
            EventProduct::class,
            'product_id',
            'id',
            'id',
            'event_id'
        )->where('event_products.is_active', 1)
            ->where('event_products.status', 'active')
            ->where('events.status', 'active')
            ->where('events.is_active', 1)
            ->where('events.event_schedule_start', '<=', now())
            ->where('events.event_schedule_end', '>=', now())
            ->orderBy('events.event_priority', 'asc');
    }

    public function getProductNameAttribute()
    {
        return @$this->getTranslation('name',languageCheck());
    }

    public function getProductMetaTitleAttribute()
    {
        return @$this->translate->meta_title;
    }

    public function getImage190x230Attribute()
    {
        return @is_file_exists($this->thumbnail['image_190x230'] , $this->thumbnail['storage']) ? @get_media($this->thumbnail['image_190x230'],$this->thumbnail['storage']) : static_asset('images/default/190x230_no_bg.png');
    }

    public function getImage40x40Attribute()
    {
        return getFileLink('40x40',$this->thumbnail);
    }

    public function getImage110x122Attribute()
    {
        return @is_file_exists($this->thumbnail['image_110x122'] , $this->thumbnail['storage']) ? @get_media($this->thumbnail['image_110x122'],$this->thumbnail['storage']) : static_asset('images/default/default-image-72x72.png');
    }

    public function getImage72x72Attribute()
    {
        return @is_file_exists($this->thumbnail['image_72x72'] , $this->thumbnail['storage']) ? @get_media($this->thumbnail['image_72x72'],$this->thumbnail['storage']) : static_asset('images/default/default-image-72x72.png');
    }

    public function getSpecialDiscountCheckAttribute()
    {
        $price = 0;
        $special_discount = $this->special_discount;
        $now = Carbon::now()->format('Y-m-d H:i:s');

        if ($special_discount > 0 && $this->special_discount_start <= $now && $this->special_discount_end >= $now)
        {
            $price = $special_discount;
        }
        return $price;
    }

    public function getRatingAttribute($value): float
    {
        return round($value,2);
    }

    /**
     * Attribute: Get campaign price for this product
     * Returns the lowest price among: campaign price, special discount, or regular price
     */
    public function getCampaignPriceAttribute()
    {
        try {
            if (class_exists(\App\Services\CampaignPricingService::class)) {
                $pricingService = app(\App\Services\CampaignPricingService::class);
                $pricing = $pricingService->getFinalPrice($this);
                if ($pricing['discount_source'] === 'campaign') {
                    return [
                        'price' => $pricing['price'],
                        'original_price' => $pricing['original_price'],
                        'discount_amount' => $pricing['discount_info']['discount_amount'] ?? 0,
                        'discount_type' => $pricing['discount_info']['discount_type'] ?? 'flat',
                        'formatted_discount' => $pricing['discount_info']['formatted_discount'] ?? '',
                        'badge_text' => $pricing['discount_info']['badge_text'] ?? '',
                        'badge_color' => $pricing['discount_info']['badge_color'] ?? '#ff0000',
                        'is_campaign' => true,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Ignore errors
        }

        // No campaign pricing
        return null;
    }

    /**
     * Attribute: Get the lowest price for this product
     * Considers: campaign > special discount > regular price
     */
    public function getLowestPriceAttribute()
    {
        $campaignPrice = $this->campaign_price;
        $specialPrice = null;

        // Check special discount
        $now = \Carbon\Carbon::now();
        if ($this->special_discount > 0
            && $this->special_discount_start
            && $this->special_discount_end
            && $now->between($this->special_discount_start, $this->special_discount_end)) {
            if ($this->special_discount_type === 'percentage') {
                $specialPrice = $this->price - ($this->price * $this->special_discount / 100);
            } else {
                $specialPrice = $this->price - $this->special_discount;
            }
            $specialPrice = max(0, $specialPrice);
        }

        // Return campaign price if available and lower
        if ($campaignPrice && $campaignPrice['price'] < $this->price) {
            return [
                'price' => $campaignPrice['price'],
                'original_price' => $campaignPrice['original_price'],
                'discount_source' => 'campaign',
                'info' => $campaignPrice,
            ];
        }

        // Return special discount price if available and lower
        if ($specialPrice !== null && $specialPrice < $this->price) {
            return [
                'price' => $specialPrice,
                'original_price' => $this->price,
                'discount_source' => 'special',
                'info' => [
                    'discount_amount' => $this->special_discount,
                    'discount_type' => $this->special_discount_type,
                    'formatted_discount' => $this->special_discount_type === 'percentage'
                        ? number_format($this->special_discount, 0) . '%'
                        : get_price($this->special_discount),
                ],
            ];
        }

        // Return regular price
        return [
            'price' => $this->price,
            'original_price' => $this->price,
            'discount_source' => 'none',
            'info' => null,
        ];
    }

    public function getDiscountPercentageAttribute()
    {
        $amount = 0;
        $special_discount = $this->special_discount;
        $now = Carbon::now()->format('Y-m-d H:i:s');

        if ($special_discount > 0 && $this->special_discount_start <= $now && $this->special_discount_end >= $now)
        {
            $type = $this->special_discount_type;

            if ($type == 'flat')
            {
                $amount = $this->price - $special_discount;
            }
            else{
                $amount = $this->price - ($this->price*($special_discount/100));
            }
        }
        return $amount;
    }

    public function scopeUserCheck($query)
    {
        return $query->whereHas('user',function ($qu){
            $qu->where('status',1)->where('is_user_banned',0)->where(function ($q){
                $q->where('user_type','admin')->orWhere('user_type','staff');
            });
        })->when(settingHelper('seller_system') == 1, function ($q){
            $q->orWhereHas('user',function ($qu){
                $qu->where('status',1)->where('is_user_banned',0)->where('user_type','seller')->whereHas('sellerProfile',function ($q){
                    $q->where('verified_at','!=',null);
                });
            });
        });
    }

    public function scopeIsWholesale($query)
    {
        return $query->when(!addon_is_activated('wholesale'), function ($q){
            $q->where('is_wholesale',0);
        });
    }
    public function scopeCheckSellerSystem($query)
    {
        return $query->when(settingHelper('seller_system') != 1, function ($q) {
            $q->where('user_id',1);
        });
    }

    public function scopeProfileCheck($query)
    {
        return $query->where(function ($query){
            $query->where('status',1)->where('is_user_banned',0);
        });
    }
    public function scopeProductPublished($query)
    {
        return $query->where('status', 'published')->where('is_approved', 1);
    }
    public function scopeIsStockOut($query)
    {
        return $query->when(settingHelper('stock_out_product_hide'), function ($q){
            $q->whereRaw('current_stock > 0');
        });
    }

    public static function boot() {
        parent::boot();
        //while creating/inserting item into db
        static::creating(function (Product $item) {
            $item->cash_on_delivery = 1; //assigning value
        });
    }
}
