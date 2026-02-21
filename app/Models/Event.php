<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Event extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = [
        'image_1920x412',
        'image_406x235',
        'image_374x374',
        'image_1920x412',
        'image_406x235',
        'banner_image_original',
        'event_start_date',
        'event_end_date',
        'is_active_now',
        'event_duration'
    ];

    protected $casts = [
        'recurring_settings' => 'array',
        'banner_image' => 'array',
    ];

    protected $attributes = [
        'banner_image' => '[]',
        'recurring_settings' => '[]',
    ];

    protected $fillable = [
        'event_title',
        'slug',
        'description',
        'banner_image',
        'banner_image_id',
        'event_priority',
        'event_type',
        'event_schedule_start',
        'event_schedule_end',
        'recurring_settings',
        'daily_start_time',
        'daily_end_time',
        'background_color',
        'text_color',
        'show_on_frontend',
        'status',
        'is_active',
        'total_products',
        'total_views',
        'total_sales',
        'total_revenue',
        'created_by',
        'updated_by',
        // Campaign fields
        'campaign_type',
        'activated_at',
        'deactivated_at',
        'default_discount',
        'default_discount_type',
        'badge_text',
        'badge_color',
    ];

    /**
     * Relationship: Event has many products
     */
    public function eventProducts()
    {
        return $this->hasMany(EventProduct::class)->orderBy('product_priority', 'asc');
    }

    /**
     * Relationship: Event has many active products
     */
    public function activeEventProducts()
    {
        return $this->hasMany(EventProduct::class)
            ->where('is_active', 1)
            ->where('status', 'active')
            ->orderBy('product_priority', 'asc');
    }

    /**
     * Relationship: Event belongs to creator
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Event belongs to updater
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship: Event has many event categories (for category-based campaigns)
     */
    public function eventCategories()
    {
        return $this->hasMany(EventCategory::class);
    }

    /**
     * Relationship: Event has many event brands (for brand-based campaigns)
     */
    public function eventBrands()
    {
        return $this->hasMany(EventBrand::class);
    }

    /**
     * Relationship: Event belongs to many categories
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'event_categories');
    }

    /**
     * Relationship: Event belongs to many brands
     */
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'event_brands');
    }

    /**
     * Scope: Get active events
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('is_active', 1);
    }

    /**
     * Scope: Get events currently running based on schedule
     */
    public function scopeCurrentlyRunning($query)
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        return $query->where('status', 'active')
            ->where('is_active', 1)
            ->where(function ($q) use ($now) {
                $q->where('event_type', 'daily')
                    ->orWhere(function ($q) use ($now) {
                        $q->where('event_type', 'date_range')
                            ->where('event_schedule_start', '<=', $now)
                            ->where('event_schedule_end', '>=', $now);
                    });
            })
            ->orderBy('event_priority', 'asc');
    }

    /**
     * Scope: Get upcoming events
     */
    public function scopeUpcoming($query)
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        return $query->where('status', 'active')
            ->where('is_active', 1)
            ->where('event_schedule_start', '>', $now)
            ->orderBy('event_schedule_start', 'asc');
    }

    /**
     * Scope: Get expired events
     */
    public function scopeExpired($query)
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        return $query->where('event_schedule_end', '<', $now)
            ->orderBy('event_schedule_end', 'desc');
    }

    /**
     * Scope: Show on frontend
     */
    public function scopeShowOnFrontend($query)
    {
        return $query->where('show_on_frontend', 1);
    }

    /**
     * Scope: By priority (lower number = higher priority)
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('event_priority', 'asc');
    }

    /**
     * Scope: Get the single currently active campaign
     * Returns only one campaign - the highest priority active campaign
     */
    public function scopeSingleActive($query)
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        return $query->where('status', 'active')
            ->where('is_active', 1)
            ->whereNotNull('activated_at')
            ->whereNull('deactivated_at')
            ->where(function ($q) use ($now) {
                $q->where('event_type', 'daily')
                    ->orWhere(function ($q) use ($now) {
                        $q->where('event_type', 'date_range')
                            ->where('event_schedule_start', '<=', $now)
                            ->where('event_schedule_end', '>=', $now);
                    });
            })
            ->orderBy('event_priority', 'asc')
            ->limit(1);
    }

    /**
     * Attribute: Get banner image original
     */
    public function getBannerImageOriginalAttribute()
    {
        // Try to get from Media model using banner_image_id
        if ($this->banner_image_id) {
            $media = \App\Models\Media::find($this->banner_image_id);
            if ($media && $media->original_file) {
                return @is_file_exists($media->original_file, $media->storage)
                    ? @get_media($media->original_file, $media->storage)
                    : static_asset('images/default/default-image-1280x420.png');
            }
        }
        // Fallback to banner_image array
        return @is_file_exists(@$this->banner_image['original_image'], @$this->banner_image['storage'])
            ? @get_media(@$this->banner_image['original_image'], @$this->banner_image['storage'])
            : static_asset('images/default/default-image-1280x420.png');
    }

    /**
     * Attribute: Get banner image 1920x412
     */
    public function getImage1920x412Attribute()
    {
        // Try to get from Media model using banner_image_id
        if ($this->banner_image_id) {
            $media = \App\Models\Media::find($this->banner_image_id);
            if ($media) {
                $variants = $media->image_variants ?? [];
                $storage = $media->storage ?? 'local';
                // Check for image_1920x412 variant
                if (isset($variants['image_1920x412']) && @is_file_exists($variants['image_1920x412'], $storage)) {
                    return @get_media($variants['image_1920x412'], $storage);
                }
                // Fallback to original file
                if ($media->original_file && @is_file_exists($media->original_file, $storage)) {
                    return @get_media($media->original_file, $storage);
                }
            }
        }
        // Fallback to banner_image array
        return getFileLink('1920x412', $this->banner_image);
    }

    /**
     * Attribute: Get banner image 406x235
     */
    public function getImage406x235Attribute()
    {
        // Try to get from Media model using banner_image_id
        if ($this->banner_image_id) {
            $media = \App\Models\Media::find($this->banner_image_id);
            if ($media) {
                $variants = $media->image_variants ?? [];
                $storage = $media->storage ?? 'local';
                // Check for image_406x235 variant
                if (isset($variants['image_406x235']) && @is_file_exists($variants['image_406x235'], $storage)) {
                    return @get_media($variants['image_406x235'], $storage);
                }
                // Fallback to original file
                if ($media->original_file && @is_file_exists($media->original_file, $storage)) {
                    return @get_media($media->original_file, $storage);
                }
            }
        }
        // Fallback to banner_image array
        return getFileLink('406x235', $this->banner_image);
    }

    /**
     * Attribute: Get banner image 374x374
     */
    public function getImage374x374Attribute()
    {
        // Try to get from Media model using banner_image_id
        if ($this->banner_image_id) {
            $media = \App\Models\Media::find($this->banner_image_id);
            if ($media) {
                $variants = $media->image_variants ?? [];
                $storage = $media->storage ?? 'local';
                // Check for image_374x374 or image_72x72 variant
                if (isset($variants['image_374x374']) && @is_file_exists($variants['image_374x374'], $storage)) {
                    return @get_media($variants['image_374x374'], $storage);
                }
                if (isset($variants['image_72x72']) && @is_file_exists($variants['image_72x72'], $storage)) {
                    return @get_media($variants['image_72x72'], $storage);
                }
                // Fallback to original file
                if ($media->original_file && @is_file_exists($media->original_file, $storage)) {
                    return @get_media($media->original_file, $storage);
                }
            }
        }
        // Fallback to banner_image array
        return getFileLink('374x374', $this->banner_image);
    }

    /**
     * Attribute: Get formatted start date
     */
    public function getEventStartDateAttribute(): string
    {
        return $this->event_schedule_start
            ? Carbon::parse($this->event_schedule_start)->format('d M Y H:i')
            : '';
    }

    /**
     * Attribute: Get formatted end date
     */
    public function getEventEndDateAttribute(): string
    {
        return $this->event_schedule_end
            ? Carbon::parse($this->event_schedule_end)->format('d M Y H:i')
            : '';
    }

    /**
     * Attribute: Check if event is currently active
     * For daily events (Sehri/Iftar): Check if current time is within the daily time slot
     * For date_range events (Monthly): Check if current datetime is within the date range
     *
     * IMPORTANT: Daily events are ONLY active during their time window, NOT 24/7
     */
    public function getIsActiveNowAttribute(): bool
    {
        // Event must be enabled (status=active AND is_active=1)
        if ($this->status != 'active' || !$this->is_active) {
            return false;
        }

        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        $currentDateTime = $now->format('Y-m-d H:i:s');

        if ($this->event_type == 'daily') {
            // Daily events (Sehri/Iftar) - ONLY active during time window
            // This is critical: daily events are NOT active 24/7
            if ($this->daily_start_time && $this->daily_end_time) {
                $isActive = $currentTime >= $this->daily_start_time && $currentTime <= $this->daily_end_time;
                return $isActive;
            }
            // Daily event WITHOUT time slots is NOT considered valid
            // Admin must set time slots for daily events
            return false;

        } elseif ($this->event_type == 'date_range') {
            // Date range events (Monthly/General) - check date/time range
            if ($this->event_schedule_start && $this->event_schedule_end) {
                return $currentDateTime >= $this->event_schedule_start && $currentDateTime <= $this->event_schedule_end;
            }
            return false;
        }

        return false;
    }

    /**
     * Attribute: Get event duration in human readable format
     */
    public function getEventDurationAttribute(): string
    {
        if (!$this->event_schedule_start || !$this->event_schedule_end) {
            return '';
        }

        $start = Carbon::parse($this->event_schedule_start);
        $end = Carbon::parse($this->event_schedule_end);
        $diff = $start->diff($end);

        $durationParts = [];
        if ($diff->d > 0) {
            $durationParts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
        }
        if ($diff->h > 0) {
            $durationParts[] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        }
        if ($diff->i > 0) {
            $durationParts[] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        }

        return implode(', ', $durationParts);
    }

    /**
     * Method: Add product to event
     */
    public function addProduct($productId, $eventPrice = null, $discountAmount = 0, $discountType = 'flat', $priority = 0, $eventStock = null)
    {
        \Log::info('Event::addProduct - Adding product to event', [
            'event_id' => $this->id,
            'product_id' => $productId,
            'event_price' => $eventPrice,
            'discount_amount' => $discountAmount,
            'discount_type' => $discountType,
        ]);

        // Check if product already exists in event
        $existingProduct = $this->eventProducts()->where('product_id', $productId)->first();

        if ($existingProduct) {
            \Log::info('Event::addProduct - Product already exists, updating', [
                'event_product_id' => $existingProduct->id
            ]);

            // Update existing product
            $updateData = [
                'event_price' => $eventPrice,
                'discount_amount' => $discountAmount,
                'discount_type' => $discountType,
                'product_priority' => $priority,
                'is_active' => 1,
                'status' => 'active',
            ];

            // Only include event_stock if the column exists
            if (Schema::hasColumn('event_products', 'event_stock')) {
                $updateData['event_stock'] = $eventStock;
            }

            // Add final_price if column exists
            if (Schema::hasColumn('event_products', 'final_price')) {
                $updateData['final_price'] = $eventPrice;
            }

            $existingProduct->update($updateData);
            return $existingProduct;
        }

        \Log::info('Event::addProduct - Creating new event product');

        // Prepare creation data
        $createData = [
            'product_id' => $productId,
            'event_price' => $eventPrice,
            'discount_amount' => $discountAmount,
            'discount_type' => $discountType,
            'product_priority' => $priority,
            'event_stock_sold' => 0,
            'is_active' => 1,
            'status' => 'active',
            'created_by' => authId() ?? 1,
        ];

        // Only include event_stock if the column exists and value is provided
        if (Schema::hasColumn('event_products', 'event_stock') && $eventStock !== null) {
            $createData['event_stock'] = $eventStock;
        }

        // Add final_price if column exists
        if (Schema::hasColumn('event_products', 'final_price')) {
            $createData['final_price'] = $eventPrice;
        }

        // Create new event product
        $eventProduct = $this->eventProducts()->create($createData);

        \Log::info('Event::addProduct - Event product created', [
            'event_product_id' => $eventProduct->id,
            'created_at' => $eventProduct->created_at
        ]);

        return $eventProduct;
    }

    /**
     * Method: Remove product from event
     */
    public function removeProduct($productId)
    {
        return $this->eventProducts()->where('product_id', $productId)->delete();
    }

    /**
     * Method: Update total products count
     */
    public function updateTotalProducts()
    {
        $this->update([
            'total_products' => $this->eventProducts()->count()
        ]);
    }

    /**
     * Method: Increment views
     */
    public function incrementViews()
    {
        $this->increment('total_views');
    }

    /**
     * Method: Activate this event
     * - For date_range events: Deactivates all other active date_range campaigns
     * - For daily events: Allows multiple daily events with different time slots
     */
    public function activate()
    {
        DB::transaction(function () {
            $now = Carbon::now();

            // Only deactivate other date_range campaigns
            // Daily time-slotted events can coexist
            if ($this->event_type === 'date_range') {
                $deactivateQuery = self::where('id', '!=', $this->id)
                    ->where('status', 'active')
                    ->where('is_active', 1)
                    ->where('event_type', 'date_range');

                // Check if activated_at column exists before using it
                if (Schema::hasColumn('events', 'activated_at')) {
                    $deactivateQuery->whereNotNull('activated_at');
                }

                $updateData = [
                    'is_active' => 0,
                    'status' => 'paused'
                ];

                if (Schema::hasColumn('events', 'deactivated_at')) {
                    $updateData['deactivated_at'] = $now;
                }

                $deactivateQuery->update($updateData);
            }

            // Activate this event
            $activateData = [
                'is_active' => 1,
                'status' => 'active'
            ];

            if (Schema::hasColumn('events', 'activated_at')) {
                $activateData['activated_at'] = $now;
            }

            if (Schema::hasColumn('events', 'deactivated_at')) {
                $activateData['deactivated_at'] = null;
            }

            $this->update($activateData);
        });

        // Clear campaign pricing cache to ensure real-time updates
        try {
            if (class_exists(\App\Services\CampaignPricingService::class)) {
                app(\App\Services\CampaignPricingService::class)->clearCache();
            }
        } catch (\Exception $e) {
            // Log but don't fail activation
            \Log::error('Failed to clear campaign cache on activation: ' . $e->getMessage());
        }
    }

    /**
     * Method: Auto-expire when end date passes
     * Returns true if event was expired, false otherwise
     */
    public function checkExpiration()
    {
        if ($this->event_type == 'date_range' && $this->event_schedule_end) {
            if (Carbon::now()->gt($this->event_schedule_end)) {
                $this->update([
                    'status' => 'expired',
                    'is_active' => 0,
                ]);
                return true;
            }
        }
        return false;
    }

    /**
     * Method: Get all products for this event (including category/brand inherited)
     * Returns a query builder for further filtering
     */
    public function getAllProducts()
    {
        $productIds = collect();

        // Direct products
        $productIds = $productIds->merge(
            $this->activeEventProducts()->pluck('product_id')
        );

        // Category-based products (only if table exists)
        if (Schema::hasTable('event_categories') && Schema::hasColumn('events', 'campaign_type')) {
            $campaignType = $this->campaign_type ?? 'product';
            if ($campaignType == 'category') {
                try {
                    foreach ($this->eventCategories as $eventCategory) {
                        $categoryIds = [$eventCategory->category_id];
                        if ($eventCategory->include_subcategories) {
                            // Get all descendant categories
                            $descendants = Category::descendantsOf($eventCategory->category_id)->pluck('id')->toArray();
                            $categoryIds = array_merge($categoryIds, $descendants);
                        }
                        $productIds = $productIds->merge(
                            Product::whereIn('category_id', $categoryIds)
                                ->ProductPublished()
                                ->pluck('id')
                        );
                    }
                } catch (\Exception $e) {
                    // Log error but continue
                }
            }

            // Brand-based products
            if ($campaignType == 'brand') {
                try {
                    foreach ($this->eventBrands as $eventBrand) {
                        $productIds = $productIds->merge(
                            Product::whereIn('brand_id', $this->brands->pluck('id'))
                                ->ProductPublished()
                                ->pluck('id')
                        );
                    }
                } catch (\Exception $e) {
                    // Log error but continue
                }
            }
        }

        return Product::whereIn('id', $productIds->unique())
            ->with(['eventProducts' => function ($q) {
                $q->where('event_id', $this->id);
            }]);
    }
}