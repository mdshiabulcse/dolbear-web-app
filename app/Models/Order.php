<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Jobs\OrderSyncJob;

class Order extends Model
{
    use HasFactory;

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'offline_method_file' => 'array',
        'payment_details' => 'array',
        'tax_method' => 'array',
    ];
    protected $appends = ['delivered_days', 'total_order_quantity'];

    protected $attributes = [
        'shipping_address' => '[]',
        'billing_address' => '[]',
        'payment_details' => '[]',
    ];

    protected $fillable = [
        'erp_code',
        'seller_id',
        'user_id',
        'billing_address',
        'shipping_address',
        'payment_type',
        'sub_total',
        'discount',
        'coupon_codes',
        'coupon_discount',
        'total_tax',
        'total_amount',
        'delivery_status',
        'shipping_cost',
        'billing_address',
        'total_payable',
        'status',
        'code',
        'is_draft',
        'trx_id',
        'date',
        'pickup_hub_id',
        'is_refundable',
        'created_by',
        'payment_details',
        'offline_method_id',
        'offline_method_file',
        'trx_id',
        'gateway_tran_id',
        'shipping_method',
        'is_coupon_system_active',
        'tax_method',
        'payment_status',
        'delivery_method',
        'store_id',
        'erp_sync'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $lastOrder = Order::orderBy('id', 'desc')->first();

            if ($lastOrder) {
                // Check if the code starts with #
                $lastCode = strpos($lastOrder->code, '#') === 0
                    ? (int) substr($lastOrder->code, 1)  // Remove # and convert to int
                    : (int) $lastOrder->code;  // Convert directly if no #

                $newCode = max($lastCode + 1, 30000); // Ensure the minimum code is 30000
            } else {
                $newCode = 30000; // Start from 30000 if no previous order exists
            }

            $order->code = '#' . $newCode;
        });

        static::updated(function ($order) {
            info('order updated');
            if ($order->wasChanged('delivery_status') && $order->delivery_status == 'confirm' && !$order->erp_sync) {
                info('order dispatch');
                OrderSyncJob::dispatch($order);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function deliveryHero(): BelongsTo
    {
        return $this->belongsTo(DeliveryHero::class);
    }

    public function getOrderDateAttribute(): string
    {
        return Carbon::parse($this->updated_at)->format('d M Y');
    }

    public function deliveryHistories()
    {
        return $this->hasMany(DeliveryHistory::class)->latest();
    }
    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class)->latest();
    }

    public function pickupHub()
    {
        return $this->belongsTo(PickupHub::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function processedRefunds()
    {
        return $this->hasMany(Refund::class)->where('status', 'processed');
    }

    public function totalRefunded()
    {
        return $this->hasMany(Refund::class)->where('status', 'processed');
    }

    public function deliveredAt()
    {
        return $this->hasOne(DeliveryHistory::class)->where('event', 'order_delivered_event')->latest();
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getDeliveredDaysAttribute()
    {
        $delivered = $this->deliveredAt;

        if ($delivered):
            return Carbon::parse($delivered->created_at)->diffInDays();
        else:
            return null;
        endif;
    }

    public function getTotalOrderQuantityAttribute()
    {
        return $this->orderDetails()->sum('quantity');
    }
}
