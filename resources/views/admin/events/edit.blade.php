@extends('admin.partials.master')
@section('title')
    {{ __('Edit Event') }} - {{ $event->event_title }}
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
                    <h2 class="section-title">{{ __('Edit Event') }}</h2>
                    <p class="section-lead">
                        {{ __('Update event: ') . $event->event_title }}
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <form id="event-form" action="{{ route('events.update', $event->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-header input-title">
                            <h4>{{ __('Event Information') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Event Title') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="event_title" class="form-control" required
                                               value="{{ $event->event_title }}"
                                               placeholder="{{ __('Enter event title') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Slug') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="slug" class="form-control" required
                                               value="{{ $event->slug }}"
                                               placeholder="{{ __('Enter event slug') }}"
                                               oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-')">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{ __('Description') }}</label>
                                        <textarea name="description" class="form-control" rows="3"
                                                  placeholder="{{ __('Enter event description') }}">{{ $event->description }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Event Priority') }} <span class="text-danger">*</span></label>
                                        <input type="number" name="event_priority" class="form-control" value="{{ $event->event_priority }}" min="0" required>
                                        <small class="text-muted">{{ __('Lower number = higher priority') }}</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Event Type') }} <span class="text-danger">*</span></label>
                                        <select name="event_type" class="form-control" id="event_type" required>
                                            <option value="date_range" {{ $event->event_type == 'date_range' ? 'selected' : '' }}>{{ __('Date Range') }}</option>
                                            <option value="daily" {{ $event->event_type == 'daily' ? 'selected' : '' }}>{{ __('Daily') }}</option>
                                            <option value="recurring" {{ $event->event_type == 'recurring' ? 'selected' : '' }}>{{ __('Recurring') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Campaign Type') }}</label>
                                        <select name="campaign_type" class="form-control" id="campaign_type">
                                            <option value="product" {{ ($event->campaign_type ?? 'product') == 'product' ? 'selected' : '' }}>{{ __('Product-based') }}</option>
                                            <option value="category" {{ ($event->campaign_type ?? 'product') == 'category' ? 'selected' : '' }}>{{ __('Category-based') }}</option>
                                            <option value="brand" {{ ($event->campaign_type ?? 'product') == 'brand' ? 'selected' : '' }}>{{ __('Brand-based') }}</option>
                                            <option value="event" {{ ($event->campaign_type ?? 'product') == 'event' ? 'selected' : '' }}>{{ __('Event-based (Ramadan, Black Friday, etc.)') }}</option>
                                        </select>
                                        <small class="text-muted">{{ __('Select how products are included in this campaign') }}</small>
                                    </div>
                                </div>
                                <div class="col-md-6" id="start_date_field" {{ $event->event_type != 'date_range' ? 'style="display:none;"' : '' }}>
                                    <div class="form-group">
                                        <label>{{ __('Start Date & Time') }}</label>
                                        <input type="datetime-local" name="event_schedule_start" class="form-control"
                                               value="{{ $event->event_schedule_start ? date('Y-m-d\TH:i', strtotime($event->event_schedule_start)) : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6" id="end_date_field" {{ $event->event_type != 'date_range' ? 'style="display:none;"' : '' }}>
                                    <div class="form-group">
                                        <label>{{ __('End Date & Time') }}</label>
                                        <input type="datetime-local" name="event_schedule_end" class="form-control"
                                               value="{{ $event->event_schedule_end ? date('Y-m-d\TH:i', strtotime($event->event_schedule_end)) : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6" id="daily_start_field" {{ $event->event_type != 'daily' ? 'style="display:none;"' : '' }}>
                                    <div class="form-group">
                                        <label>{{ __('Daily Start Time') }}</label>
                                        <input type="time" name="daily_start_time" class="form-control"
                                               value="{{ $event->daily_start_time }}">
                                    </div>
                                </div>
                                <div class="col-md-6" id="daily_end_field" {{ $event->event_type != 'daily' ? 'style="display:none;"' : '' }}>
                                    <div class="form-group">
                                        <label>{{ __('Daily End Time') }}</label>
                                        <input type="time" name="daily_end_time" class="form-control"
                                               value="{{ $event->daily_end_time }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Background Color') }}</label>
                                        <input type="color" name="background_color" class="form-control"
                                               value="{{ $event->background_color ?: '#ffffff' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Text Color') }}</label>
                                        <input type="color" name="text_color" class="form-control"
                                               value="{{ $event->text_color ?: '#000000' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Banner Image') }}</label>
                                        <div class="input-group gallery-modal" data-for="image"
                                             data-selection="single"
                                             data-target="#galleryModal" data-dismiss="modal">
                                            <input type="hidden" name="banner_image_id" value="{{ $event->banner_image_id }}"
                                                   class="image-selected">
                                            <span class="form-control"><span
                                                    class="counter">{{ $event->banner_image_id ? 1 : 0 }}</span> {{ __('file chosen') }}</span>
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    {{ __('Choose File') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="selected-media-box">
                                            <div class="mt-2 gallery gallery-md d-flex">
                                                <div class="selected-media mr-2 mb-2 mt-3 ml-0"
                                                     data-id="{{ $event->banner_image_id }}">
                                                    @if (!empty($event->banner_image) && isset($event->banner_image['image_72x72']) && isset($event->banner_image['storage']) && is_file_exists($event->banner_image['image_72x72'], $event->banner_image['storage']))
                                                        <img src="{{ get_media($event->banner_image['image_72x72'], $event->banner_image['storage']) }}"
                                                             alt=""
                                                             class="img-thumbnail">
                                                        <div class="image-remove">
                                                            <a href="javascript:void(0)" class="remove"><i class="bx bx-x"></i></a>
                                                        </div>
                                                    @else
                                                        <img src="{{ static_asset('images/default/default-image-72x72.png') }}"
                                                             data-default="{{ static_asset('images/default/default-image-72x72.png') }}"
                                                             alt=""
                                                             class="img-thumbnail">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Status') }} <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control" required>
                                            <option value="draft" {{ $event->status == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                            <option value="active" {{ $event->status == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                            <option value="paused" {{ $event->status == 'paused' ? 'selected' : '' }}>{{ __('Paused') }}</option>
                                            <option value="expired" {{ $event->status == 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="show_on_frontend" value="1" {{ $event->show_on_frontend ? 'checked' : '' }}>
                                            {{ __('Show on Frontend') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Products Section -->
                            <div class="row mt-4" id="products-section">
                                <div class="col-12">
                                    <h5>{{ __('Event Products') }}</h5>
                                    <p class="text-muted">{{ __('Manage products in this event') }}</p>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Product') }}</th>
                                                    <th>{{ __('Event Price') }}</th>
                                                    <th>{{ __('Discount') }}</th>
                                                    <th>{{ __('Priority') }}</th>
                                                    <th>{{ __('Active') }}</th>
                                                    <th>{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($event->eventProducts as $eventProduct)
                                                    <tr>
                                                        <td>
                                                            @if ($eventProduct->product)
                                                                {{ $eventProduct->product->product_name }}
                                                            @else
                                                                <span class="text-danger">{{ __('Product Deleted') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($eventProduct->event_price)
                                                                {{ get_price($eventProduct->event_price) }}
                                                            @else
                                                                <span class="text-muted">{{ __('Original') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($eventProduct->discount_amount > 0)
                                                                <span class="badge badge-danger">
                                                                    @if ($eventProduct->discount_type == 'percentage')
                                                                        {{ $eventProduct->discount_amount }}%
                                                                    @else
                                                                        {{ get_price($eventProduct->discount_amount) }}
                                                                    @endif
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $eventProduct->product_priority }}</td>
                                                        <td>
                                                            <label class="custom-switch">
                                                                <input type="checkbox"
                                                                       class="custom-switch-input"
                                                                       disabled
                                                                       {{ $eventProduct->is_active ? 'checked' : '' }}>
                                                                <span class="custom-switch-indicator"></span>
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('events.show', $event->id) }}"
                                                               class="btn btn-sm btn-info">
                                                                <i class="bx bx-edit"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="{{ route('events.show', $event->id) }}"
                                       class="btn btn-primary mt-2">
                                        <i class="bx bx-plus"></i> {{ __('Manage Products') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <a href="{{ route('events') }}" class="btn btn-danger">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Update Event') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    @include('admin.common.selector-modal')
@endsection
@push('scripts')
    <script>
        $(document).ready(function () {
            // Handle event type change
            $('#event_type').change(function () {
                var type = $(this).val();
                if (type === 'daily') {
                    $('#start_date_field, #end_date_field').hide();
                    $('#daily_start_field, #daily_end_field').show();
                } else if (type === 'date_range') {
                    $('#start_date_field, #end_date_field').show();
                    $('#daily_start_field, #daily_end_field').hide();
                } else {
                    $('#start_date_field, #end_date_field').hide();
                    $('#daily_start_field, #daily_end_field').hide();
                }
            });

            // Handle campaign type change
            $('#campaign_type').change(function () {
                var type = $(this).val();
                if (type === 'product' || type === 'event') {
                    $('#products-section').show();
                } else {
                    $('#products-section').hide();
                }
            });

            // Initialize campaign type state
            $('#campaign_type').trigger('change');

            // Generate slug from title
            $('input[name="event_title"]').on('input', function () {
                var slug = $(this).val().toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
                $('input[name="slug"]').val(slug);
            });
        });
    </script>
@endpush