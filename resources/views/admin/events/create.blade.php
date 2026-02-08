@extends('admin.partials.master')
@section('title')
    {{ __('Create Event') }}
@endsection
@section('marketing_active')
    active
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Create Event') }}</h2>
                    <p class="section-lead">
                        {{ __('Create a new event with scheduling and product management') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <form id="event-form" action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-header input-title">
                            <h4>{{ __('Event Information') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Event Title') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="event_title" class="form-control" required
                                               placeholder="{{ __('Enter event title') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Slug') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="slug" class="form-control" required
                                               placeholder="{{ __('Enter event slug') }}"
                                               oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-')">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{ __('Description') }}</label>
                                        <textarea name="description" class="form-control" rows="3"
                                                  placeholder="{{ __('Enter event description') }}"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Event Priority') }} <span class="text-danger">*</span></label>
                                        <input type="number" name="event_priority" class="form-control" value="0" min="0" required>
                                        <small class="text-muted">{{ __('Lower number = higher priority') }}</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Event Type') }} <span class="text-danger">*</span></label>
                                        <select name="event_type" class="form-control" id="event_type" required>
                                            <option value="date_range">{{ __('Date Range') }}</option>
                                            <option value="daily">{{ __('Daily') }}</option>
                                            <option value="recurring">{{ __('Recurring') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6" id="start_date_field">
                                    <div class="form-group">
                                        <label>{{ __('Start Date & Time') }} <span class="text-danger">*</span></label>
                                        <input type="datetime-local" name="event_schedule_start" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6" id="end_date_field">
                                    <div class="form-group">
                                        <label>{{ __('End Date & Time') }} <span class="text-danger">*</span></label>
                                        <input type="datetime-local" name="event_schedule_end" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6" id="daily_start_field" style="display:none;">
                                    <div class="form-group">
                                        <label>{{ __('Daily Start Time') }}</label>
                                        <input type="time" name="daily_start_time" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6" id="daily_end_field" style="display:none;">
                                    <div class="form-group">
                                        <label>{{ __('Daily End Time') }}</label>
                                        <input type="time" name="daily_end_time" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Background Color') }}</label>
                                        <input type="color" name="background_color" class="form-control" value="#ffffff">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Text Color') }}</label>
                                        <input type="color" name="text_color" class="form-control" value="#000000">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Banner Image') }}</label>
                                        <div class="input-group gallery-modal" id="btnSubmit" data-for="image"
                                             data-selection="single"
                                             data-target="#galleryModal" data-dismiss="modal">
                                            <input type="hidden" name="banner_image_id" value=""
                                                   class="image-selected">
                                            <span class="form-control"><span
                                                    class="counter">0</span> {{ __('file chosen') }}</span>
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    {{ __('Choose File') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="selected-media-box">
                                            <div class="mt-2 gallery gallery-md d-flex">
                                                <div class="selected-media mr-2 mb-2 mt-3 ml-0"
                                                     data-id="">
                                                    <img src="{{ static_asset('images/default/default-image-72x72.png') }}"
                                                         data-default="{{ static_asset('images/default/default-image-72x72.png') }}"
                                                         alt=""
                                                         class="img-thumbnail">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Status') }} <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control" required>
                                            <option value="draft">{{ __('Draft') }}</option>
                                            <option value="active">{{ __('Active') }}</option>
                                            <option value="paused">{{ __('Paused') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="show_on_frontend" value="1" checked>
                                            {{ __('Show on Frontend') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Products Section -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>{{ __('Event Products') }}</h5>
                                    <div class="form-group">
                                        <button type="button" class="btn btn-primary btn-sm" id="add-product-btn">
                                            <i class="bx bx-plus"></i> {{ __('Add Product') }}
                                        </button>
                                    </div>
                                    <div id="products-container">
                                        <!-- Products will be added here dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <a href="{{ route('events') }}" class="btn btn-danger">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Create Event') }}</button>
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

            // Add product row
            var productCount = 0;
            $('#add-product-btn').click(function () {
                productCount++;
                var html = `
                    <div class="product-row card p-3 mb-3" data-count="${productCount}">
                        <div class="row">
                            <div class="col-md-3">
                                <label>{{ __('Product') }}</label>
                                <select name="products[${productCount}][product_id]" class="form-control product-select" required>
                                    <option value="">{{ __('Select Product') }}</option>
                                    @foreach ($products as $prod)
                                        <option value="{{ $prod->id }}">{{ $prod->product_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>{{ __('Event Price') }}</label>
                                <input type="number" name="products[${productCount}][event_price]" class="form-control" step="0.01" placeholder="{{ __('Optional') }}">
                            </div>
                            <div class="col-md-2">
                                <label>{{ __('Discount Amount') }}</label>
                                <input type="number" name="products[${productCount}][discount_amount]" class="form-control" value="0" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <label>{{ __('Discount Type') }}</label>
                                <select name="products[${productCount}][discount_type]" class="form-control">
                                    <option value="flat">{{ __('Flat') }}</option>
                                    <option value="percentage">{{ __('Percentage') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>{{ __('Priority') }}</label>
                                <input type="number" name="products[${productCount}][product_priority]" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-md-1">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-danger btn-sm remove-product-btn">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                $('#products-container').append(html);
            });

            // Remove product row
            $(document).on('click', '.remove-product-btn', function () {
                $(this).closest('.product-row').remove();
            });

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