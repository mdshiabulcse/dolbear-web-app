<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = [
        'image_1920x412',
        'image_406x235',
        'image_374x374',
        'event_start_date',
        'event_end_date',
        'is_active_now',
        'is_expired',
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
     * Attribute: Get banner image 1920x412
     */
    public function getImage1920x412Attribute()
    {
        // Use original_image since 1920x412 doesn't exist in media variants
        return @is_file_exists(@$this->banner_image['original_image'], @$this->banner_image['storage'])
            ? @get_media(@$this->banner_image['original_image'], @$this->banner_image['storage'])
            : static_asset('images/default/default-image-1280x420.png');
    }

    /**
     * Attribute: Get banner image 406x235
     */
    public function getImage406x235Attribute()
    {
        // Use original_image since 406x235 doesn't exist in media variants
        return @is_file_exists(@$this->banner_image['original_image'], @$this->banner_image['storage'])
            ? @get_media(@$this->banner_image['original_image'], @$this->banner_image['storage'])
            : static_asset('images/default/default-image-400x235.png');
    }

    /**
     * Attribute: Get banner image 374x374
     */
    public function getImage374x374Attribute()
    {
        // Use image_72x72 or original_image since 374x374 doesn't exist in media variants
        return @is_file_exists(@$this->banner_image['image_72x72'], @$this->banner_image['storage'])
            ? @get_media(@$this->banner_image['image_72x72'], @$this->banner_image['storage'])
            : static_asset('images/default/default-image-72x72.png');
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
     */
    public function getIsActiveNowAttribute(): bool
    {
        if ($this->status != 'active' || !$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->event_type == 'daily') {
            // For daily events, check time range
            if ($this->daily_start_time && $this->daily_end_time) {
                $currentTime = $now->format('H:i:s');
                return $currentTime >= $this->daily_start_time && $currentTime <= $this->daily_end_time;
            }
            return true;
        } elseif ($this->event_type == 'date_range') {
            // For date range events, check if current time is within range
            return $now >= $this->event_schedule_start && $now <= $this->event_schedule_end;
        }

        return false;
    }

    /**
     * Attribute: Check if event is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        $now = Carbon::now();
        if ($this->event_type == 'date_range') {
            return $now > $this->event_schedule_end;
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
        // Check if product already exists in event
        $existingProduct = $this->eventProducts()->where('product_id', $productId)->first();

        if ($existingProduct) {
            // Update existing product
            $existingProduct->update([
                'event_price' => $eventPrice,
                'discount_amount' => $discountAmount,
                'discount_type' => $discountType,
                'product_priority' => $priority,
                'event_stock' => $eventStock,
                'is_active' => 1,
                'status' => 'active',
            ]);
            return $existingProduct;
        }

        // Create new event product
        return $this->eventProducts()->create([
            'product_id' => $productId,
            'event_price' => $eventPrice,
            'discount_amount' => $discountAmount,
            'discount_type' => $discountType,
            'product_priority' => $priority,
            'event_stock' => $eventStock,
            'event_stock_sold' => 0,
            'is_active' => 1,
            'status' => 'active',
            'created_by' => authId() ?? 1,
        ]);
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
}