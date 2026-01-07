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
                    <a href="{{ route('orders') }}"
                        class="btn btn-outline-primary"><i class='bx bx-arrow-back'></i>{{ __('Back') }}</a>
                </div>
            </div>

            <form id="orderForm" action="{{ route('admin.delivery.pathao.store') }}" method="post" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="orderId" value="{{ $orderData->id }}" >

                <div class="row">
                    <div class="col-12 col-sm-12 col-md-8 col-lg-9 middle">
                        <div class="tab-content no-padding" id="myTabContent2">
                            <div class="tab-pane fade show active" id="product-info" role="tabpanel"
                                aria-labelledby="product-info-tab">
                                <div class="card">
                                    <div class="card-header extra-padding">
                                        <h4>Delivery Information</h4>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="weight">Name</label>
                                                    <input type="text" class="form-control ai_content_name" name="name"
                                                        id="name" placeholder="Name" value="{{ $orderData->shipping_address['name'] ?? "" }}">
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
                                                        id="phone" placeholder="Phone Number" value="{{ substr($orderData->shipping_address['phone_no'] , 3) ?? '' }}">
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
                                                    <textarea class="form-control ai_content_name" name="address" id="address" placeholder="Address">{{ $orderData->shipping_address['address'] }}</textarea>
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
                                                    <p>({{ $orderData->shipping_address['thana'] }})</p>
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
                                                    <label for="zone">Zone</label>
                                                    <p>Not Selected</p>
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
                                                    <select class="form-control select2" name="delivery_type" id="delivery_type">
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
                                                    <input type="number" class="form-control ai_content_name" name="quantity"
                                                        id="quantity" placeholder="quantity" value="{{ $totalQuantity }}">
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
                                                        id="weight" placeholder="Weight" value="0.5">
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
                                            <input type="number" class="form-control ai_content_name" name="amount_to_collect"
                                                id="amount_to_collect" placeholder="Amount To Collect" value="0">
                                                @if ($errors->has('amount_to_collect'))
                                                    <div class="invalid-feedback">
                                                        <p>{{ $errors->first('amount_to_collect') }}</p>
                                                    </div>
                                                @endif
                                        </div>
                                    </div>





                                </div>

                                <div>
                                    <button type="submit" class="btn btn-outline-primary"
                                        tabindex="4">
                                        {{ __('Save') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>



        </div>
    </section>
@endsection

@section('footer-scripts')
<script>
$(document).ready(function() {

    const baseUrl = "<?php echo env('APP_URL'); ?>";

    $.ajax({
        url: baseUrl +'/admin/pathao/city',
        type: 'GET',
        dataType: 'json',
        headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        },
        success: function(data) 
        {
            
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
            $.each(cityData, function(index, city) {
                $city.append($('<option>', {
                    value: city.city_id,
                    text: city.city_name
                }));
            });

        },
        error: function(error) {
            // Handle error response
            console.error(error)
        }
    })

    $('#city').change(function() {
        let cityId = $(this).val()

        if(!cityId || cityId === '')
        {
            return
        }
    
        $.ajax({
            url: baseUrl + '/admin/pathao/zone',
            type: 'GET',
            dataType: 'json',
            data : {
                "city_id": cityId
            },
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            success: function(data) {

                const zoneData = data?.data?.data
                const $zone = $('#zone');

                // zone existing options
                $zone.empty();

                // Add default option
                $zone.append($('<option>', {
                    value: '',
                    text: 'Select a zone'
                }));

                // Add options for each city
                $.each(zoneData, function(index, zone) {
                    $zone.append($('<option>', {
                        value: zone.zone_id,
                        text: zone.zone_name
                    }));
                });
            },
            error: function(error) {
                // Handle error response
                console.error(error);
            }
        });
    })


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
