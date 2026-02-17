@extends('admin.partials.master')

@section('title')
    Create Delivery
@endsection

@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">
                        Create Delivery
                    </h2>
                </div>
                <div class="buttons add-button">
                    <a href="{{ route('orders') }}" class="btn btn-outline-primary"><i
                            class='bx bx-arrow-back'></i>{{ __('Back') }}</a>
                </div>
            </div>

            <form id="orderForm" action="{{ route('admin.delivery.pathao.store') }}" method="post"
                enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="orderId" value="{{ $orderData->id }}">
                <input type="hidden" name="district" value="{{ $orderData->shipping_address['district'] ?? '' }}">
                <input type="hidden" name="thana" value="{{ $orderData->shipping_address['thana'] ?? '' }}">

                <div class="row">
                    <div class="col-12 col-sm-12 col-md-8 col-lg-9 middle">
                        <div class="tab-content no-padding" id="myTabContent2">
                            <div class="tab-pane fade show active" id="product-info" role="tabpanel"
                                aria-labelledby="product-info-tab">
                                <div class="card">
                                    <div class="card-header extra-padding">
                                        <h4>Delivery Information</h4>
                                    </div>

                                    @if (!empty($orderData->pathao_delivery_id))

                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><b>Delivery ID:</b>
                                                        <span>{{ $orderData->pathao_delivery_id ?? 'N/A' }}</span>
                                                    </p>
                                                    <p><b>Delivery Fee:</b>
                                                        <span>{{ $orderData->delivery_fee ?? '0.00' }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                    @else

                                        <div class="card-body">
                                            <div class="row">

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="weight">Order ID</label>
                                                        <input type="text" class="form-control ai_content_name" name="orderId"
                                                            id="orderId" placeholder="Order ID"
                                                            value="{{ ltrim($orderData->code, '#') }}">
                                                        @if ($errors->has('orderId'))
                                                            <div class="invalid-feedback">
                                                                <p>{{ $errors->first('orderId') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="weight">Name</label>
                                                        <input type="text" class="form-control ai_content_name" name="name"
                                                            id="name" placeholder="Name"
                                                            value="{{ $orderData->shipping_address['name'] ?? "" }}">
                                                        @if ($errors->has('name'))
                                                            <div class="invalid-feedback">
                                                                <p>{{ $errors->first('name') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="weight">Phone</label>
                                                        <input type="text" class="form-control ai_content_name" name="phone"
                                                            id="phone" placeholder="Phone Number"
                                                            value="{{ $orderData->shipping_address['phone_no'] }}">
                                                        @if ($errors->has('phone'))
                                                            <div class="invalid-feedback">
                                                                <p>{{ $errors->first('phone') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="address">Address</label>
                                                        <textarea class="form-control ai_content_name" name="address"
                                                            id="address"
                                                            placeholder="Address">{{ $orderData->shipping_address['address'] }}</textarea>
                                                        <!-- Validation error message for address -->
                                                        @if ($errors->has('address'))
                                                            <div class="invalid-feedback">
                                                                <p>{{ $errors->first('address') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>


                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="city">City</label>
                                                        <select class="form-control select2" name="city" id="city">
                                                            <option disabled selected value="">Select City</option>
                                                        </select>

                                                        @if ($errors->has('city'))
                                                            <div class="invalid-feedback">
                                                                <p>{{ $errors->first('city') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="zone">Zone
                                                            ({{ $orderData->shipping_address['thana'] }})</label>
                                                        <select class="form-control select2" name="zone" id="zone">
                                                            <option disabled selected value="">Select zone</option>
                                                        </select>

                                                        @if ($errors->has('zone'))
                                                            <div class="invalid-feedback">
                                                                <p>{{ $errors->first('zone') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="zone">Delivery Type</label>
                                                        <select class="form-control select2" name="delivery_type"
                                                            id="delivery_type">
                                                            <option selected value="48">Normal</option>
                                                            <option value="12">On Demand</option>
                                                        </select>

                                                        @if ($errors->has('delivery_type'))
                                                            <div class="invalid-feedback">
                                                                <p>{{ $errors->first('delivery_type') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="item_type">Item Type</label>
                                                        <select class="form-control select2" name="item_type" id="item_type">
                                                            <option selected value="2">Parcel</option>
                                                            <option value="1">Document</option>
                                                        </select>

                                                        @if ($errors->has('item_type'))
                                                            <div class="invalid-feedback">
                                                                <p>{{ $errors->first('item_type') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                @php
                                                    $totalQuantity = 0;

                                                    foreach ($orderData->orderDetails as $orderDetail) {
                                                        $totalQuantity += $orderDetail->quantity;
                                                    }
                                                @endphp
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="weight">Item Quantity</label>
                                                        <input type="number" class="form-control ai_content_name"
                                                            name="quantity" id="quantity" placeholder="quantity"
                                                            value="{{ $totalQuantity }}">
                                                        @if ($errors->has('quantity'))
                                                            <div class="invalid-feedback">
                                                                <p>{{ $errors->first('quantity') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="weight">Item Weight(KG)</label>
                                                        <input type="number" class="form-control ai_content_name" name="weight"
                                                            id="weight" placeholder="Weight" step="0.01" min="0" value="0.5">
                                                        @if ($errors->has('weight'))
                                                            <div class="invalid-feedback">
                                                                <p>{{ $errors->first('weight') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="form-group">
                                                <label for="weight">Amount To Collect</label>
                                                <input type="number" class="form-control ai_content_name"
                                                    name="amount_to_collect" id="amount_to_collect"
                                                    placeholder="Amount To Collect"
                                                    value="{{ $orderData->payment_status == 'unpaid' ? $orderData->total_payable : 0 }}">
                                                @if ($errors->has('amount_to_collect'))
                                                    <div class="invalid-feedback">
                                                        <p>{{ $errors->first('amount_to_collect') }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                    @endif

                                </div>

                                @if (empty($orderData->pathao_delivery_id))
                                    <div>
                                        <button type="submit" class="btn btn-outline-primary" tabindex="4">
                                            {{ __('Save') }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    // Build products data safely with multiple fallback options
                    $productsData = [];
                    if (isset($orderData->orderDetails) && is_iterable($orderData->orderDetails)) {
                        foreach($orderData->orderDetails as $detail) {
                            $productName = 'Product';

                            // Try multiple ways to get product name
                            if (isset($detail->product) && $detail->product && isset($detail->product->product_name)) {
                                $productName = $detail->product->product_name;
                            } elseif (isset($detail->product_name)) {
                                $productName = $detail->product_name;
                            } elseif (isset($detail->product_id)) {
                                $productName = 'Product #' . $detail->product_id;
                            }

                            $productsData[] = [
                                'product_name' => $productName,
                                'quantity' => $detail->quantity ?? 1
                            ];
                        }
                    }
                    $productsJson = json_encode($productsData, JSON_HEX_APOS | JSON_HEX_QUOT);
                @endphp
                <input type="hidden" name="products" value="{{ $productsJson }}">


            </form>



        </div>
    </section>
@endsection

@section('footer-scripts')
    <script>
        $(document).ready(function () {

            const baseUrl = "<?php echo env('APP_URL'); ?>";
            const orderDistrict = "{{ $orderData->shipping_address['district'] ?? '' }}".trim().toLowerCase();
            const orderThana = "{{ $orderData->shipping_address['thana'] ?? '' }}".trim().toLowerCase();

            $.ajax({
                url: baseUrl + '/admin/pathao/city',
                type: 'GET',
                dataType: 'json',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                success: function (data) {
                    let selectedCityId = null;
                    const cityData = data?.data?.data
                    const $city = $('#city');

                    // Clear existing options
                    $city.empty();

                    // Add default option
                    $city.append($('<option>', {
                        value: '',
                        text: 'Select a city'
                    }));

                    // Add options for each city
                    $.each(cityData, function (index, city) {
                        const option = $('<option>', {
                            value: city.city_id,
                            text: city.city_name
                        });

                        // Match district name to city name
                        if (city.city_name.toLowerCase() === orderDistrict) {
                            selectedCityId = city.city_id;
                            option.attr('selected', true);
                        }

                        $city.append(option);
                    });

                    if (selectedCityId) {
                        $city.val(selectedCityId).trigger('change');
                    }

                },
                error: function (error) {
                    // Handle error response
                    console.error(error)
                }
            })

            // --- Load Zone List when City Changes ---
            $('#city').change(function () {
                const cityId = $(this).val();
                if (cityId) {
                    loadZones(cityId);
                }
            });

            // --- Function to Load Zones and Auto-select Thana ---
            function loadZones(cityId) {
                $.ajax({
                    url: baseUrl + '/admin/pathao/zone',
                    type: 'GET',
                    dataType: 'json',
                    data: { city_id: cityId },
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    success: function (data) {
                        const zoneData = data?.data?.data || [];
                        const $zone = $('#zone');

                        $zone.empty().append($('<option>', { value: '', text: 'Select Zone' }));

                        $.each(zoneData, function (index, zone) {
                            const zoneName = zone.zone_name.trim().toLowerCase();
                            const option = $('<option>', {
                                value: zone.zone_id,
                                text: zone.zone_name
                            });

                            // Auto-select if zone name matches Thana
                            if (zoneName === orderThana || zoneName.includes(orderThana)) {
                                option.attr('selected', true);
                            }

                            $zone.append(option);
                        });
                    },
                    error: function (error) {
                        console.error('Zone fetch error:', error);
                    }
                });
            }

            $('#orderForm').submit(function (e) {
                // Prevent the default form submission
                e.preventDefault();

                // Serialize form data
                var formData = $(this).serialize();

                // Send AJAX request
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        // Handle success response
                        window.location.href = baseUrl + '/admin/orders'
                        // You can do further processing here if needed
                    },
                    error: function (error) {
                        // Clear previous error messages
                        $('.alert').remove();

                        // Parse the error response
                        var errors = error.responseJSON;

                        // Concatenate all error messages
                        var errorMessage = '<div class="alert alert-danger" role="alert"><ul>';
                        $.each(errors.errors, function (key, value) {
                            errorMessage += '<li>' + value[0] + '</li>';
                            // Add 'is-invalid' class to the input field
                            // $('#' + key).addClass('is-invalid');
                        });
                        errorMessage += '</ul></div>';

                        // Append the error message to the form
                        $('#orderForm').prepend(errorMessage);
                    }
                });
            });

        });

    </script>
@endsection