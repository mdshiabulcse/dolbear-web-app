<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentLog;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentLogController extends Controller
{
    /**
     * Display a listing of payment logs.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $query = PaymentLog::query()->with('order');

            // Filter by gateway
            if ($request->has('gateway') && $request->gateway != '') {
                $query->byGateway($request->gateway);
            }

            // Filter by event type
            if ($request->has('event_type') && $request->event_type != '') {
                $query->byEventType($request->event_type);
            }

            // Filter by payment status
            if ($request->has('payment_status') && $request->payment_status != '') {
                $query->byStatus($request->payment_status);
            }

            // Filter by trx_id
            if ($request->has('trx_id') && $request->trx_id != '') {
                $query->byTrxId($request->trx_id);
            }

            // Filter by order code
            if ($request->has('order_code') && $request->order_code != '') {
                $query->byOrderCode($request->order_code);
            }

            // Date range filter
            if ($request->has('date_from') && $request->date_from != '') {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to != '') {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Order by latest first
            $query->orderBy('created_at', 'desc');

            // Paginate
            $paymentLogs = $query->paginate(get_pagination('pagination', 20));

            // Get statistics
            $stats = [
                'total' => PaymentLog::count(),
                'today' => PaymentLog::today()->count(),
                'completed' => PaymentLog::completed()->count(),
                'failed' => PaymentLog::failed()->count(),
                'pending' => PaymentLog::pending()->count(),
                'sslcommerz' => PaymentLog::byGateway('sslcommerz')->count(),
                'other_gateways' => PaymentLog::where('gateway', '!=', 'sslcommerz')->count(),
            ];

            return view('admin.payment-logs.index', compact('paymentLogs', 'stats'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    /**
     * Display the specified payment log.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $paymentLog = PaymentLog::with('order')->findOrFail($id);
            return view('admin.payment-logs.show', compact('paymentLog'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    /**
     * Display payment logs for a specific transaction.
     *
     * @param string $trxId
     * @return \Illuminate\View\View
     */
    public function byTransaction($trxId)
    {
        try {
            $paymentLogs = PaymentLog::byTrxId($trxId)
                ->with('order')
                ->orderBy('created_at', 'asc')
                ->get();

            if ($paymentLogs->isEmpty()) {
                Toastr::error(__('No payment logs found for this transaction.'));
                return back();
            }

            $stats = [
                'total' => $paymentLogs->count(),
                'completed' => $paymentLogs->where('payment_status', 'completed')->count(),
                'failed' => $paymentLogs->where('payment_status', 'failed')->count(),
            ];

            return view('admin.payment-logs.transaction', compact('paymentLogs', 'trxId', 'stats'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    /**
     * Display payment logs for a specific order.
     *
     * @param string $orderCode
     * @return \Illuminate\View\View
     */
    public function byOrder($orderCode)
    {
        try {
            $paymentLogs = PaymentLog::byOrderCode($orderCode)
                ->with('order')
                ->orderBy('created_at', 'asc')
                ->get();

            if ($paymentLogs->isEmpty()) {
                Toastr::error(__('No payment logs found for this order.'));
                return back();
            }

            $stats = [
                'total' => $paymentLogs->count(),
                'completed' => $paymentLogs->where('payment_status', 'completed')->count(),
                'failed' => $paymentLogs->where('payment_status', 'failed')->count(),
            ];

            return view('admin.payment-logs.order', compact('paymentLogs', 'orderCode', 'stats'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    /**
     * Get payment statistics dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        try {
            // Get last 30 days statistics
            $thirtyDaysAgo = now()->subDays(30);

            $stats = [
                'total_all' => PaymentLog::count(),
                'total_30_days' => PaymentLog::where('created_at', '>=', $thirtyDaysAgo)->count(),
                'today' => PaymentLog::today()->count(),
                'this_week' => PaymentLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'this_month' => PaymentLog::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),

                'completed_all' => PaymentLog::completed()->count(),
                'completed_30_days' => PaymentLog::where('created_at', '>=', $thirtyDaysAgo)->completed()->count(),

                'failed_all' => PaymentLog::failed()->count(),
                'failed_30_days' => PaymentLog::where('created_at', '>=', $thirtyDaysAgo)->failed()->count(),

                'pending' => PaymentLog::pending()->count(),

                'by_gateway' => PaymentLog::select('gateway', DB::raw('count(*) as total'))
                    ->groupBy('gateway')
                    ->get()
                    ->pluck('total', 'gateway')
                    ->toArray(),

                'by_event_type' => PaymentLog::select('event_type', DB::raw('count(*) as total'))
                    ->groupBy('event_type')
                    ->get()
                    ->pluck('total', 'event_type')
                    ->toArray(),

                'recent_logs' => PaymentLog::with('order')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(),

                'failed_recently' => PaymentLog::failed()
                    ->with('order')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            // Calculate success rate
            $totalProcessed = $stats['completed_all'] + $stats['failed_all'];
            $stats['success_rate'] = $totalProcessed > 0
                ? round(($stats['completed_all'] / $totalProcessed) * 100, 2)
                : 0;

            // Daily stats for last 7 days
            $dailyStats = PaymentLog::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $stats['daily_last_7_days'] = $dailyStats->pluck('total', 'date')->toArray();

            return view('admin.payment-logs.dashboard', compact('stats'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    /**
     * Delete old payment logs (cleanup).
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cleanup(Request $request)
    {
        try {
            $days = $request->input('days', 90); // Default 90 days

            $deleted = PaymentLog::where('created_at', '<', now()->subDays($days))
                ->where(function ($query) {
                    $query->where('payment_status', 'completed')
                        ->orWhere('payment_status', 'failed')
                        ->orWhere('payment_status', 'cancelled');
                })
                ->delete();

            Toastr::success(__(':count payment logs deleted successfully.', ['count' => $deleted]));
            return back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    /**
     * Export payment logs to CSV.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        try {
            $query = PaymentLog::query()->with('order');

            // Apply same filters as index
            if ($request->has('gateway') && $request->gateway != '') {
                $query->byGateway($request->gateway);
            }
            if ($request->has('event_type') && $request->event_type != '') {
                $query->byEventType($request->event_type);
            }
            if ($request->has('payment_status') && $request->payment_status != '') {
                $query->byStatus($request->payment_status);
            }
            if ($request->has('date_from') && $request->date_from != '') {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to != '') {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $paymentLogs = $query->orderBy('created_at', 'desc')->get();

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="payment-logs-' . now()->format('Y-m-d') . '.csv"',
            ];

            $callback = function () use ($paymentLogs) {
                $file = fopen('php://output', 'w');

                // CSV Header
                fputcsv($file, [
                    'ID',
                    'Order ID',
                    'TRX ID',
                    'Gateway TRX ID',
                    'Order Code',
                    'Gateway',
                    'Event Type',
                    'Payment Status',
                    'Amount',
                    'Currency',
                    'Error Message',
                    'IP Address',
                    'Environment',
                    'Created At'
                ]);

                // CSV Data
                foreach ($paymentLogs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->order_id,
                        $log->trx_id,
                        $log->gateway_tran_id,
                        $log->order_code,
                        $log->gateway,
                        $log->event_type_label,
                        $log->payment_status_label,
                        $log->amount,
                        $log->currency,
                        $log->error_message,
                        $log->ip_address,
                        $log->environment,
                        $log->created_at,
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
}