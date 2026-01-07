@extends('admin.partials.master')

@section('title')
    Create Recommended
@endsection
@section('recorecommended_active')
    active
@endsection
@section('recommended_create_active')
    active
@endsection
@section('page-style')
    <link rel="stylesheet" href="{{ static_asset('admin/css/summernote-bs4.css') }}">
@endsection



@section('main-content')

    <section class="section">
        <div class="section-body">

            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">Create Popup Ads</h2>
                </div>
                
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Give Info</h4>
                        </div>
                        <div class="card-body">
                            
                            <form action="{{ route('recommended.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                               
                                <div class="form-group row mb-4">
                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" for="title">Name *</label>
                                    <div class="col-sm-12 col-md-7">
                                        
                                        <input type="text" name="name" id="name" class="form-control">

                                        @if ($errors->has('name'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('name') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-4">
                                    <label for="logo" class="col-form-label text-md-right col-12 col-md-3 col-lg-3" for="image">Image (900x300)</label>
                                    <div class="col-sm-12 col-md-7">
                                        <div class="input-group gallery-modal" id="btnSubmit"  data-for="image" data-selection="single"
                                             data-target="#galleryModal" data-dismiss="modal">
                                            <input type="hidden" name="banner"  value="{{ old('banner') !='' ? old('banner') : (@$post->blog->banner_id ? $post->blog->banner_id : '' )}}" id="image" class="image-selected">
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
                               
                                <div class="form-group row mb-4">
                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" for="meta_description">Description</label>
                                    <div class="col-sm-12 col-md-7">
                                        <textarea name="description" class="form-control" id="description"></textarea>
                                        @if ($errors->has('description'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('description') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row mb-4 text-right">
                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                                    <div class="col-sm-12 col-md-7 ">
                                        <button class="btn btn-outline-primary">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    @include('admin.common.selector-modal')
@endsection

@push('page-script')
    <script src="{{ static_asset('admin/js/summernote-bs4.js') }}"></script>
@endpush
@section('style')
    <link rel="stylesheet" href="{{ static_asset('admin/css/dropzone.css') }}">
@endsection
@push('script')
    <script type="text/javascript" src="{{ static_asset('admin/js/dropzone.min.js') }}"></script>
@endpush
