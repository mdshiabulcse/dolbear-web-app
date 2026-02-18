@extends('admin.partials.master')
@section('title')
    {{ __('Campaigns & Events') }}
@endsection
@section('marketing_active')
    active
@endsection
@section('events')
    active
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Campaigns & Events') }}</h2>
                    <p class="section-lead">
                        {{ __('Manage your campaigns and events. Only ONE campaign can be active at a time.') }}
                    </p>
                </div>
                <div class="buttons add-button">
                    <a href="{{ route('events.create') }}" class="btn btn-icon icon-left btn-outline-primary">
                        <i class="bx bx-plus"></i>{{ __('Add Campaign') }}</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-xs-12 col-md-12">
                <div class="card">
                    <form action="">
                        <div class="card-header input-title">
                            <h4>{{ __('Campaigns List') }}</h4>
                        </div>
                    </form>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-md">
                                <tbody>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Campaign Name') }}</th>
                                    <th>{{ __('Slug') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Start Date & Time') }}</th>
                                    <th>{{ __('End Date & Time') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Products') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                                @foreach ($events as $key => $event)
                                    @php
                                        // Determine campaign status
                                        $now = \Carbon\Carbon::now();
                                        $statusBadge = 'secondary';
                                        $statusText = __('Draft');

                                        if ($event->status == 'active' && $event->is_active) {
                                            if ($event->is_active_now) {
                                                $statusBadge = 'success';
                                                $statusText = __('Active');
                                            } elseif ($event->event_schedule_start && $now->lt(\Carbon\Carbon::parse($event->event_schedule_start))) {
                                                $statusBadge = 'info';
                                                $statusText = __('Upcoming');
                                            }
                                        } elseif ($event->status == 'expired') {
                                            $statusBadge = 'dark';
                                            $statusText = __('Expired');
                                        } elseif ($event->status == 'paused') {
                                            $statusBadge = 'warning';
                                            $statusText = __('Paused');
                                        }

                                        // Campaign type label
                                        $campaignTypeLabel = match($event->campaign_type ?? 'product') {
                                            'product' => __('Product-based'),
                                            'category' => __('Category-based'),
                                            'brand' => __('Brand-based'),
                                            'event' => __('Event-based'),
                                            default => __('Product-based')
                                        };
                                    @endphp

                                    <tr id="row_{{ $event->id }}" class="table-data-row">
                                        <td>{{ $events->firstItem() + $key }}</td>
                                        <td>
                                            <a href="{{ isAppMode() ? '#' : route('events.show', $event->id) }}" target="{{isAppMode() ? '_parent' : '_blank'}}">
                                                <strong>{{ $event->event_title }}</strong>
                                            </a>
                                            @if($event->campaign_type == 'category' || $event->campaign_type == 'brand')
                                                <br>
                                                <small class="text-muted">{{ $campaignTypeLabel }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <code>{{ $event->slug }}</code>
                                            <br>
                                            <small class="text-muted">
                                                <a href="{{ url('campaign/' . $event->slug) }}" target="_blank">
                                                    <i class="bx bx-link-external"></i> {{ __('View') }}
                                                </a>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-light">{{ $campaignTypeLabel }}</span>
                                        </td>
                                        <td>{{ $event->event_schedule_start ? date('M d, Y H:i', strtotime($event->event_schedule_start)) : '-' }}</td>
                                        <td>{{ $event->event_schedule_end ? date('M d, Y H:i', strtotime($event->event_schedule_end)) : '-' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $statusBadge }}">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $event->total_products }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <!-- View -->
                                                <a href="{{ route('events.show', $event->id) }}"
                                                   class="btn btn-sm btn-info"
                                                   title="{{ __('View') }}">
                                                    <i class="bx bx-show"></i>
                                                </a>

                                                <!-- Edit -->
                                                <a href="{{ route('events.edit', $event->id) }}"
                                                   class="btn btn-sm btn-primary"
                                                   title="{{ __('Edit') }}">
                                                    <i class="bx bx-edit"></i>
                                                </a>

                                                <!-- Enable/Disable (Status Toggle) -->
                                                @if($event->status == 'active' && $event->is_active)
                                                    <button type="button"
                                                            class="btn btn-sm btn-warning status-change-btn"
                                                            data-action="{{ route('events.status.change') }}"
                                                            data-id="{{ $event->id }}"
                                                            title="{{ __('Disable') }}">
                                                        <i class="bx bx-pause"></i>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-sm btn-success status-change-btn"
                                                            data-action="{{ route('events.status.change') }}"
                                                            data-id="{{ $event->id }}"
                                                            title="{{ __('Enable') }}">
                                                        <i class="bx bx-play"></i>
                                                    </button>
                                                @endif

                                                <!-- Delete -->
                                                <button type="button"
                                                        class="btn btn-sm btn-danger delete-ajax-btn"
                                                        data-action="{{ route('events.delete', $event->id) }}"
                                                        data-id="{{ $event->id }}"
                                                        data-label="#row_{{ $event->id }}"
                                                        title="{{ __('Delete') }}">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(method_exists($events, 'links'))
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        @endif
    </section>
@endsection
@push('scripts')
    <script>
        $(document).ready(function () {
            // Status change for button-based toggle
            $(document).on('click', '.status-change-btn', function(e) {
                e.preventDefault();
                let btn = $(this);
                let id = btn.data('id');
                let url = btn.data('action');

                // Check if this is an activate action (enable button)
                let isActivate = btn.hasClass('btn-success');

                // Show confirmation if activating (since it will deactivate other campaigns)
                if (isActivate) {
                    if (!confirm('{{ __("Only ONE campaign can be active at a time. This will deactivate any currently active campaign. Continue?") }}')) {
                        return false;
                    }
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        data: {
                            id: id,
                            status: isActivate ? 'active' : 'paused'
                        }
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON?.message || '{{ __("An error occurred") }}';
                        toastr.error(message);
                    }
                });
            });

            // Delete button handler
            $(document).on('click', '.delete-ajax-btn', function(e) {
                e.preventDefault();
                let btn = $(this);
                let action = btn.data('action');
                let id = btn.data('id');
                let label = btn.data('label');

                if (confirm('{{ __("Are you sure to delete?") }}')) {
                    $.ajax({
                        url: action,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                toastr.success(response.message);
                                if (label) {
                                    $(label).fadeOut();
                                }
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(xhr) {
                            let message = xhr.responseJSON?.message || '{{ __("An error occurred") }}';
                            toastr.error(message);
                        }
                    });
                }
            });
        });
    </script>
@endpush