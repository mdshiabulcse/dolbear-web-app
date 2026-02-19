<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventProduct;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Campaign Pricing Service
 *
 * Implements STRICT time-wise priority validation for event-based pricing.
 * Only ONE event can be active at any given time.
 *
 * Priority Rules:
 * 1. Sehri Event (daily, during Sehri time only) - Priority 1 (highest)
 * 2. Iftar Event (daily, during Iftar time only) - Priority 2
 * 3. Monthly/General Event (date_range) - Priority 3 (lowest)
 *
 * Time-based activation is FULLY AUTOMATIC - no manual conflict resolution needed.
 */
class CampaignPricingService
{
    protected $activeCampaigns = null;

    public function __construct()
    {
        $this->loadActiveCampaigns();
    }

    /**
     * STEP 1: Event Activation (Time-wise Priority - STRICT)
     *
     * Priority Rules (automatic based on time):
     * 1. Sehri Event → Active ONLY during Sehri time window (e.g., 03:00-04:00)
     * 2. Iftar Event → Active ONLY during Iftar time window (e.g., 17:30-19:00)
     * 3. Monthly Event → Active ONLY during date range (e.g., Feb 1 - Feb 28)
     * 4. Expired events → NEVER active on frontend
     *
     * ONLY ONE event can be active at any given time.
     * The system automatically selects based on current time and event_priority.
     */
    protected function loadActiveCampaigns(): void
    {
        $this->activeCampaigns = Cache::remember('active_campaigns', 30, function () {
            $now = Carbon::now();
            $currentTime = $now->format('H:i:s');
            $currentDateTime = $now->format('Y-m-d H:i:s');

            Log::info('CampaignPricingService: Checking active campaigns', [
                'current_time' => $currentDateTime,
                'current_time_only' => $currentTime,
            ]);

            // Get all ENABLED events (status='active' AND is_active=1)
            $events = Event::where('status', 'active')
                ->where('is_active', 1)
                ->whereNull('deactivated_at')  // Not explicitly deactivated
                ->with(['activeEventProducts' => function ($q) {
                    $q->where('is_active', 1)->where('status', 'active');
                }])
                ->get();

            $currentlyRunningEvents = [];

            foreach ($events as $event) {
                $isRunning = false;
                $reason = '';

                // Check if event is currently running based on its type
                if ($event->event_type === 'daily') {
                    // DAILY EVENTS (Sehri/Iftar) - Check time window
                    // These are ONLY active during their defined time slot
                    if ($event->daily_start_time && $event->daily_end_time) {
                        if ($currentTime >= $event->daily_start_time && $currentTime <= $event->daily_end_time) {
                            $isRunning = true;
                            $reason = 'Daily time window active';
                        } else {
                            $reason = 'Daily time window not active';
                        }
                    } else {
                        $reason = 'Daily event missing time slots';
                    }

                } elseif ($event->event_type === 'date_range') {
                    // DATE RANGE EVENTS (Monthly/General) - Check date/time range
                    if ($event->event_schedule_start && $event->event_schedule_end) {
                        if ($currentDateTime >= $event->event_schedule_start && $currentDateTime <= $event->event_schedule_end) {
                            // Additional check: ensure no daily event is currently active
                            // Daily events take priority over date_range events
                            $hasActiveDailyEvent = $events->contains(function ($e) use ($currentTime) {
                                return $e->event_type === 'daily'
                                    && $e->daily_start_time
                                    && $e->daily_end_time
                                    && $currentTime >= $e->daily_start_time
                                    && $currentTime <= $e->daily_end_time;
                            });

                            if ($hasActiveDailyEvent) {
                                $isRunning = false;
                                $reason = 'Superseded by daily event';
                            } else {
                                $isRunning = true;
                                $reason = 'Date range active';
                            }
                        } else {
                            $reason = 'Outside date range';
                        }
                    } else {
                        $reason = 'Date range missing schedule';
                    }
                }

                Log::info('CampaignPricingService: Event check', [
                    'event_id' => $event->id,
                    'event_title' => $event->event_title,
                    'event_type' => $event->event_type,
                    'event_priority' => $event->event_priority,
                    'is_running' => $isRunning,
                    'reason' => $reason,
                    'daily_start' => $event->daily_start_time,
                    'daily_end' => $event->daily_end_time,
                    'date_range_start' => $event->event_schedule_start,
                    'date_range_end' => $event->event_schedule_end,
                ]);

                if ($isRunning) {
                    $currentlyRunningEvents[] = $event;
                }
            }

            // STRICT: Sort by event_priority (lower number = higher priority)
            usort($currentlyRunningEvents, function ($a, $b) {
                return $a->event_priority <=> $b->event_priority;
            });

            // Take ONLY the first (highest priority) event
            $finalCampaigns = array_slice($currentlyRunningEvents, 0, 1);

            Log::info('CampaignPricingService: Active campaign determined', [
                'total_running' => count($currentlyRunningEvents),
                'selected_count' => count($finalCampaigns),
                'current_time' => $currentDateTime,
                'all_running_ids' => array_map(fn($e) => $e->id, $currentlyRunningEvents),
                'selected_event_id' => !empty($finalCampaigns) ? $finalCampaigns[0]->id : null,
                'selected_event_title' => !empty($finalCampaigns) ? $finalCampaigns[0]->event_title : null,
            ]);

            // Return ONLY the single active campaign (or empty collection)
            return collect($finalCampaigns);
        });
    }

    /**
     * Get the single currently active campaign
     * Returns null if no campaign is currently active
     */
    public function getActiveCampaign(): ?Event
    {
        return $this->activeCampaigns?->first();
    }

    /**
     * Check if any campaign is currently active
     */
    public function hasActiveCampaign(): bool
    {
        return $this->activeCampaigns && !$this->activeCampaigns->isEmpty();
    }

    /**
     * STEP 2 & 3: Event Product Mapping & Price Override
     *
     * Using the active event_id, search the event_product table
     * Match event_product.product_id with products.id
     *
     * Returns campaign pricing data with:
     * - Override price using event_product.event_price
     * - Original price for display
     * - Discount amount/percentage
     * - Badge text and color from event configuration
     *
     * Does NOT modify the original product table
     */
    public function getCampaignPriceForObject(Product $product): ?array
    {
        if (!$this->hasActiveCampaign()) {
            Log::info('CampaignPricingService: No active campaign', ['product_id' => $product->id]);
            return null;
        }

        $campaign = $this->getActiveCampaign();
        if (!$campaign) {
            Log::warning('CampaignPricingService: Active campaign object is null', ['product_id' => $product->id]);
            return null;
        }

        Log::info('CampaignPricingService: Searching for event product', [
            'campaign_id' => $campaign->id,
            'campaign_title' => $campaign->event_title,
            'product_id' => $product->id,
        ]);

        // Search event_product table for this product
        $eventProduct = $campaign->eventProducts()
            ->where('product_id', $product->id)
            ->where('is_active', 1)
            ->where('status', 'active')
            ->first();

        if (!$eventProduct) {
            Log::info('CampaignPricingService: Product not found in event_products', [
                'campaign_id' => $campaign->id,
                'product_id' => $product->id,
            ]);
            return null;
        }

        Log::info('CampaignPricingService: Event product found', [
            'event_product_id' => $eventProduct->id,
            'event_price' => $eventProduct->event_price,
            'discount_amount' => $eventProduct->discount_amount,
        ]);

        // Calculate campaign price
        $campaignPrice = $this->calculateCampaignPrice($product, $eventProduct);

        // Return null if no valid campaign price
        if ($campaignPrice === null || $campaignPrice <= 0) {
            return null;
        }

        // Calculate discount details
        $discountAmount = $product->price - $campaignPrice;
        $discountPercentage = $product->price > 0 ? ($discountAmount / $product->price) * 100 : 0;

        // Determine badge (event_product level overrides event level)
        $badgeText = $eventProduct->badge_text ?? $campaign->badge_text ?? $this->getDefaultBadgeText($campaign);
        $badgeColor = $eventProduct->badge_color ?? $campaign->badge_color ?? '#ff0000';

        // Format discount for display
        if ($eventProduct->discount_amount > 0) {
            if ($eventProduct->discount_type === 'percentage') {
                $formattedDiscount = number_format($eventProduct->discount_amount, 0) . '%';
            } else {
                $formattedDiscount = get_price($eventProduct->discount_amount) . ' off';
            }
        } else {
            $formattedDiscount = number_format($discountPercentage, 0) . '%';
        }

        return [
            'price' => $campaignPrice,
            'original_price' => $product->price,
            'discount_amount' => $discountAmount,
            'discount_percentage' => $discountPercentage,
            'discount_type' => $eventProduct->discount_type,
            'formatted_discount' => $formattedDiscount,
            'badge_text' => $badgeText,
            'badge_color' => $badgeColor,
            'campaign_id' => $campaign->id,
            'campaign_title' => $campaign->event_title,
            'is_inherited' => false,
        ];
    }

    /**
     * Get default badge text based on event type
     */
    protected function getDefaultBadgeText(Event $campaign): string
    {
        if ($campaign->event_type === 'daily') {
            // For daily events, use a time-based badge
            $now = Carbon::now();
            $currentTime = $now->format('H:i');

            if ($campaign->daily_end_time) {
                $endTime = Carbon::parse($campaign->daily_end_time);
                $minutesLeft = $endTime->diffInMinutes($now);

                if ($minutesLeft > 0) {
                    return 'Ends in ' . floor($minutesLeft / 60) . 'h ' . ($minutesLeft % 60) . 'm';
                }
            }

            return 'Limited Time';
        }

        return 'Campaign Deal';
    }

