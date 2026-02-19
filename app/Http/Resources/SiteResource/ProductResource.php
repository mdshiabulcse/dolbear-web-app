<?php

namespace App\Http\Resources\SiteResource;

use App\Http\Resources\Api\Seller\ProductStockResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        // Get campaign price directly from model accessor
        $campaignPriceData = $this->campaign_price;

        // Get lowest price (includes fallback to special discount)
        $lowestPrice = $this->lowest_price;
        $hasCampaign = $lowestPrice && $lowestPrice['discount_source'] === 'campaign';

        return [
            'id'                                    => $this->id,
            'slug'                                  => $this->slug,
            'category_id'                           => $this->category_id,
            'product_name'                          => @$this->getTranslation('name',languageCheck()),
            'special_discount_type'                 => nullCheck($this->special_discount_type),
            'special_discount_check'                => $this->special_discount_check,
            'discount_percentage'                   => $this->discount_percentage,
            'image_190x230'                         => $this->image_190x230,
            'image_40x40'                           => $this->image_40x40,
            'is_approved'                           => $this->is_approved,
            'price'                                 => (double)$this->price,
            'status'                                => $this->is_approved == 0 ? 'pending' : $this->status,
            'rating'                                => (double)$this->reviews_avg_rating,
            'reviews_count'                         => (int)$this->reviews_count,
            'current_stock'                         => (int)$this->current_stock,
            'reward'                                => (double)$this->reward,
            'minimum_order_quantity'                => (int)$this->minimum_order_quantity,
            'todays_deal'                           => (int)$this->todays_deal,
            'has_variant'                           => (bool)$this->has_variant,
            'user_wishlist'                         => (bool)$this->userWishlist,
            'is_catalog'                            => (bool)$this->is_catalog,
            'is_featured'                           => (bool)$this->is_featured,
            'is_new_arrived'                        => (bool)$this->is_new_arrived,
            'is_best_seller'                        => (bool)$this->is_best_seller,
            'is_bundle_deals'                       => (bool)$this->is_bundle_deals,
            'is_classified'                         => (bool)$this->is_classified,
            'stock'                                 => ProductStockResource::collection($this->stock),
            // Campaign pricing fields
            // campaign_price: The event price (only if campaign is active)
            'campaign_price'                        => $hasCampaign && $campaignPriceData ? $campaignPriceData['price'] : null,
            // original_price: The original product price (for strikethrough display)
            'original_price'                        => ($lowestPrice && $lowestPrice['price'] < $this->price) ? $this->price : null,
            // has_campaign_discount: Whether product has active campaign discount
            'has_campaign_discount'                 => $hasCampaign,
            // discount_info: Contains badge text, color, discount amount, etc.
            'discount_info'                         => $campaignPriceData ?? null,
        ];
    }
}
