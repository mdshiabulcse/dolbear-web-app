@extends('admin.partials.master')
@section('title')
    {{ __('Event Details') }} - {{ $event->event_title }}
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
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-block">
                    <h2 class="section-title">{{ $event->event_title }}</h2>
                    <p class="section-lead">
                        @if ($event->is_active_now)
                            <span class="badge badge-success">{{ __('Currently Active') }}</span>
                        @else
                            <span class="badge badge-secondary">{{ __('Inactive') }}</span>
                        @endif
                        <span class="badge badge-info">{{ $event->total_products }} {{ __('Products') }}</span>
                    </p>
                </div>
                <div class="buttons">
                    <a href="{{ route('events.edit', $event->id) }}" class="btn btn-primary">
                        <i class="bx bx-edit"></i> {{ __('Edit Event') }}
                    </a>
                    <a href="{{ route('events') }}" class="btn btn-outline-primary">
                        <i class="bx bx-arrow-back"></i> {{ __('Back') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Event Info Card -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Event Information') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>{{ __('Title') }}:</strong> {{ $event->event_title }}</p>
                                <p><strong>{{ __('Type') }}:</strong>
                                    <span class="badge badge-{{ $event->event_type == 'daily' ? 'info' : 'primary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                                    </span>
                                </p>
                                <p><strong>{{ __('Priority') }}:</strong> {{ $event->event_priority }}</p>
                                <p><strong>{{ __('Status') }}:</strong>
                                    <span class="badge badge-{{ $event->status == 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($event->status) }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                @if($event->event_type == 'daily')
                                    <p><strong>{{ __('Daily Start Time') }}:</strong> {{ $event->daily_start_time ?: '-' }}</p>
                                    <p><strong>{{ __('Daily End Time') }}:</strong> {{ $event->daily_end_time ?: '-' }}</p>
                                    <p><strong>{{ __('Active Window') }}:</strong>
                                        @if($event->daily_start_time && $event->daily_end_time)
                                            {{ $event->daily_start_time }} - {{ $event->daily_end_time }}
                                        @else
                                            <span class="text-danger">{{ __('Not configured') }}</span>
                                        @endif
                                    </p>
                                @else
                                    <p><strong>{{ __('Start Date') }}:</strong> {{ $event->event_start_date }}</p>
                                    <p><strong>{{ __('End Date') }}:</strong> {{ $event->event_end_date }}</p>
                                    <p><strong>{{ __('Duration') }}:</strong> {{ $event->event_duration ?: '-' }}</p>
                                @endif
                                <p><strong>{{ __('Total Views') }}:</strong> {{ $event->total_views }}</p>
                            </div>
                            <div class="col-md-12">
                                <p><strong>{{ __('Description') }}:</strong> {{ $event->description ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Event Banner') }}</h4>
                    </div>
                    <div class="card-body text-center">
                        @if($event->bannerImageOriginal)
                            <img src="{{ $event->bannerImageOriginal }}"
                                 alt="{{ $event->event_title }}" class="img-fluid">
                        @else
                            <img src="{{ static_asset('images/default/default-image-400x235.png') }}"
                                 alt="{{ $event->event_title }}" class="img-fluid">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Products -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>{{ __('Event Products') }}</h4>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addProductModal">
                            <i class="bx bx-plus"></i> {{ __('Add Product') }}
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-md">
                                <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Original Price') }}</th>
                                    <th>{{ __('Event Price') }}</th>
                                    <th>{{ __('Discount') }}</th>
                                    <th>{{ __('Event Stock') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($event->eventProducts as $eventProduct)
                                    <tr>
                                        <td>
                                            @if ($eventProduct->product)
                                                <img src="{{ $eventProduct->product->image_40x40 }}"
                                                     alt="{{ $eventProduct->product->product_name }}"
                                                     style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                                                {{ $eventProduct->product->product_name }}
                                            @else
                                                <span class="text-danger">{{ __('Product Deleted') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($eventProduct->product && $eventProduct->product->category)
                                                @php
                                                    $catLang = $eventProduct->product->category->categoryLanguage()->where('lang', app()->getLocale())->first();
                                                    if (!$catLang) {
                                                        $catLang = $eventProduct->product->category->categoryLanguage()->where('lang', 'en')->first();
                                                    }
                                                @endphp
                                                <span class="badge badge-secondary">{{ $catLang ? $catLang->name : 'N/A' }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($eventProduct->product)
                                                {{ get_price($eventProduct->product->price) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($eventProduct->event_price)
                                                {{ get_price($eventProduct->event_price) }}
                                            @else
                                                <span class="text-muted">-</span>
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
                                        <td>
                                            @if ($eventProduct->event_stock)
                                                <span class="badge badge-info">{{ $eventProduct->event_stock - $eventProduct->event_stock_sold }} / {{ $eventProduct->event_stock }}</span>
                                            @else
                                                <span class="text-muted">{{ __('Use Product Stock') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $eventProduct->product_priority }}</td>
                                        <td>
                                            <label class="custom-switch">
                                                <input type="checkbox"
                                                       class="custom-switch-input event-product-status-toggle"
                                                       data-event-id="{{ $event->id }}"
                                                       data-event-product-id="{{ $eventProduct->id }}"
                                                       data-product-id="{{ $eventProduct->product_id }}"
                                                       {{ $eventProduct->is_active ? 'checked' : '' }}>
                                                <span class="custom-switch-indicator"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info edit-event-product"
                                                    data-event-product-id="{{ $eventProduct->id }}"
                                                    data-product-id="{{ $eventProduct->product_id }}"
                                                    data-event-price="{{ $eventProduct->event_price }}"
                                                    data-discount-amount="{{ $eventProduct->discount_amount }}"
                                                    data-discount-type="{{ $eventProduct->discount_type }}"
                                                    data-event-stock="{{ $eventProduct->event_stock }}"
                                                    data-product-priority="{{ $eventProduct->product_priority }}"
                                                    data-is-active="{{ $eventProduct->is_active }}"
                                                    data-badge-text="{{ $eventProduct->badge_text }}"
                                                    data-badge-color="{{ $eventProduct->badge_color }}">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger remove-event-product"
                                                    data-event-product-id="{{ $eventProduct->id }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
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
    </section>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="addProductForm" action="{{ route('events.add.product', $event->id) }}" method="POST" onsubmit="return false;">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add Product to Event') }}</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <i class="bx bx-x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Product Selection with Details -->
                        <div class="form-group">
                            <label>{{ __('Select Product') }} <span class="text-danger">*</span></label>
                            <select name="product_id" id="productSelect" class="form-control" required>
                                <option value="">{{ __('Select Product') }}</option>
                                @foreach ($products as $prod)
                                    @php
                                        $categoryName = 'N/A';
                                        if ($prod->category) {
                                            $categoryLang = $prod->category->categoryLanguage()->where('lang', app()->getLocale())->first();
                                            if (!$categoryLang) {
                                                $categoryLang = $prod->category->categoryLanguage()->where('lang', 'en')->first();
                                            }
                                            if ($categoryLang) {
                                                $categoryName = $categoryLang->name;
                                            }
                                        }
                                    @endphp
                                    <option value="{{ $prod->id }}"
                                            data-price="{{ $prod->price }}"
                                            data-name="{{ $prod->product_name }}"
                                            data-image="{{ $prod->image_40x40 }}"
                                            data-category="{{ $categoryName }}"
                                            data-stock="{{ $prod->current_stock }}">
                                        {{ $prod->product_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Product Details Preview -->
                        <div id="productPreview" class="card bg-light mb-3" style="display: none;">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <img id="previewImage" src="" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                    </div>
                                    <div class="col">
                                        <h6 class="mb-1" id="previewName"></h6>
                                        <p class="mb-0">
                                            <span class="badge badge-info" id="previewCategory"></span>
                                            <span class="ml-2 text-muted">{{ __('Stock') }}: <span id="previewStock"></span></span>
                                        </p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1">{{ __('Original Price') }}:</p>
                                        <h4 class="text-primary mb-0" id="previewOriginalPrice"></h4>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">{{ __('Event Price') }}:</p>
                                        <h4 class="text-success mb-0" id="previewEventPrice"></h4>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <p class="mb-1">{{ __('Discount') }}:</p>
                                        <h5 class="text-danger mb-0" id="previewDiscount"></h5>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">{{ __('You Save') }}:</p>
                                        <h5 class="text-success mb-0" id="previewSavings"></h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Discount Settings -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Discount Amount') }}</label>
                                    <input type="number" name="discount_amount" id="discountAmountInput" class="form-control" value="0" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Discount Type') }}</label>
                                    <select name="discount_type" id="discountTypeSelect" class="form-control">
                                        <option value="flat">{{ __('Flat (Fixed Amount)') }}</option>
                                        <option value="percentage">{{ __('Percentage (%)') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Event Stock & Priority Settings -->
                        <div class="row">
                            <!-- Event Price is now always auto-calculated from discount -->
                            <input type="hidden" name="event_price" id="eventPriceInput" value="">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Event Stock (Optional)') }}</label>
                                    <input type="number" name="event_stock" id="eventStockInput" class="form-control" min="1" placeholder="{{ __('Leave empty to use product stock') }}">
                                    <small class="text-muted">{{ __('Leave empty to use original product stock') }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Display Priority') }}</label>
                                    <input type="number" name="product_priority" class="form-control" value="0" min="0">
                                    <small class="text-muted">{{ __('Lower number = higher priority') }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Validation Alert -->
                        <div class="alert alert-warning" id="validationAlert" style="display: none;">
                            <i class="bx bx-error"></i>
                            <span id="validationMessage"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" id="submitProductBtn">{{ __('Add Product') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="editProductForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Edit Event Product') }}</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <i class="bx bx-x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="edit_product_id">
                        <!-- Event price is auto-calculated from discount, kept as hidden field -->
                        <input type="hidden" name="event_price" id="edit_event_price" value="">

                        <!-- Product Details Preview -->
                        <div class="card bg-light mb-3" id="editProductPreview">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <img id="editPreviewImage" src="" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                    </div>
                                    <div class="col">
                                        <h6 class="mb-1" id="editPreviewName"></h6>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1">{{ __('Original Price') }}:</p>
                                        <h4 class="text-primary mb-0" id="editPreviewOriginalPrice"></h4>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">{{ __('Event Price') }}:</p>
                                        <h4 class="text-success mb-0" id="editPreviewEventPrice"></h4>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <p class="mb-1">{{ __('Discount') }}:</p>
                                        <h5 class="text-danger mb-0" id="editPreviewDiscount"></h5>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">{{ __('You Save') }}:</p>
                                        <h5 class="text-success mb-0" id="editPreviewSavings"></h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Discount Amount') }}</label>
                            <input type="number" name="discount_amount" id="edit_discount_amount" class="form-control" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Discount Type') }}</label>
                            <select name="discount_type" id="edit_discount_type" class="form-control">
                                <option value="flat">{{ __('Flat (Fixed Amount)') }}</option>
                                <option value="percentage">{{ __('Percentage (%)') }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Display Priority') }}</label>
                            <input type="number" name="product_priority" id="edit_product_priority" class="form-control" min="0">
                        </div>
                        <div class="form-group">
                            <label>{{ __('Event Stock (Optional)') }}</label>
                            <input type="number" name="event_stock" id="edit_event_stock" class="form-control" min="1" placeholder="{{ __('Leave empty to use product stock') }}">
                            <small class="text-muted">{{ __('Leave empty to use original product stock, or enter limited stock for this event') }}</small>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Badge Text') }}</label>
                            <input type="text" name="badge_text" id="edit_badge_text" class="form-control"
                                   placeholder="{{ __('e.g., Hot Deal, Limited Time') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ __('Badge Color') }}</label>
                            <input type="color" name="badge_color" id="edit_badge_color" class="form-control" value="#ff0000">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_active" id="edit_is_active" value="1" checked>
                                {{ __('Active') }}
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        // Currency symbol
        var currencySymbol = '{{ get_symbol() }}';

        // Format currency helper - defined first before use
        function formatCurrency(amount) {
            if (isNaN(amount)) amount = 0;
            return currencySymbol + parseFloat(amount).toFixed(2);
        }

        // Get price helper (similar to PHP get_price)
        function getPrice(amount) {
            if (isNaN(amount)) amount = 0;
            return currencySymbol + parseFloat(amount).toFixed(2);
        }

        // Store current product data
        var currentProduct = null;

        $(document).ready(function () {
            console.log('Document ready, initializing event product handlers...');

            // Verify the form exists
            var $addProductForm = $('#addProductForm');
            if ($addProductForm.length === 0) {
                console.error('Add Product Form not found!');
                return;
            }
            console.log('Add Product Form found, attaching submit handler...');

            // Product selection change handler
            $('#productSelect').on('change', function() {
                console.log('Product selection changed');
                var $selectedOption = $(this).find('option:selected');
                var productId = $(this).val();

                console.log('Selected product ID:', productId);

                if (productId === '') {
                    $('#productPreview').hide();
                    currentProduct = null;
                    return;
                }

                // Get product data from option attributes
                var price = $selectedOption.data('price');
                var name = $selectedOption.data('name');
                var image = $selectedOption.data('image');
                var category = $selectedOption.data('category');
                var stock = $selectedOption.data('stock');

                console.log('Product data:', { price: price, name: name, image: image, category: category, stock: stock });

                currentProduct = {
                    id: productId,
                    price: parseFloat(price) || 0,
                    name: name || '',
                    image: image || '',
                    category: category || 'N/A',
                    stock: stock || 0
                };

                console.log('Current product object:', currentProduct);

                // Update preview
                $('#previewImage').attr('src', currentProduct.image);
                $('#previewName').text(currentProduct.name);
                $('#previewCategory').text(currentProduct.category);
                $('#previewStock').text(currentProduct.stock);
                $('#previewOriginalPrice').text(formatCurrency(currentProduct.price));

                console.log('Preview updated, calculating event price...');

                // Calculate and display event price
                calculateEventPrice();

                // Show preview
                $('#productPreview').slideDown();
                console.log('Product preview shown');
            });

            // Event price input change handler (no longer needed - price is auto-calculated)
            // $('#eventPriceInput').on('input', function() {
            //     console.log('Event price input changed');
            //     calculateEventPrice();
            // });

            // Discount amount change handler
            $('#discountAmountInput').on('input', function() {
                calculateEventPrice();
            });

            // Discount type change handler
            $('#discountTypeSelect').on('change', function() {
                calculateEventPrice();
            });

            // Calculate event price based on discount (ALWAYS auto-calculated)
            function calculateEventPrice() {
                if (!currentProduct) return;

                var discountAmount = parseFloat($('#discountAmountInput').val()) || 0;
                var discountType = $('#discountTypeSelect').val();

                // Always calculate based on discount
                var eventPrice;
                if (discountType === 'percentage') {
                    eventPrice = currentProduct.price - (currentProduct.price * (discountAmount / 100));
                } else {
                    eventPrice = currentProduct.price - discountAmount;
                }

                // Ensure price doesn't go below 0
                eventPrice = Math.max(0, eventPrice);

                // Calculate discount and savings
                var discountValue = currentProduct.price - eventPrice;
                var discountPercent = (discountValue / currentProduct.price) * 100;

                // Update display
                $('#previewEventPrice').text(formatCurrency(eventPrice));
                $('#previewDiscount').text(discountPercent.toFixed(0) + '% (' + formatCurrency(discountValue) + ')');
                $('#previewSavings').text(formatCurrency(discountValue));

                // Update hidden event_price input with calculated value
                $('#eventPriceInput').val(eventPrice.toFixed(2));

                // Validate event price
                validateEventPrice(eventPrice);
            }

            // Validate event price is less than original price
            function validateEventPrice(eventPrice) {
                var isValid = eventPrice < currentProduct.price;
                var $validationAlert = $('#validationAlert');
                var $submitBtn = $('#submitProductBtn');

                if (!isValid) {
                    $validationAlert.show();
                    $('#validationMessage').text('Event price must be less than original price to provide discount to customers!');
                    $submitBtn.prop('disabled', true);
                } else {
                    $validationAlert.hide();
                    $submitBtn.prop('disabled', false);
                }

                return isValid;
            }

            // Add product to event - with validation and DOM update
            $addProductForm.on('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Form submit event triggered');

                // Double-check validation before submit
                if (currentProduct) {
                    var discountAmount = parseFloat($('#discountAmountInput').val()) || 0;
                    var discountType = $('#discountTypeSelect').val();

                    // Always calculate event price from discount
                    var eventPrice;
                    if (discountType === 'percentage') {
                        eventPrice = currentProduct.price - (currentProduct.price * (discountAmount / 100));
                    } else {
                        eventPrice = currentProduct.price - discountAmount;
                    }

                    eventPrice = Math.max(0, eventPrice);

                    if (eventPrice >= currentProduct.price) {
                        toastr.error('{{ __("Event price must be less than original price to provide discount to customers!") }}');
                        return false;
                    }
                }

                var $form = $(this);
                var $submitBtn = $('#submitProductBtn');
                $submitBtn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Adding...');

                var formData = new FormData(this);
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $submitBtn.prop('disabled', false).html('{{ __('Add Product') }}');

                        if (response.status === 'success') {
                            toastr.success(response.message);
                            $('#addProductModal').modal('hide');
                            // Reset form
                            $form[0].reset();
                            $('#productPreview').hide();
                            currentProduct = null;

                            // Update total products count
                            var currentTotal = parseInt($('.badge-info').text().trim()) || 0;
                            $('.badge-info').text(currentTotal + 1);

                            // Add new row to table
                            if (response.event_product) {
                                var ep = response.event_product;
                                var product = ep.product || {};

                                // Get category name
                                var categoryName = 'N/A';
                                if (product.category) {
                                    // Try to get localized category name
                                    var catLang = null;
                                    @if(auth()->check() && auth()->user())
                                        categoryName = product.category.categoryLanguage
                                            ?.where('lang', '{{ app()->getLocale() }}')
                                            ?.first()
                                            ?.name || product.category.categoryLanguage
                                                ?.where('lang', 'en')
                                                ?.first()
                                                ?.name || categoryName;
                                    @endif
                                }

                                var newRow = '<tr>' +
                                    '<td>' +
                                        '<img src="' + (product.image_40x40 || '') + '" alt="' + (product.product_name || '') + '" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;"> ' +
                                        (product.product_name || 'Deleted Product') +
                                    '</td>' +
                                    '<td><span class="badge badge-secondary">' + categoryName + '</span></td>' +
                                    '<td>' + formatCurrency(product.price || 0) + '</td>' +
                                    '<td>' + (ep.event_price ? formatCurrency(ep.event_price) : '-') + '</td>' +
                                    '<td><span class="badge badge-danger">' +
                                        (ep.discount_type == 'percentage' ? ep.discount_amount + '%' : formatCurrency(ep.discount_amount || 0)) +
                                    '</span></td>' +
                                    '<td>' +
                                        (ep.event_stock ?
                                            '<span class="badge badge-info">' + (ep.event_stock - (ep.event_stock_sold || 0)) + ' / ' + ep.event_stock + '</span>' :
                                            '<span class="text-muted">Use Product Stock</span>'
                                        ) +
                                    '</td>' +
                                    '<td>' + (ep.product_priority || 0) + '</td>' +
                                    '<td>' +
                                        '<label class="custom-switch">' +
                                            '<input type="checkbox" ' +
                                                'class="custom-switch-input event-product-status-toggle" ' +
                                                'data-event-id="{{ $event->id }}" ' +
                                                'data-event-product-id="' + ep.id + '" ' +
                                                'data-product-id="' + ep.product_id + '" ' +
                                                (ep.is_active ? 'checked' : '') +
                                                '>' +
                                            '<span class="custom-switch-indicator"></span>' +
                                        '</label>' +
                                    '</td>' +
                                    '<td>' +
                                        '<button type="button" class="btn btn-sm btn-info edit-event-product" ' +
                                                'data-event-product-id="' + ep.id + '" ' +
                                                'data-product-id="' + ep.product_id + '" ' +
                                                'data-event-price="' + (ep.event_price || '') + '" ' +
                                                'data-discount-amount="' + (ep.discount_amount || 0) + '" ' +
                                                'data-discount-type="' + (ep.discount_type || 'flat') + '" ' +
                                                'data-event-stock="' + (ep.event_stock || '') + '" ' +
                                                'data-product-priority="' + (ep.product_priority || 0) + '" ' +
                                                'data-is-active="' + (ep.is_active ? 1 : 0) + '" ' +
                                                'data-badge-text="' + (ep.badge_text || '') + '" ' +
                                                'data-badge-color="' + (ep.badge_color || '#ff0000') + '">' +
                                                '<i class="bx bx-edit"></i>' +
                                            '</button> ' +
                                        '<button type="button" class="btn btn-sm btn-danger remove-event-product" ' +
                                                'data-event-product-id="' + ep.id + '">' +
                                                '<i class="bx bx-trash"></i>' +
                                            '</button>' +
                                        '</td>' +
                                    '</tr>';

                                $('tbody').append(newRow);
                            }
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr) {
                        $submitBtn.prop('disabled', false).html('{{ __('Add Product') }}');
                        var errorMsg = xhr.responseJSON?.message || 'Error adding product';
                        if (xhr.responseJSON?.errors) {
                            var errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMsg = errors.join(', ');
                        }
                        toastr.error(errorMsg);
                    }
                });
            });

            // Edit event product - use event delegation for dynamically added elements
            $(document).on('click', '.edit-event-product', function () {
                var $btn = $(this);
                var productId = $btn.data('product-id');
                var eventProductId = $btn.data('event-product-id');
                var eventPrice = $btn.data('event-price');

                // Build URL properly: /admin/events/{eventId}/products/{productId}
                $('#editProductForm').attr('action', '/admin/events/{{ $event->id }}/products/' + productId);
                $('#edit_product_id').val(productId);
                $('#edit_discount_amount').val($btn.data('discount-amount'));
                $('#edit_discount_type').val($btn.data('discount-type'));
                $('#edit_product_priority').val($btn.data('product-priority'));
                $('#edit_event_stock').val($btn.data('event-stock') || '');
                $('#edit_badge_text').val($btn.data('badge-text') || '');
                $('#edit_badge_color').val($btn.data('badge-color') || '#ff0000');
                $('#edit_is_active').prop('checked', $btn.data('is-active') === 1 || $btn.data('is-active') === '1');

                // Get the row to access product details for preview
                var $row = $btn.closest('tr');
                var productName = $row.find('td').eq(0).text().trim();
                var productImage = $row.find('td').eq(0).find('img').attr('src') || '';
                var originalPriceText = $row.find('td').eq(2).text().trim().replace(/[^\d.]/g, '');
                var originalPrice = parseFloat(originalPriceText) || 0;

                // Store product price for calculation
                $('#editProductForm').data('product-price', originalPrice);
                $('#editProductForm').data('product-name', productName);
                $('#editProductForm').data('product-image', productImage);

                // Update preview
                $('#editPreviewImage').attr('src', productImage);
                $('#editPreviewName').text(productName);
                $('#editPreviewOriginalPrice').text(formatCurrency(originalPrice));

                // Calculate and show initial event price
                calculateEditEventPrice();

                $('#editProductModal').modal('show');
            });

            // Edit discount change handlers - auto-calculate preview
            $('#edit_discount_amount').on('input', function() {
                calculateEditEventPrice();
            });

            $('#edit_discount_type').on('change', function() {
                calculateEditEventPrice();
            });

            // Calculate edit modal event price
            function calculateEditEventPrice() {
                var originalPrice = $('#editProductForm').data('product-price') || 0;
                if (originalPrice === 0) return;

                var discountAmount = parseFloat($('#edit_discount_amount').val()) || 0;
                var discountType = $('#edit_discount_type').val();

                var eventPrice;
                if (discountType === 'percentage') {
                    eventPrice = originalPrice - (originalPrice * (discountAmount / 100));
                } else {
                    eventPrice = originalPrice - discountAmount;
                }
                eventPrice = Math.max(0, eventPrice);

                var discountValue = originalPrice - eventPrice;
                var discountPercent = (discountValue / originalPrice) * 100;

                // Update display
                $('#editPreviewEventPrice').text(formatCurrency(eventPrice));
                $('#editPreviewDiscount').text(discountPercent.toFixed(0) + '% (' + formatCurrency(discountValue) + ')');
                $('#editPreviewSavings').text(formatCurrency(discountValue));

                // Update hidden field
                $('#edit_event_price').val(eventPrice.toFixed(2));
            }

            // Update event product - auto-calculate event_price from discount
            $('#editProductForm').on('submit', function (e) {
                e.preventDefault();

                var formData = new FormData(this);
                var productId = $('#edit_product_id').val();
                var action = $(this).attr('action');
                var $btn = $('.edit-event-product[data-product-id="' + productId + '"]');
                var $row = $btn.closest('tr');

                // Get product original price from stored data
                var productPrice = $('#editProductForm').data('product-price') || 0;

                // Get the pre-calculated event price from hidden field (set by calculateEditEventPrice)
                var calculatedEventPrice = parseFloat($('#edit_event_price').val()) || 0;

                // Double-check calculation
                var discountAmount = parseFloat($('#edit_discount_amount').val()) || 0;
                var discountType = $('#edit_discount_type').val();

                if (discountType === 'percentage') {
                    calculatedEventPrice = productPrice - (productPrice * (discountAmount / 100));
                } else {
                    calculatedEventPrice = productPrice - discountAmount;
                }
                calculatedEventPrice = Math.max(0, calculatedEventPrice);

                // Update the hidden event_price field with calculated value
                $('#edit_event_price').val(calculatedEventPrice.toFixed(2));
                formData.set('event_price', calculatedEventPrice.toFixed(2));

                // Ensure CSRF token is included in FormData
                if (!formData.has('_token')) {
                    formData.append('_token', '{{ csrf_token() }}');
                }

                $.ajax({
                    url: action,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            $('#editProductModal').modal('hide');

                            // Update the row in the table
                            if (response.event_product) {
                                var ep = response.event_product;
                                var product = ep.product || {};

                                // Update event price cell
                                var priceCell = $row.find('td').eq(3);
                                priceCell.html(ep.event_price ? getPrice(ep.event_price) : '-');

                                // Update discount cell
                                var discountCell = $row.find('td').eq(4);
                                discountCell.html(`
                                    <span class="badge badge-danger">
                                        ${ep.discount_type == 'percentage' ? ep.discount_amount + '%' : getPrice(ep.discount_amount)}
                                    </span>
                                `);

                                // Update priority cell
                                $row.find('td').eq(6).text(ep.product_priority || 0);

                                // Update status toggle
                                var statusCell = $row.find('td').eq(7);
                                statusCell.html(`
                                    <label class="custom-switch">
                                        <input type="checkbox"
                                               class="custom-switch-input event-product-status-toggle"
                                               data-event-id="{{ $event->id }}"
                                               data-event-product-id="${ep.id}"
                                               data-product-id="${ep.product_id}"
                                               ${ep.is_active ? 'checked' : ''}>
                                        <span class="custom-switch-indicator"></span>
                                    </label>
                                `);

                                // Update edit button data attributes
                                var $editBtn = $row.find('.edit-event-product');
                                $editBtn.attr('data-event-price', ep.event_price || '');
                                $editBtn.attr('data-discount-amount', ep.discount_amount || 0);
                                $editBtn.attr('data-discount-type', ep.discount_type || 'flat');
                                $editBtn.attr('data-event-stock', ep.event_stock || '');
                                $editBtn.attr('data-product-priority', ep.product_priority || 0);
                                $editBtn.attr('data-is-active', ep.is_active ? 1 : 0);
                                $editBtn.attr('data-badge-text', ep.badge_text || '');
                                $editBtn.attr('data-badge-color', ep.badge_color || '#ff0000');
                            }
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Error updating product');
                    }
                });
            });

            // Toggle event product status
            $(document).on('change', '.event-product-status-toggle', function () {
                var $checkbox = $(this);
                var eventId = $checkbox.data('event-id');
                var eventProductId = $checkbox.data('event-product-id');
                var productId = $checkbox.data('product-id');
                var isActive = $checkbox.is(':checked') ? 1 : 0;

                $checkbox.prop('disabled', true); // Disable during request

                $.ajax({
                    url: '/admin/events/' + eventId + '/products/' + productId,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        is_active: isActive,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);

                            // Update edit button data attribute
                            var $editBtn = $checkbox.closest('tr').find('.edit-event-product');
                            $editBtn.attr('data-is-active', isActive);
                        } else {
                            toastr.error(response.message || 'Status update failed');
                            $checkbox.prop('checked', !isActive);
                        }
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Error updating status');
                        $checkbox.prop('checked', !isActive);
                    },
                    complete: function () {
                        $checkbox.prop('disabled', false); // Re-enable
                    }
                });
            });

            // Remove product from event - use event delegation for dynamically added elements
            $(document).on('click', '.remove-event-product', function () {
                var $btn = $(this);
                var eventProductId = $btn.data('event-product-id');
                var $row = $btn.closest('tr');

                if (confirm('{{ __('Are you sure you want to remove this product from the event?') }}')) {
                    $btn.prop('disabled', true);

                    $.ajax({
                        url: '{{ route('events.remove.product', $event->id) }}',
                        type: 'DELETE',
                        data: {
                            product_id: $row.find('.edit-event-product').data('product-id'),
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                toastr.success(response.message);

                                // Update total products count
                                var newTotal = parseInt($('.badge-info').text()) - 1;
                                $('.badge-info').text(Math.max(0, newTotal));

                                // Remove row with animation
                                $row.fadeOut(300, function () {
                                    $(this).remove();
                                });
                            } else {
                                toastr.error(response.message);
                                $btn.prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Error removing product');
                            $btn.prop('disabled', false);
                        }
                    });
                }
            });
        });
    </script>
@endpush