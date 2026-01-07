@extends('admin.partials.master')
@section('store_front_active')
    active
@endsection
@section('page-style')
    <link rel="stylesheet" href="{{ static_asset('admin/css/summernote-bs4.css') }}">
@endsection
@section('video')
    active
@endsection
@section('title')
    Website Video
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <h2 class="section-title">Store Front</h2>
            <div id="output-status"></div>
            <div class="row">
                @include('admin.store-front.theme-options-sitebar')
                <div class="col-md-9">
                    <div class="card email-card">
                        <div class="card-header">
                            <h4>Video</h4>
                        </div>
                        <div class="card-body col-md-10 middle">

                            <form method="post" action="{{route('update')}}">
                                @csrf
                                @method('put')
                                <div class="form">
                                    <div class="form-group">
                                        <label for="popup_title">Embedded Url</label>
                                        <input type="text" class="form-control" name="website.video" id="website.video" placeholder="https://www.youtube.com/embed/9R43mT.." value="{{ old('website.video') ? old('website.video') : settingHelper('website_video', $lang) }}">
                                        <input type="hidden" value="{{ $lang }}" name="site_lang">
                                    </div>
                                    <div class="text-md-right">
                                        <button class="btn btn-outline-primary" id="save-btn">
                                            {{ __('Update') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('page-script')
    <script src="{{ static_asset('admin/js/summernote-bs4.js') }}"></script>
@endpush
