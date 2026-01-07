@extends('admin.partials.master')

@section('store_active')
    active
@endsection
@section('store_create_active')
    active
@endsection

@section('title')
    Create Store
@endsection

@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">
                        Create Store
                    </h2>
                </div>
                <div class="buttons add-button">
                    <a href="{{ old('r') != '' ? old('r') : (@$r ? $r : url()->previous()) }}"
                        class="btn btn-outline-primary"><i class='bx bx-arrow-back'></i>{{ __('Back') }}</a>
                </div>
            </div>

            <form action="{{ route('admin.store.save') }}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-12 col-sm-12 col-md-8 col-lg-9 middle">
                        <div class="tab-content no-padding" id="myTabContent2">
                            <div class="tab-pane fade show active" id="product-info" role="tabpanel"
                                aria-labelledby="product-info-tab">
                                <div class="card">
                                    <div class="card-header extra-padding">
                                        <h4>Store Information</h4>
                                    </div>

                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="name">Store Name *</label>
                                            <input type="text" class="form-control ai_content_name" name="name"
                                                id="name" placeholder="Name">
                                            @if ($errors->has('name'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('name') }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <label for="name">Phone </label>
                                            <input type="text" class="form-control ai_content_name" name="phone"
                                                id="phone" placeholder="Phone">
                                            @if ($errors->has('phone'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('phone') }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <label for="name">Store Address </label>
                                            <input type="text" class="form-control ai_content_name" name="address"
                                                id="address" placeholder="Address">
                                            @if ($errors->has('address'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('address') }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <label for="map">Map Location</label>
                                            <textarea class="form-control ai_content_name" name="map" id="map" placeholder="Map Embed Code" rows="6"></textarea>
                                            @if ($errors->has('map'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('map') }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <label for="logo" for="image">Image (900x300)</label>
                                            <div>
                                                <div class="input-group gallery-modal" id="btnSubmit"  data-for="image" data-selection="single"
                                                     data-target="#galleryModal" data-dismiss="modal">
                                                    <input type="hidden" name="image"  value="{{ old('banner') !='' ? old('banner') : (@$post->blog->banner_id ? $post->blog->banner_id : '' )}}" id="image" class="image-selected">
                                                    <span class="form-control"><span class="counter">{{ old('banner') != '' ? substr_count(old('banner'), ',') + 1  : (@$post->blog->banner_id != '' ? substr_count(@$post->blog->banner_id, ',') + 1 : 0) }}</span> {{ __('file chosen') }}</span>
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">
                                                            {{ __('Choose File') }}
                                                        </div>
                                                    </div>
                                                    @if ($errors->has('banner'))
                                                        <div class="invalid-feedback">
                                                            <p>{{ $errors->first('banner') }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="selected-media-box">
                                                    <div class="mt-2 gallery gallery-md d-flex">
                                                        @php
                                                            $thumb = old('banner') ? old('banner') : @$post->blog->banner_id;
                                                            $banner = \App\Models\Media::find($thumb);
                                                        @endphp
                                                        @if($banner)
                                                            <div class="selected-media mr-2 mb-2 mt-3 ml-0" data-id="{{ $banner->id }}">
                                                                @if(@is_file_exists($banner->image_variants['image_72x72'], $banner->image_variants['storage']))
                                                                    <img src="{{ get_media($banner->image_variants['image_72x72'], $banner->image_variants['storage'])}}" alt="img-thumbnail"
                                                                        class="img-thumbnail logo-profile">
                                                                @else
                                                                    <img src="{{ static_asset('images/default/default-image-72x72.png') }}" alt="img-thumbnail"
                                                                        class="img-thumbnail logo-profile">
                                                                @endif
                                                                <div class="image-remove">
                                                                    <a href="javaScript:void(0)" class="remove"><i class="bx bx-x"></i></a>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="selected-media mr-2 mb-2 mt-3 ml-0">
                                                                <img src="{{ static_asset('images/default/default-image-72x72.png') }}" data-default="{{ static_asset('images/default/default-image-72x72.png') }}"
                                                                     alt="brand-logo" class="img-thumbnail logo-profile">
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
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
                                                <textarea type="text" class="summernote ai_description" name="description" id="description">{{ old('description') ? old('description') : '' }}</textarea>
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
    <!-- Modal -->
    @include('admin.common.selector-modal')
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
