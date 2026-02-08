@extends('admin.partials.master')
@section('title')
    {{ __('Events') }}
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
                    <h2 class="section-title">{{ __('All Events') }}</h2>
                    <p class="section-lead">
                        {{ __('You have total') . ' ' . $events->total() . ' ' . __('events') }}
                    </p>
                </div>
                <div class="buttons add-button">
                    <a href="{{ route('events.create') }}" class="btn btn-icon icon-left btn-outline-primary">
                        <i class="bx bx-plus"></i>{{ __('Add Event') }}</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-xs-12 col-md-12">
                <div class="card">
                    <form action="">
                        <div class="card-header input-title">
                            <h4>{{ __('Events') }}</h4>
                        </div>
                    </form>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-md">
                                <tbody>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Banner') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Products') }}</th>
                                    <th>{{ __('Options') }}</th>
                                </tr>
                                @foreach ($events as $key => $event)
                                    <tr id="row_{{ $event->id }}" class="table-data-row">
                                        <td>{{ $events->firstItem() + $key }}</td>
                                        <td>
                                            <a href="{{ isAppMode() ? '#' : route('events.show', $event->id) }}" target="{{isAppMode() ? '_parent' : '_blank'}}">
                                                {{ $event->event_title }}
                                            </a>
                                        </td>
                                        <td>
                                            <figure class="">
                                                @if (!empty($event->banner_image) && isset($event->banner_image['image_40x40']) && isset($event->banner_image['storage']) && is_file_exists($event->banner_image['image_40x40'], $event->banner_image['storage']))
                                                    <img src="{{ get_media($event->banner_image['image_40x40'], $event->banner_image['storage']) }}"
                                                         alt="{{ $event->event_title }}" style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <img src="{{ static_asset('images/default/default-image-40x40.png') }}"
                                                         alt="{{ $event->event_title }}" style="width: 40px; height: 40px; object-fit: cover;">
                                                @endif
                                            </figure>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $event->event_type == 'daily' ? 'info' : ($event->event_type == 'date_range' ? 'primary' : 'warning') }}">
                                                {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $event->event_priority <= 5 ? 'success' : ($event->event_priority <= 10 ? 'warning' : 'danger') }}">
                                                {{ $event->event_priority }}
                                            </span>
                                        </td>
                                        <td>{{ $event->event_schedule_start ? date('M d, Y H:i', strtotime($event->event_schedule_start)) : '-' }}</td>
                                        <td>{{ $event->event_schedule_end ? date('M d, Y H:i', strtotime($event->event_schedule_end)) : '-' }}</td>
                                        <td>
                                            <label class="custom-switch mt-2">
                                                <input type="checkbox" name="custom-switch-checkbox"
                                                       value="events-status-change/{{ $event->id }}"
                                                       {{ $event->is_active ? 'checked' : '' }}
                                                       class="status-change custom-switch-input">
                                                <span class="custom-switch-indicator"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $event->total_products }}</span>
                                        </td>
                                        <td>
                                            <div class="dropdown d-inline">
                                                <button class="btn btn-primary dropdown-toggle" type="button"
                                                        data-toggle="dropdown">
                                                    {{ __('Options') }}
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item"
                                                       href="{{ route('events.edit', $event->id) }}">{{ __('Edit') }}</a>
                                                    <a class="dropdown-item"
                                                       href="{{ route('events.show', $event->id) }}">{{ __('View Products') }}</a>
                                                    <button type="button" class="dropdown-item delete-ajax-btn"
                                                            data-action="{{ route('events.delete', $event->id) }}"
                                                            data-id="{{ $event->id }}"
                                                            data-label="#row_{{ $event->id }}">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </div>
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
            // Initialize status change
            initStatusChange();
        });
    </script>
@endpush