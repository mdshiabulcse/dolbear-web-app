<?php

namespace App\Services;

use App\Models\PaymentLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;

class PaymentLogService
{
    /**
     * Log a payment event.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function log(array $data)
    {
        // Add IP and user agent if not provided
        if (!isset($data['ip_address'])) {
            $data['ip_address'] = Request::ip();
        }
        if (!isset($data['user_agent'])) {
            $data['user_agent'] = Request::userAgent();
        }

        return PaymentLog::create($data);
    }

    /**
     * Log payment initiation.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function logInitiation(array $data)
    {
        return $this->log(array_merge($data, [
            'event_type' => 'initiation',
            'payment_status' => 'pending',
            'initiated_at' => now(),
        ]));
    }

    /**
     * Log redirect to gateway.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function logRedirect(array $data)
    {
        return $this->log(array_merge($data, [
            'event_type' => 'redirect',
            'payment_status' => 'processing',
        ]));
    }

    /**
     * Log success callback.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function logSuccessCallback(array $data)
    {
        return $this->log(array_merge($data, [
            'event_type' => 'success_callback',
            'payment_status' => 'processing',
            'gateway_responded_at' => now(),
        ]));
    }

    /**
     * Log fail callback.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function logFailCallback(array $data)
    {
        return $this->log(array_merge($data, [
            'event_type' => 'fail_callback',
            'payment_status' => 'failed',
            'gateway_responded_at' => now(),
        ]));
    }

    /**
     * Log cancel callback.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function logCancelCallback(array $data)
    {
        return $this->log(array_merge($data, [
            'event_type' => 'cancel_callback',
            'payment_status' => 'cancelled',
            'gateway_responded_at' => now(),
        ]));
    }

    /**
     * Log IPN received.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function logIpnReceived(array $data)
    {
        return $this->log(array_merge($data, [
            'event_type' => 'ipn_received',
            'payment_status' => 'processing',
            'gateway_responded_at' => now(),
        ]));
    }

    /**
     * Log IPN processed.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function logIpnProcessed(array $data)
    {
        return $this->log(array_merge($data, [
            'event_type' => 'ipn_processed',
            'payment_status' => 'completed',
            'completed_at' => now(),
        ]));
    }

    /**
     * Log validation success.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function logValidationSuccess(array $data)
    {
        return $this->log(array_merge($data, [
            'event_type' => 'validation_success',
            'payment_status' => 'processing',
        ]));
    }

    /**
     * Log validation failed.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function logValidationFailed(array $data)
    {
        return $this->log(array_merge($data, [
            'event_type' => 'validation_failed',
            'payment_status' => 'failed',
        ]));
    }

    /**
     * Log order completed.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function logOrderCompleted(array $data)
    {
        return $this->log(array_merge($data, [
            'event_type' => 'order_completed',
            'payment_status' => 'completed',
            'completed_at' => now(),
        ]));
    }

    /**
     * Log order failed.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function logOrderFailed(array $data)
    {
        return $this->log(array_merge($data, [
            'event_type' => 'order_failed',
            'payment_status' => 'failed',
        ]));
    }

    /**
     * Log duplicate prevented.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function logDuplicatePrevented(array $data)
    {
        return $this->log(array_merge($data, [
            'event_type' => 'duplicate_prevented',
            'payment_status' => 'completed',
        ]));
    }

    /**
     * Log error.
     *
     * @param array $data
     * @return PaymentLog
     */
    public function logError(array $data)
    {
        return $this->log(array_merge($data, [
            'event_type' => 'error',
            'payment_status' => 'failed',
        ]));
    }

    /**
     * Get all logs for a transaction.
     *
     * @param string $trxId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLogsByTrxId($trxId)
    {
        return PaymentLog::byTrxId($trxId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get all logs for a gateway transaction.
     *
     * @param string $gatewayTranId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLogsByGatewayTranId($gatewayTranId)
    {
        return PaymentLog::byGatewayTranId($gatewayTranId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get all logs for an order.
     *
     * @param int $orderId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLogsByOrderId($orderId)
    {
        return PaymentLog::where('order_id', $orderId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get payment statistics for a date range.
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @param string|null $gateway
     * @return array
     */
    public function getStatistics($startDate, $endDate, $gateway = null)
    {
        $query = PaymentLog::whereBetween('created_at', [$startDate, $endDate]);

        if ($gateway) {
            $query->byGateway($gateway);
        }

        $logs = $query->get();

        return [
            'total' => $logs->count(),
            'completed' => $logs->where('payment_status', 'completed')->count(),
            'failed' => $logs->where('payment_status', 'failed')->count(),
            'pending' => $logs->where('payment_status', 'pending')->count(),
            'cancelled' => $logs->where('payment_status', 'cancelled')->count(),
            'total_amount' => $logs->where('payment_status', 'completed')->sum('amount'),
        ];
    }

    /**
     * Check if transaction is already completed.
     *
     * @param string $gatewayTranId
     * @return bool
     */
    public function isTransactionCompleted($gatewayTranId)
    {
        return PaymentLog::byGatewayTranId($gatewayTranId)
            ->byStatus('completed')
            ->exists();
    }

    /**
     * Clean up old logs (optional for maintenance).
     *
     * @param int $days
     * @return int
     */
    public function cleanOldLogs($days = 90)
    {
        return PaymentLog::where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}