    /**
     * Calculate campaign price for a product
     * Priority: event_price > discount_amount calculation > original price
     */
    protected function calculateCampaignPrice(Product $product, EventProduct $eventProduct): ?float
    {
        $campaignPrice = null;

        // 1. Use event_price if explicitly set and valid (must be greater than 0)
        if ($eventProduct->event_price !== null && $eventProduct->event_price > 0) {
            $campaignPrice = (float) $eventProduct->event_price;
        }
        // 2. Calculate from discount_amount (must be greater than 0)
        elseif ($eventProduct->discount_amount > 0) {
            if ($eventProduct->discount_type === 'percentage') {
                $campaignPrice = $product->price - ($product->price * $eventProduct->discount_amount / 100);
            } else {
                $campaignPrice = $product->price - $eventProduct->discount_amount;
            }
        }
        // 3. Use final_price if available (must be greater than 0)
        elseif ($eventProduct->final_price !== null && $eventProduct->final_price > 0) {
            $campaignPrice = (float) $eventProduct->final_price;
        }

        // Validate price - must be greater than 0 and less than or equal to original price
        if ($campaignPrice !== null) {
            $campaignPrice = max(0, min($campaignPrice, $product->price));

            // If calculated price is 0 or equals original price, return null (no valid discount)
            if ($campaignPrice <= 0 || $campaignPrice >= $product->price) {
                return null;
            }
        }

        return $campaignPrice;
    }

    /**
     * Get campaign price by product ID
     */
    public function getCampaignPrice(int $productId): ?array
    {
        $product = Product::find($productId);
        if (!$product) {
            Log::warning('CampaignPricingService: Product not found', ['product_id' => $productId]);
            return null;
        }

        Log::info('CampaignPricingService: Getting campaign price', [
            'product_id' => $productId,
            'has_active_campaign' => $this->hasActiveCampaign(),
            'active_campaign_id' => $this->getActiveCampaign()?->id ?? null,
        ]);

        return $this->getCampaignPriceForObject($product);
    }

    /**
     * STEP 5: Fallback Logic
     * Priority: Campaign > Product Special Discount > Base Price
     */
    public function getFinalPrice(Product $product): array
    {
        // 1. Campaign Price (Highest Priority)
        $campaignPrice = $this->getCampaignPriceForObject($product);

        if ($campaignPrice && $campaignPrice['price'] < $product->price) {
            return [
                'price' => $campaignPrice['price'],
                'original_price' => $campaignPrice['original_price'],
                'discount_source' => 'campaign',
                'discount_info' => $campaignPrice,
            ];
        }

        // 2. Product Special Discount (Medium Priority)
        $now = Carbon::now();
        if ($product->special_discount > 0
            && $product->special_discount_start
            && $product->special_discount_end
            && $now->between($product->special_discount_start, $product->special_discount_end)
        ) {
            $specialPrice = $product->price;
            if ($product->special_discount_type === 'percentage') {
                $specialPrice = $product->price - ($product->price * $product->special_discount / 100);
            } else {
                $specialPrice = $product->price - $product->special_discount;
            }
            $specialPrice = max(0, $specialPrice);

            if ($specialPrice < $product->price) {
                return [
                    'price' => $specialPrice,
                    'original_price' => $product->price,
                    'discount_source' => 'special',
                    'discount_info' => [
                        'discount_type' => $product->special_discount_type,
                        'discount_amount' => $product->special_discount,
                    ],
                ];
            }
        }

        // 3. Base Price (Lowest Priority)
        return [
            'price' => $product->price,
            'original_price' => $product->price,
            'discount_source' => 'none',
            'discount_info' => null,
        ];
    }

    /**
     * Clear cache when campaign changes
     * Call this when:
     * - Event is activated/deactivated
     * - Event products are added/removed/updated
     * - Event schedule is modified
     */
    public function clearCache(): void
    {
        Cache::forget('active_campaigns');
        $this->activeCampaigns = null;
        $this->loadActiveCampaigns();

        Log::info('CampaignPricingService: Cache cleared');
    }

    /**
     * Auto-expire events that have passed their end time
     * This should be called periodically (e.g., via cron job)
     */
    public function autoExpireEvents(): int
    {
        $now = Carbon::now();
        $currentDateTime = $now->format('Y-m-d H:i:s');
        $currentTime = $now->format('H:i:s');

        $expiredCount = 0;

        // Get all active events
        $activeEvents = Event::where('status', 'active')
            ->where('is_active', 1)
            ->get();

        foreach ($activeEvents as $event) {
            $shouldExpire = false;

            if ($event->event_type === 'daily') {
                // Daily events expire when end time passes for the day
                // They can be reactivated next day at start time
                if ($event->daily_end_time && $currentTime > $event->daily_end_time) {
                    $shouldExpire = true;
                }
            } elseif ($event->event_type === 'date_range') {
                // Date range events expire when end datetime passes
                if ($event->event_schedule_end && $currentDateTime > $event->event_schedule_end) {
                    $shouldExpire = true;
                }
            }

            if ($shouldExpire) {
                $event->update([
                    'status' => 'expired',
                    'is_active' => 0,
                    'deactivated_at' => $now,
                ]);

                Log::info('CampaignPricingService: Event auto-expired', [
                    'event_id' => $event->id,
                    'event_title' => $event->event_title,
                    'event_type' => $event->event_type,
                ]);

                $expiredCount++;
            }
        }

        if ($expiredCount > 0) {
            $this->clearCache();
        }

        return $expiredCount;
    }
}