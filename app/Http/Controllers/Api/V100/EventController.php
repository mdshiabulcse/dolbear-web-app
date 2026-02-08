<?php

namespace App\Http\Controllers\Api\V100;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Event;
use App\Models\EventProduct;
use App\Models\Product;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    use ApiReturnFormatTrait;

    /**
     * Get all events
     */
    public function events(): \Illuminate\Http\JsonResponse
    {
        try {
            $events = Event::active()
                ->showOnFrontend()
                ->with(['activeEventProducts.product'])
                ->orderBy('event_priority', 'asc')
                ->paginate(get_pagination('api_paginate'));

            $data = [];
            foreach ($events as $event) {
                $data[] = [
                    'id' => $event->id,
                    'slug' => $event->slug,
                    'title' => $event->event_title,
                    'description' => $event->description,
                    'banner' => $event->image_1920x412,
                    'banner_406x235' => $event->image_406x235,
                    'banner_374x374' => $event->image_374x374,
                    'event_type' => $event->event_type,
                    'start_date' => $event->event_start_date,
                    'end_date' => $event->event_end_date,
                    'duration' => $event->event_duration,
                    'is_active_now' => $event->is_active_now,
                    'background_color' => $event->background_color,
                    'text_color' => $event->text_color,
                    'total_products' => $event->total_products,
                    'total_views' => $event->total_views,
                ];
            }

            return $this->responseWithSuccess(__('Events Retrieved'), [
                'events' => $data,
                'pagination' => [
                    'total' => $events->total(),
                    'per_page' => $events->perPage(),
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], null);
        }
    }

    /**
     * Get active events currently running
     */
    public function activeEvents(): \Illuminate\Http\JsonResponse
    {
        try {
            $events = Event::currentlyRunning()
                ->showOnFrontend()
                ->with(['activeEventProducts.product'])
                ->orderBy('event_priority', 'asc')
                ->get();

            $data = [];
            foreach ($events as $event) {
                $data[] = [
                    'id' => $event->id,
                    'slug' => $event->slug,
                    'title' => $event->event_title,
                    'description' => $event->description,
                    'banner' => $event->image_1920x412,
                    'banner_406x235' => $event->image_406x235,
                    'banner_374x374' => $event->image_374x374,
                    'event_type' => $event->event_type,
                    'start_date' => $event->event_start_date,
                    'end_date' => $event->event_end_date,
                    'duration' => $event->event_duration,
                    'background_color' => $event->background_color,
                    'text_color' => $event->text_color,
                    'total_products' => $event->total_products,
                ];
            }

            return $this->responseWithSuccess(__('Active Events Retrieved'), ['events' => $data], 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], null);
        }
    }

    /**
     * Get event details
     */
    public function eventDetails($id): \Illuminate\Http\JsonResponse
    {
        try {
            $event = Event::with(['eventProducts.product', 'createdBy'])
                ->where('id', $id)
                ->where('status', 'active')
                ->first();

            if (!$event) {
                return $this->responseWithError(__('Event not found'), [], null);
            }

            // Increment views
            $event->incrementViews();

            $data['event'] = [
                'id' => $event->id,
                'slug' => $event->slug,
                'title' => $event->event_title,
                'description' => $event->description,
                'banner' => $event->image_1920x412,
                'banner_406x235' => $event->image_406x235,
                'banner_374x374' => $event->image_374x374,
                'event_type' => $event->event_type,
                'start_date' => $event->event_start_date,
                'end_date' => $event->event_end_date,
                'duration' => $event->event_duration,
                'is_active_now' => $event->is_active_now,
                'background_color' => $event->background_color,
                'text_color' => $event->text_color,
                'total_products' => $event->total_products,
                'total_views' => $event->total_views,
                'total_sales' => $event->total_sales,
                'total_revenue' => $event->total_revenue,
            ];

            return $this->responseWithSuccess(__('Event Details Retrieved'), $data, 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], null);
        }
    }

    /**
     * Get event products
     */
    public function eventProducts(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $eventId = $request->id ?? null;

            if (!$eventId) {
                return $this->responseWithError(__('Event ID is required'), [], null);
            }

            $event = Event::where('id', $eventId)
                ->where('status', 'active')
                ->first();

            if (!$event) {
                return $this->responseWithError(__('Event not found'), [], null);
            }

            $eventProducts = EventProduct::with('product')
                ->where('event_id', $eventId)
                ->where('is_active', 1)
                ->where('status', 'active')
                ->orderBy('product_priority', 'asc')
                ->paginate(get_pagination('api_paginate'));

            $products = [];
            foreach ($eventProducts as $eventProduct) {
                $product = $eventProduct->product;
                if ($product) {
                    $finalPrice = $eventProduct->final_price;
                    $discount = $eventProduct->formatted_discount;

                    $productData = [
                        'id' => $product->id,
                        'slug' => $product->slug,
                        'name' => $product->product_name,
                        'image_190x230' => $product->image_190x230,
                        'image_40x40' => $product->image_40x40,
                        'rating' => $product->rating,
                        'current_stock' => $product->current_stock,
                        'is_wholesale' => $product->is_wholesale,
                        'is_digital' => $product->is_digital,
                        'original_price' => $product->price,
                        'event_price' => $eventProduct->event_price,
                        'final_price' => $finalPrice,
                        'discount_amount' => $eventProduct->discount_amount,
                        'discount_type' => $eventProduct->discount_type,
                        'discount_display' => $discount,
                        'badge_text' => $eventProduct->badge_text,
                        'badge_color' => $eventProduct->badge_color,
                        'event_stock' => $eventProduct->event_stock,
                        'event_stock_remaining' => $eventProduct->remaining_stock,
                        'is_sold_out' => $eventProduct->is_sold_out,
                    ];

                    $products[] = $productData;
                }
            }

            $data = [
                'event_id' => $event->id,
                'event_title' => $event->event_title,
                'event_slug' => $event->slug,
                'event_banner' => $event->image_1920x412,
                'products' => $products,
                'pagination' => [
                    'total' => $eventProducts->total(),
                    'per_page' => $eventProducts->perPage(),
                    'current_page' => $eventProducts->currentPage(),
                    'last_page' => $eventProducts->lastPage(),
                ]
            ];

            return $this->responseWithSuccess(__('Event Products Retrieved'), $data, 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], null);
        }
    }

    /**
     * Get event data with filtering options
     */
    public function eventData(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $eventId = $request->id ?? null;

            if (!$eventId) {
                return $this->responseWithError(__('Event ID is required'), [], null);
            }

            $event = Event::where('id', $eventId)
                ->where('status', 'active')
                ->first();

            if (!$event) {
                return $this->responseWithError(__('Event not found'), [], null);
            }

            $query = EventProduct::with('product')
                ->where('event_id', $eventId)
                ->where('is_active', 1)
                ->where('status', 'active')
                ->orderBy('product_priority', 'asc');

            // Apply filters if provided
            if ($request->has('min_price')) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('price', '>=', $request->min_price);
                });
            }

            if ($request->has('max_price')) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('price', '<=', $request->max_price);
                });
            }

            if ($request->has('brand_id')) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('brand_id', $request->brand_id);
                });
            }

            if ($request->has('category_id')) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            $eventProducts = $query->paginate(get_pagination('api_paginate'));

            $products = [];
            foreach ($eventProducts as $eventProduct) {
                $product = $eventProduct->product;
                if ($product) {
                    $products[] = [
                        'id' => $product->id,
                        'slug' => $product->slug,
                        'name' => $product->product_name,
                        'image_190x230' => $product->image_190x230,
                        'price' => $product->price,
                        'event_price' => $eventProduct->final_price,
                        'discount' => $eventProduct->formatted_discount,
                        'rating' => $product->rating,
                    ];
                }
            }

            $data = [
                'products' => $products,
                'filters' => [
                    'min_price' => $request->min_price ?? null,
                    'max_price' => $request->max_price ?? null,
                    'brand_id' => $request->brand_id ?? null,
                    'category_id' => $request->category_id ?? null,
                ],
                'pagination' => [
                    'total' => $eventProducts->total(),
                    'per_page' => $eventProducts->perPage(),
                    'current_page' => $eventProducts->currentPage(),
                    'last_page' => $eventProducts->lastPage(),
                ]
            ];

            return $this->responseWithSuccess(__('Event Data Retrieved'), $data, 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], null);
        }
    }
}