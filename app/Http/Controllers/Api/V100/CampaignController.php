<?php

namespace App\Http\Controllers\Api\V100;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CampaignPaginateResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ShopResource;
use App\Models\Event;
use App\Repositories\Interfaces\Admin\Marketing\CampaignInterface;
use App\Repositories\Interfaces\Admin\Product\BrandInterface;
use App\Repositories\Interfaces\Admin\Product\ProductInterface;
use App\Repositories\Interfaces\Admin\SellerInterface;
use App\Services\CampaignPricingService;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    use ApiReturnFormatTrait;

    public $campaign;
    public $product;
    public $brand;
    public $shop;

    public function __construct(CampaignInterface $campaign, ProductInterface $product, BrandInterface $brand, SellerInterface $shop)
    {
        $this->campaign = $campaign;
        $this->product  = $product;
        $this->brand    = $brand;
        $this->shop     = $shop;
    }

    public function campaigns()
    {
        try {
            $data = CampaignPaginateResource::collection($this->campaign->campaigns(get_pagination('api_paginate')));
            return $this->responseWithSuccess(__('Campaigns Retrieved'), $data, 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], null);
        }
    }

    public function campaignDetails($id): \Illuminate\Http\JsonResponse
    {
        $campaign = $this->campaign->get($id);
        if (!blank($campaign)):
            $data['campaign'] = [
                'id'                    => $campaign->id,
                'slug'                  => $campaign->slug,
                'title'                 => $campaign->title,
                'short_description'     => nullCheck($campaign->short_description),
                'start_date'            => nullCheck($campaign->start_date),
                'end_date'              => nullCheck($campaign->end_date),
                'banner'                => $campaign->image_1920x412,
            ];
            $data['brands']   = BrandResource::collection($this->brand->brandByCampaign($campaign->id,get_pagination('api_paginate')));
            $data['shops']    = ShopResource::collection(settingHelper('seller_system') == 1 ? $this->shop->shopByCampaign($campaign->id) : []);
            $data['products'] = ProductResource::collection($this->product->productByCampaign($campaign->id,get_pagination('api_paginate')));
            return $this->responseWithSuccess(__('Campaigns Details Retrieved'), $data, 200);
        else:
            return $this->responseWithError(__('Campaign not found'), [], null);
        endif;
    }

    public function campaignData(Request $request, BrandInterface $brand, CampaignInterface $campaign, SellerInterface $shop): \Illuminate\Http\JsonResponse
    {
        $campaign = $this->campaign->get($request->id);
        try {
            if ($request->type == 'shop') {
                $data = [
                    'shops' => settingHelper('seller_system') == 1 ? ShopResource::collection($shop->shopByCampaign($campaign->id,get_pagination('api_paginate'))) : [],
                ];
            } else {
                $data = [
                    'brands' => BrandResource::collection($brand->brandByCampaign($campaign->id,get_pagination('api_paginate'))),
                ];
            }

            return $this->responseWithSuccess(__('Campaigns Data Retrieved'), $data, 200);

        } catch (\Exception $e) {
            return $this->responseWithError(__('Campaign not found'), [], null);
        }
    }

    /**
     * Get the currently active campaign (Event-based)
     */
    public function activeCampaign(): \Illuminate\Http\JsonResponse
    {
        try {
            $pricingService = app(CampaignPricingService::class);
            $campaign = $pricingService->getActiveCampaign();

            if (!$campaign) {
                return $this->responseWithSuccess('No active campaign', [
                    'campaign' => null,
                    'products' => []
                ], 200);
            }

            $products = $campaign->getAllProducts()
                ->paginate(get_pagination('api_paginate'));

            // Apply campaign pricing to all products
            $products->getCollection()->transform(function ($product) use ($pricingService) {
                $pricing = $pricingService->getFinalPrice($product);
                $product->campaign_price = $pricing['price'];
                $product->original_price = $pricing['original_price'];
                $product->discount_info = $pricing['discount_info'];
                return $product;
            });

            return $this->responseWithSuccess('Active campaign retrieved', [
                'campaign' => [
                    'id' => $campaign->id,
                    'slug' => $campaign->slug,
                    'title' => $campaign->event_title,
                    'description' => $campaign->description,
                    'banner' => $campaign->image_1920x412,
                    'start_date' => $campaign->event_schedule_start,
                    'end_date' => $campaign->event_schedule_end,
                    'campaign_type' => $campaign->campaign_type,
                    'is_active_now' => $campaign->is_active_now,
                ],
                'products' => $products,
            ], 200);

        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], null);
        }
    }

    /**
     * Get campaign details by slug (Event-based)
     */
    public function campaignDetailsBySlug($slug): \Illuminate\Http\JsonResponse
    {
        try {
            $campaign = Event::where('slug', $slug)->first();

            if (!$campaign) {
                return $this->responseWithError('Campaign not found', [], 404);
            }

            // Check if expired
            if (!$campaign->is_active_now && $campaign->status !== 'upcoming') {
                return $this->responseWithError('This campaign has ended', [], 404);
            }

            $pricingService = app(CampaignPricingService::class);

            // Only return this campaign if it's active
            $isActive = ($pricingService->getActiveCampaign()?->id === $campaign->id);

            $productsQuery = $campaign->getAllProducts();

            if ($isActive) {
                // Apply campaign pricing for active campaign
                $products = $productsQuery->paginate(get_pagination('api_paginate'));
                $products->getCollection()->transform(function ($product) use ($pricingService) {
                    $pricing = $pricingService->getFinalPrice($product);
                    $product->campaign_price = $pricing['price'];
                    $product->original_price = $pricing['original_price'];
                    $product->discount_info = $pricing['discount_info'];
                    return $product;
                });
            } else {
                $products = $productsQuery->paginate(get_pagination('api_paginate'));
            }

            return $this->responseWithSuccess('Campaign details retrieved', [
                'campaign' => [
                    'id' => $campaign->id,
                    'slug' => $campaign->slug,
                    'title' => $campaign->event_title,
                    'description' => $campaign->description,
                    'banner' => $campaign->image_1920x412,
                    'start_date' => $campaign->event_schedule_start,
                    'end_date' => $campaign->event_schedule_end,
                    'campaign_type' => $campaign->campaign_type,
                    'is_active_now' => $campaign->is_active_now,
                    'is_active_campaign' => $isActive,
                ],
                'products' => $products,
                'filters' => [
                    'categories' => $campaign->campaign_type === 'category'
                        ? $campaign->categories
                        : [],
                    'brands' => $campaign->campaign_type === 'brand'
                        ? $campaign->brands
                        : [],
                ],
            ], 200);

        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], null);
        }
    }

    /**
     * Get products for a campaign (Event-based)
     */
    public function campaignProducts(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $slug = $request->slug;
            $campaign = Event::where('slug', $slug)->first();

            if (!$campaign) {
                return $this->responseWithError('Campaign not found', [], 404);
            }

            $pricingService = app(CampaignPricingService::class);
            $isActive = ($pricingService->getActiveCampaign()?->id === $campaign->id);

            $productsQuery = $campaign->getAllProducts();

            if ($isActive) {
                $products = $productsQuery->paginate(get_pagination('api_paginate'));
                $products->getCollection()->transform(function ($product) use ($pricingService) {
                    $pricing = $pricingService->getFinalPrice($product);
                    $product->campaign_price = $pricing['price'];
                    $product->original_price = $pricing['original_price'];
                    $product->discount_info = $pricing['discount_info'];
                    return $product;
                });
            } else {
                $products = $productsQuery->paginate(get_pagination('api_paginate'));
            }

            return $this->responseWithSuccess('Campaign products retrieved', [
                'products' => $products,
            ], 200);

        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], null);
        }
    }

    /**
     * Get filters for a campaign (categories, brands)
     */
    public function campaignFilters(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $slug = $request->slug;
            $campaign = Event::where('slug', $slug)->first();

            if (!$campaign) {
                return $this->responseWithError('Campaign not found', [], 404);
            }

            return $this->responseWithSuccess('Campaign filters retrieved', [
                'categories' => $campaign->campaign_type === 'category'
                    ? $campaign->load('categories.category')->categories
                    : [],
                'brands' => $campaign->campaign_type === 'brand'
                    ? $campaign->load('brands.brand')->brands
                    : [],
            ], 200);

        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], null);
        }
    }
}
