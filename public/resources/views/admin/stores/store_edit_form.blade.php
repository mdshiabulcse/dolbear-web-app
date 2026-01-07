@extends('admin.partials.master')

@section('title')
    Store Edit
@endsection

@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">
                         Store Edit
                    </h2>
                </div>
                <div class="buttons add-button">
                    <a href="{{ old('r') != '' ? old('r') : (@$r ? $r : url()->previous()) }}"
                        class="btn btn-outline-primary"><i class='bx bx-arrow-back'></i>{{ __('Back') }}</a>
                </div>
            </div>

            <form action="{{ route('store.update') }}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-12 col-sm-12 col-md-8 col-lg-9 middle">
                        <div class="tab-content no-padding" id="myTabContent2">
                            <div class="tab-pane fade show active" id="product-info" role="tabpanel"
                                aria-labelledby="product-info-tab">
                                <div class="card">
                                    <div class="card-header extra-padding">
                                        <h4>Store Information Edit</h4>
                                    </div>

                                    <input type="text" class="form-control ai_content_name" name="id"
                                    id="id" value="{{ $store->id }}" hidden>

                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="name">Store Name *</label>
                                            <input type="text" class="form-control ai_content_name" name="name"
                                                id="name" placeholder="Name" value="{{ $store->name }}">
                                            @if ($errors->has('name'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('name') }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <label for="name">Phone </label>
                                            <input type="text" class="form-control ai_content_name" name="phone"
                                                id="phone" placeholder="Phone" value = "{{ $store->phone }}">
                                            @if ($errors->has('phone'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('phone') }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <label for="name">Store Address </label>
                                            <input type="text" class="form-control ai_content_name" name="address"
                                                id="address" placeholder="Address" value = "{{ $store->address }}">
                                            @if ($errors->has('address'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('address') }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <label for="map">Map Location</label>
                                            <textarea class="form-control ai_content_name" name="map" id="map" placeholder="Map Embed Code" rows="6">{{ $store->map }}</textarea>
                                            @if ($errors->has('map'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('map') }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <label for="description"
                                                class="form-control-label">{{ __('Description') }}</label>
                                            <div>
                                                @include('admin.ai_writer.ai_btn', [
                                                    'name' => 'ai_description',
                                                    'length' => '259',
                                                    'topic' => 'ai_content_name',
                                                    'long_description' => 1,
                                                ])
                                                <textarea type="text" class="summernote ai_description" name="description" id="description">{{ old('description') ? old('description') : '' }} {{ $store->description }}</textarea>
                                            </div>
                                            @if ($errors->has('description'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('description') }}</p>
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



@section('style')
    <link rel="stylesheet" href="{{ static_asset('admin/css/dropzone.css') }}">
@endsection
@section('page-style')
    <link rel="stylesheet" href="{{ static_asset('admin/css/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ static_asset('admin/css/daterangepicker.css') }}">
@endsection

@push('page-script')
    <script src="{{ static_asset('admin/js/summernote-bs4.js') }}"></script>
    <script type="text/javascript" src="{{ static_asset('admin/js/daterangepicker.min.js') }}"></script>
    @if (addon_is_activated('ai_writer'))
        <script src="{{ static_asset('admin/js/ai_writer.js') }}"></script>
    @endif
@endpush
@push('script')
    <script type="text/javascript" src="{{ static_asset('admin/js/dropzone.min.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            var toAdd = $('.daterange-cus');
            toAdd.daterangepicker({
                autoUpdateInput: false,
                timePicker: true,
                locale: {
                    cancelLabel: '{{ __('Clear') }}',
                    format: 'M-DD-YYYY hh:mm A'
                }
            });
            toAdd.on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM-DD-YYYY hh:mm A') + ' - ' + picker.endDate.format(
                    'MM-DD-YYYY hh:mm A'));
            });
            toAdd.on('cancel.daterangepicker', function() {
                $(this).val('');
            });
            $(document).ready(function() {
                $(document).on('change', '#description_images', function() {

                    let input = this;

                    if (input.files) {
                        $('#description_image_modal').empty();
                        var filesAmount = input.files.length;

                        for (let i = 0; i < filesAmount; i++) {
                            var reader = new FileReader();

                            reader.onload = function(event) {
                                $($.parseHTML('<img class="description_image">')).attr('src',
                                    event.target.result).appendTo(
                                    '#description_image_modal');
                            }

                            reader.readAsDataURL(input.files[i]);
                        }
                    }
                })
            })

        });
    </script>
@endpush