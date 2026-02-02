<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentLog extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'trx_id',
        'gateway_tran_id',
        'order_code',
        'gateway',
        'event_type',
        'payment_status',
        'amount',
        'currency',
        'val_id',
        'card_type',
        'bank_tran_id',
        'status',
        'request_data',
        'response_data',
        'validation_data',
        'error_message',
        'error_code',
        'ip_address',
        'user_agent',
        'environment',
        'initiated_at',
        'gateway_responded_at',
        'completed_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'validation_data' => 'array',
        'amount' => 'decimal:2',
        'initiated_at' => 'datetime',
        'gateway_responded_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the order that owns the payment log.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope a query to only include logs for a specific gateway.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $gateway
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGateway($query, $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope a query to only include logs for a specific event type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $eventType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope a query to only include logs with a specific payment status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope a query to only include logs for a specific transaction.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $trxId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTrxId($query, $trxId)
    {
        return $query->where('trx_id', $trxId);
    }

    /**
     * Scope a query to only include logs for a specific gateway transaction.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $gatewayTranId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGatewayTranId($query, $gatewayTranId)
    {
        return $query->where('gateway_tran_id', $gatewayTranId);
    }

    /**
     * Scope a query to only include logs for a specific order code.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $orderCode
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByOrderCode($query, $orderCode)
    {
        return $query->where('order_code', $orderCode);
    }

    /**
     * Scope a query to only include logs from today.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope a query to only include failed payments.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    /**
     * Scope a query to only include completed payments.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Scope a query to only include pending payments.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Get human-readable event type label.
     *
     * @return string
     */
    public function getEventTypeLabelAttribute()
    {
        return [
            'initiation' => 'Payment Initiated',
            'redirect' => 'Redirected to Gateway',
            'success_callback' => 'Success Callback',
            'fail_callback' => 'Failed Callback',
            'cancel_callback' => 'Cancelled by User',
            'ipn_received' => 'IPN Received',
            'ipn_processed' => 'IPN Processed',
            'validation_success' => 'Validation Successful',
            'validation_failed' => 'Validation Failed',
            'order_completed' => 'Order Completed',
            'order_failed' => 'Order Failed',
            'duplicate_prevented' => 'Duplicate Prevented',
            'error' => 'Error Occurred',
        ][$this->event_type] ?? $this->event_type;
    }

    /**
     * Get human-readable gateway label.
     *
     * @return string
     */
    public function getGatewayLabelAttribute()
    {
        return [
            'sslcommerz' => 'SSLCOMMERZ',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'bkash' => 'bKash',
            'nagad' => 'Nagad',
            'rocket' => 'Rocket',
            'kkiapay' => 'Kkiapay',
            'other' => 'Other',
        ][$this->gateway] ?? $this->gateway;
    }

    /**
     * Get human-readable payment status label.
     *
     * @return string
     */
    public function getPaymentStatusLabelAttribute()
    {
        return [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ][$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Get status badge HTML for admin panel.
     *
     * @return string
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'processing' => '<span class="badge badge-info">Processing</span>',
            'completed' => '<span class="badge badge-success">Completed</span>',
            'failed' => '<span class="badge badge-danger">Failed</span>',
            'cancelled' => '<span class="badge badge-secondary">Cancelled</span>',
            'refunded' => '<span class="badge badge-dark">Refunded</span>',
        ];

        return $badges[$this->payment_status] ?? '<span class="badge badge-secondary">' . $this->payment_status . '</span>';
    }

    /**
     * Get gateway badge HTML for admin panel.
     *
     * @return string
     */
    public function getGatewayBadgeAttribute()
    {
        $badges = [
            'sslcommerz' => '<span class="badge badge-primary">SSLCOMMERZ</span>',
            'paypal' => '<span class="badge badge-info">PayPal</span>',
            'stripe' => '<span class="badge badge-purple">Stripe</span>',
            'bkash' => '<span class="badge badge-pink">bKash</span>',
            'nagad' => '<span class="badge badge-orange">Nagad</span>',
            'rocket' => '<span class="badge badge-purple">Rocket</span>',
            'kkiapay' => '<span class="badge badge-teal">Kkiapay</span>',
        ];

        return $badges[$this->gateway] ?? '<span class="badge badge-secondary">' . $this->gateway . '</span>';
    }

    /**
     * Check if payment is completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->payment_status === 'completed';
    }

    /**
     * Check if payment is failed.
     *
     * @return bool
     */
    public function isFailed()
    {
        return $this->payment_status === 'failed';
    }

    /**
     * Check if payment is pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->payment_status === 'pending';
    }
}