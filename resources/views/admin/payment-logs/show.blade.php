@extends('admin.partials.master')

@section('title', __('Payment Log Details'))

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="pull-left">
                <h2 class="page-title">{{ __('Payment Log Details') }}</h2>
                <nav class="breadcrumb" itemprop="breadcrumb">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a> &nbsp;/&nbsp;
                    <a href="{{ route('payment.logs.index') }}">{{ __('Payment Logs') }}</a> &nbsp;/&nbsp;
                    {{ __('Details') }}
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Log Information') }} #{{ $paymentLog->id }}</h4>
                    <div class="card-tools">
                        <a href="{{ route('payment.logs.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> {{ __('Back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Basic Information --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">{{ __('Basic Information') }}</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="30%">{{ __('ID') }}</th>
                                    <td>{{ $paymentLog->id }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Gateway') }}</th>
                                    <td>{!! $paymentLog->gateway_badge !!}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Event Type') }}</th>
                                    <td>{{ $paymentLog->event_type_label }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Payment Status') }}</th>
                                    <td>{!! $paymentLog->status_badge !!}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Environment') }}</th>
                                    <td>
                                        <span class="badge badge-{{ $paymentLog->environment == 'production' ? 'danger' : 'warning' }}">
                                            {{ $paymentLog->environment }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('Created At') }}</th>
                                    <td>{{ $paymentLog->created_at }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">{{ __('Transaction Details') }}</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="30%">{{ __('Order ID') }}</th>
                                    <td>
                                        @if($paymentLog->order_id)
                                            <a href="{{ route('order.view', $paymentLog->order_id) }}">
                                                #{{ $paymentLog->order_id }}
                                                <i class="mdi mdi-open-in-new" style="font-size: 12px;"></i>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('Order Code') }}</th>
                                    <td>{{ $paymentLog->order_code ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('TRX ID') }}</th>
                                    <td>
                                        @if($paymentLog->trx_id)
                                            <a href="{{ route('payment.logs.transaction', $paymentLog->trx_id) }}">
                                                {{ $paymentLog->trx_id }}
                                                <i class="mdi mdi-eye" style="font-size: 12px;"></i>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('Gateway TRX ID') }}</th>
                                    <td>{{ $paymentLog->gateway_tran_id ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Amount') }}</th>
                                    <td>
                                        @if($paymentLog->amount)
                                            {{ format_price($paymentLog->amount) }}
                                            @if($paymentLog->currency)
                                                <small>({{ $paymentLog->currency }})</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Gateway Response Details --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">{{ __('Gateway Response') }}</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="30%">{{ __('Validation ID') }}</th>
                                    <td>{{ $paymentLog->val_id ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Card Type') }}</th>
                                    <td>{{ $paymentLog->card_type ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Bank TRX ID') }}</th>
                                    <td>{{ $paymentLog->bank_tran_id ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Status') }}</th>
                                    <td>{{ $paymentLog->status ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">{{ __('HTTP Details') }}</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="30%">{{ __('IP Address') }}</th>
                                    <td>{{ $paymentLog->ip_address ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('User Agent') }}</th>
                                    <td class="text-truncate" style="max-width: 300px;" title="{{ $paymentLog->user_agent ?? '-' }}">
                                        {{ \Illuminate\Support\Str::limit($paymentLog->user_agent ?? '-', 50) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Error Information --}}
                    @if($paymentLog->error_message || $paymentLog->error_code)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">{{ __('Error Information') }}</h5>
                            <table class="table table-sm">
                                @if($paymentLog->error_code)
                                <tr>
                                    <th width="30%">{{ __('Error Code') }}</th>
                                    <td><code>{{ $paymentLog->error_code }}</code></td>
                                </tr>
                                @endif
                                @if($paymentLog->error_message)
                                <tr>
                                    <th width="30%">{{ __('Error Message') }}</th>
                                    <td class="text-danger">{{ $paymentLog->error_message }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Timing Information --}}
                    @if($paymentLog->initiated_at || $paymentLog->gateway_responded_at || $paymentLog->completed_at)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">{{ __('Timing Information') }}</h5>
                            <table class="table table-sm">
                                @if($paymentLog->initiated_at)
                                <tr>
                                    <th width="30%">{{ __('Initiated At') }}</th>
                                    <td>{{ $paymentLog->initiated_at }}</td>
                                </tr>
                                @endif
                                @if($paymentLog->gateway_responded_at)
                                <tr>
                                    <th width="30%">{{ __('Gateway Responded At') }}</th>
                                    <td>{{ $paymentLog->gateway_responded_at }}</td>
                                </tr>
                                @endif
                                @if($paymentLog->completed_at)
                                <tr>
                                    <th width="30%">{{ __('Completed At') }}</th>
                                    <td>{{ $paymentLog->completed_at }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Notes --}}
                    @if($paymentLog->notes)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">{{ __('Notes') }}</h5>
                            <p>{{ $paymentLog->notes }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- JSON Data --}}
                    @if($paymentLog->request_data || $paymentLog->response_data || $paymentLog->validation_data)
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">{{ __('Data Details') }}</h5>

                            <ul class="nav nav-tabs" id="jsonDataTabs" role="tablist">
                                @if($paymentLog->request_data)
                                <li class="nav-item">
                                    <a class="nav-link active" id="request-tab" data-toggle="tab" href="#request" role="tab">
                                        {{ __('Request Data') }}
                                    </a>
                                </li>
                                @endif
                                @if($paymentLog->response_data)
                                <li class="nav-item">
                                    <a class="nav-link {{ !$paymentLog->request_data ? 'active' : '' }}" id="response-tab" data-toggle="tab" href="#response" role="tab">
                                        {{ __('Response Data') }}
                                    </a>
                                </li>
                                @endif
                                @if($paymentLog->validation_data)
                                <li class="nav-item">
                                    <a class="nav-link {{ (!$paymentLog->request_data && !$paymentLog->response_data) ? 'active' : '' }}" id="validation-tab" data-toggle="tab" href="#validation" role="tab">
                                        {{ __('Validation Data') }}
                                    </a>
                                </li>
                                @endif
                            </ul>

                            <div class="tab-content pt-3" id="jsonDataTabContent">
                                @if($paymentLog->request_data)
                                <div class="tab-pane fade show active" id="request" role="tabpanel">
                                    <pre class="bg-light p-3">{{ json_encode($paymentLog->request_data, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                                @endif
                                @if($paymentLog->response_data)
                                <div class="tab-pane fade {{ !$paymentLog->request_data ? 'show active' : '' }}" id="response" role="tabpanel">
                                    <pre class="bg-light p-3">{{ json_encode($paymentLog->response_data, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                                @endif
                                @if($paymentLog->validation_data)
                                <div class="tab-pane fade {{ (!$paymentLog->request_data && !$paymentLog->response_data) ? 'show active' : '' }}" id="validation" role="tabpanel">
                                    <pre class="bg-light p-3">{{ json_encode($paymentLog->validation_data, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection