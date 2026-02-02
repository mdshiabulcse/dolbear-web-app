@extends('admin.partials.master')

@section('title', __('Payment Logs'))

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="pull-left">
                <h2 class="page-title">{{ __('Payment Logs') }}</h2>
                <nav class="breadcrumb" itemprop="breadcrumb">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a> &nbsp;/&nbsp; {{ __('Payment Logs') }}
                </nav>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card card-info-stats">
                <div class="card-body">
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
                    <p>{{ __('Total Logs') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-info-stats">
                <div class="card-body">
                    <h3>{{ $stats['today'] ?? 0 }}</h3>
                    <p>{{ __("Today's Logs") }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-info-stats">
                <div class="card-body">
                    <h3>{{ $stats['completed'] ?? 0 }}</h3>
                    <p>{{ __('Completed') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-info-stats">
                <div class="card-body">
                    <h3>{{ $stats['failed'] ?? 0 }}</h3>
                    <p>{{ __('Failed') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title">{{ __('Filters') }}</h4>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="mdi mdi-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('payment.logs.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('Gateway') }}</label>
                            <select name="gateway" class="form-control select2">
                                <option value="">{{ __('All Gateways') }}</option>
                                <option value="sslcommerz" {{ request('gateway') == 'sslcommerz' ? 'selected' : '' }}>SSLCOMMERZ</option>
                                <option value="paypal" {{ request('gateway') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                                <option value="stripe" {{ request('gateway') == 'stripe' ? 'selected' : '' }}>Stripe</option>
                                <option value="bkash" {{ request('gateway') == 'bkash' ? 'selected' : '' }}>bKash</option>
                                <option value="nagad" {{ request('gateway') == 'nagad' ? 'selected' : '' }}>Nagad</option>
                                <option value="rocket" {{ request('gateway') == 'rocket' ? 'selected' : '' }}>Rocket</option>
                                <option value="kkiapay" {{ request('gateway') == 'kkiapay' ? 'selected' : '' }}>Kkiapay</option>
                                <option value="other" {{ request('gateway') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('Event Type') }}</label>
                            <select name="event_type" class="form-control select2">
                                <option value="">{{ __('All Events') }}</option>
                                <option value="initiation" {{ request('event_type') == 'initiation' ? 'selected' : '' }}>{{ __('Payment Initiated') }}</option>
                                <option value="success_callback" {{ request('event_type') == 'success_callback' ? 'selected' : '' }}>{{ __('Success Callback') }}</option>
                                <option value="fail_callback" {{ request('event_type') == 'fail_callback' ? 'selected' : '' }}>{{ __('Failed Callback') }}</option>
                                <option value="cancel_callback" {{ request('event_type') == 'cancel_callback' ? 'selected' : '' }}>{{ __('Cancelled by User') }}</option>
                                <option value="ipn_received" {{ request('event_type') == 'ipn_received' ? 'selected' : '' }}>{{ __('IPN Received') }}</option>
                                <option value="ipn_processed" {{ request('event_type') == 'ipn_processed' ? 'selected' : '' }}>{{ __('IPN Processed') }}</option>
                                <option value="validation_success" {{ request('event_type') == 'validation_success' ? 'selected' : '' }}>{{ __('Validation Successful') }}</option>
                                <option value="validation_failed" {{ request('event_type') == 'validation_failed' ? 'selected' : '' }}>{{ __('Validation Failed') }}</option>
                                <option value="order_completed" {{ request('event_type') == 'order_completed' ? 'selected' : '' }}>{{ __('Order Completed') }}</option>
                                <option value="order_failed" {{ request('event_type') == 'order_failed' ? 'selected' : '' }}>{{ __('Order Failed') }}</option>
                                <option value="error" {{ request('event_type') == 'error' ? 'selected' : '' }}>{{ __('Error Occurred') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('Payment Status') }}</label>
                            <select name="payment_status" class="form-control select2">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="processing" {{ request('payment_status') == 'processing' ? 'selected' : '' }}>{{ __('Processing') }}</option>
                                <option value="completed" {{ request('payment_status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                                <option value="cancelled" {{ request('payment_status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                                <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>{{ __('Refunded') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('TRX ID') }}</label>
                            <input type="text" name="trx_id" class="form-control" value="{{ request('trx_id') }}" placeholder="{{ __('Search by TRX ID') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('Order Code') }}</label>
                            <input type="text" name="order_code" class="form-control" value="{{ request('order_code') }}" placeholder="{{ __('Search by Order Code') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('Date From') }}</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('Date To') }}</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                                <a href="{{ route('payment.logs.index') }}" class="btn btn-secondary">{{ __('Reset') }}</a>
                                <a href="{{ route('payment.logs.export', request()->query()) }}" class="btn btn-success">
                                    <i class="mdi mdi-download"></i> {{ __('Export CSV') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Payment Logs Table --}}
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ __('Payment Logs List') }}</h4>
        </div>
        <div class="card-body">
            @if($paymentLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Gateway') }}</th>
                                <th>{{ __('Event Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Order Code') }}</th>
                                <th>{{ __('TRX ID') }}</th>
                                <th>{{ __('Gateway TRX ID') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Environment') }}</th>
                                <th>{{ __('Created At') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paymentLogs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{!! $log->gateway_badge !!}</td>
                                <td>{{ $log->event_type_label }}</td>
                                <td>{!! $log->status_badge !!}</td>
                                <td>
                                    @if($log->order_code)
                                        <a href="{{ route('payment.logs.order', $log->order_code) }}">
                                            {{ $log->order_code }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($log->trx_id)
                                        <a href="{{ route('payment.logs.transaction', $log->trx_id) }}" title="{{ __('View all logs for this transaction') }}">
                                            {{ $log->trx_id }}
                                            <i class="mdi mdi-eye" style="font-size: 12px;"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $log->gateway_tran_id ?? '-' }}</td>
                                <td>
                                    @if($log->amount)
                                        {{ format_price($log->amount) }}
                                        @if($log->currency)
                                            <small>({{ $log->currency }})</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $log->environment == 'production' ? 'danger' : 'warning' }}">
                                        {{ $log->environment }}
                                    </span>
                                </td>
                                <td>{{ $log->created_at }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('payment.logs.show', $log->id) }}" class="btn btn-info" title="{{ __('View Details') }}">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                        @if($log->order_id)
                                            <a href="{{ route('order.view', $log->order_id) }}" class="btn btn-primary" title="{{ __('View Order') }}">
                                                <i class="mdi mdi-cart"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        {{ $paymentLogs->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="mdi mdi-database-search" style="font-size: 48px; color: #ccc;"></i>
                    <p class="mt-3">{{ __('No payment logs found.') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card-info-stats {
    border-left: 4px solid #007bff;
}
.card-info-stats .card-body h3 {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 5px;
}
.card-info-stats .card-body p {
    margin: 0;
    color: #6c757d;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%'
    });
});
</script>
@endpush