@extends('admin.partials.master')
@section('page-style')
    <link rel="stylesheet" href="{{ static_asset('admin/css/summernote-bs4.css') }}" />
@endsection
@section('banners')
    active
@endsection
@section('title')
    {{ __('Banners') }}
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-12 col-md-8 col-lg-9">
                    <div class="tab-content no-padding" id="myTab2Content">
                        <div class="tab-pane fade show active" id="about" role="tabpane1" aria-labelledby="about-tab">
                            <div class="card">
                                <div class="card-header">
                                    {{__('Banners')}}
                                </div>
                                <div class="card-body col-md-10 middle">
                                    <form method="post" action="{{route('admin.banners.update')}}" enctype="multipart/form-data">
                                        @csrf
                                        @method('put')
                                            <div class="form-group">
                                            <label for="logo">{{ __('Login Banner') }} {{__('(320x520)')}}</label>
                                            <div class="form-group">
                                                <div class="input-group gallery-modal" id="btnSubmit"  data-for="image" data-selection="single"
                                                     data-target="#galleryModal" data-dismiss="modal">
                                                    <input type="hidden" name="login_banner" value="{{@settingHelper('login_banner')['id']}}" class="image-selected">
                                                    <span class="form-control"><span class="counter">{{ @settingHelper('login_banner')['id'] ? 1 : 0 }}</span> {{ __('file chosen') }}</span>
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">
                                                            {{ __('Choose File') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="selected-media-box">
                                                    <div class="mt-4 gallery gallery-md d-flex">
                                                        <div class="selected-media mt-0 m-2" data-id="{{ @settingHelper('login_banner')['id'] }}">
                                                            @if (@settingHelper('login_banner')['images'] != [] && @is_file_exists(@settingHelper('login_banner')['images']['image_72x72'], @settingHelper('login_banner')['images']['storage']))
                                                                <img src="{{ get_media(settingHelper('login_banner')['images']['image_72x72'], settingHelper('login_banner')['images']['storage']) }}" alt=""
                                                                     class="img-thumbnail logo-profile">
                                                            @else
                                                                <img src="{{ static_asset('images/default/default-image-72x72.png') }}" alt=""
                                                                     class="img-thumbnail logo-profile">
                                                            @endif
                                                            @if(@settingHelper('login_banner')['id'] != null)
                                                                <div class="image-remove">
                                                                    <a href="javascript:void(0)" class="remove"><i class="bx bx-x"></i></a>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="logo">{{ __('SingUp Banner') }} {{__('(320x520)')}}</label>
                                            <div class="form-group">
                                                <div class="input-group gallery-modal" id="btnSubmit"  data-for="image" data-selection="single"
                                                     data-target="#galleryModal" data-dismiss="modal">
                                                    <input type="hidden" name="sing_up_banner" value="{{ @settingHelper('sing_up_banner')['id'] }}" class="image-selected">
                                                    <span class="form-control"><span class="counter">{{ @settingHelper('sing_up_banner')['id'] ? 1 : 0 }}</span> {{ __('file chosen') }}</span>
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">
                                                            {{ __('Choose File') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="selected-media-box">
                                                    <div class="mt-4 gallery gallery-md d-flex">
                                                        <div class="selected-media mt-0 m-2" data-id="{{ @settingHelper('sing_up_banner')['id'] }}">
                                                            @if (@settingHelper('sing_up_banner')['images'] != [] && @is_file_exists(@settingHelper('sing_up_banner')['images']['image_72x72'], @settingHelper('sing_up_banner')['images']['storage']))
                                                                <img src="{{ @get_media(settingHelper('sing_up_banner')['images']['image_72x72'], @settingHelper('sing_up_banner')['images']['storage']) }}" alt=""
                                                                     class="img-thumbnail logo-profile">
                                                            @else
                                                                <img src="{{ static_asset('images/default/default-image-72x72.png') }}" alt=""
                                                                     class="img-thumbnail logo-profile">
                                                            @endif
                                                            @if(@settingHelper('sing_up_banner')['id'] != null)
                                                                <div class="image-remove">
                                                                    <a href="javascript:void(0)" class="remove"><i class="bx bx-x"></i></a>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
{{--                                        @if(settingHelper('seller_system') == 1)--}}
{{--                                            <div class="form-group">--}}
{{--                                            <label for="logo">{{ __('Seller SingUp Banner') }} {{__('(320x852)')}}</label>--}}
{{--                                            <div class="form-group">--}}
{{--                                                <div class="input-group gallery-modal" id="btnSubmit"  data-for="image" data-selection="single"--}}
{{--                                                     data-target="#galleryModal" data-dismiss="modal">--}}
{{--                                                    <input type="hidden" name="seller_sing_up_banner" value="{{ @settingHelper('seller_sing_up_banner')['id'] }}" class="image-selected">--}}
{{--                                                    <span class="form-control"><span class="counter">{{ @settingHelper('seller_sing_up_banner')['id'] ? 1 : 0 }}</span> {{ __('file chosen') }}</span>--}}
{{--                                                    <div class="input-group-prepend">--}}
{{--                                                        <div class="input-group-text">--}}
{{--                                                            {{ __('Choose File') }}--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                                <div class="selected-media-box">--}}
{{--                                                    <div class="mt-4 gallery gallery-md d-flex">--}}
{{--                                                        <div class="selected-media mt-0 m-2" data-id="{{ @settingHelper('seller_sing_up_banner')['id'] }}">--}}
{{--                                                            @if (@settingHelper('seller_sing_up_banner')['images'] != [] && @is_file_exists(@settingHelper('seller_sing_up_banner')['images']['image_72x72'], @settingHelper('seller_sing_up_banner')['images']['storage']))--}}
{{--                                                                <img src="{{ @get_media(settingHelper('seller_sing_up_banner')['images']['image_72x72'], @settingHelper('seller_sing_up_banner')['images']['storage']) }}" alt=""--}}
{{--                                                                     class="img-thumbnail logo-profile">--}}
{{--                                                            @else--}}
{{--                                                                <img src="{{ static_asset('images/default/default-image-72x72.png') }}" alt=""--}}
{{--                                                                     class="img-thumbnail logo-profile">--}}
{{--                                                            @endif--}}
{{--                                                            @if(@settingHelper('seller_sing_up_banner')['id'] != null)--}}
{{--                                                                <div class="image-remove">--}}
{{--                                                                    <a href="javascript:void(0)" class="remove"><i class="bx bx-x"></i></a>--}}
{{--                                                                </div>--}}
{{--                                                            @endif--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        @endif--}}
                                        <div class="form-group">
                                            <label for="logo">{{ __('Forgot Password Banner') }} {{__('(320x520)')}}</label>
                                            <div class="form-group">
                                                <div class="input-group gallery-modal" id="btnSubmit"  data-for="image" data-selection="single"
                                                     data-target="#galleryModal" data-dismiss="modal">
                                                    <input type="hidden" name="forgot_password_banner" value="{{ @settingHelper('forgot_password_banner')['id'] }}" class="image-selected">
                                                    <span class="form-control"><span class="counter">{{ @settingHelper('forgot_password_banner')['id'] ? 1 : 0 }}</span> {{ __('file chosen') }}</span>
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">
                                                            {{ __('Choose File') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="selected-media-box">
                                                    <div class="mt-4 gallery gallery-md d-flex">
                                                        <div class="selected-media mt-0 m-2" data-id="{{ @settingHelper('forgot_password_banner')['id'] }}">
                                                            @if (@settingHelper('forgot_password_banner')['images'] != [] && @is_file_exists(@settingHelper('forgot_password_banner')['images']['image_72x72'], @settingHelper('forgot_password_banner')['images']['storage']))
                                                                <img src="{{ @get_media(settingHelper('forgot_password_banner')['images']['image_72x72'], @settingHelper('forgot_password_banner')['images']['storage']) }}" alt=""
                                                                     class="img-thumbnail logo-profile">
                                                            @else
                                                                <img src="{{ static_asset('images/default/default-image-72x72.png') }}" alt=""
                                                                     class="img-thumbnail logo-profile">
                                                            @endif
                                                            @if(@settingHelper('forgot_password_banner')['id'] != null)
                                                                <div class="image-remove">
                                                                    <a href="javascript:void(0)" class="remove"><i class="bx bx-x"></i></a>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right btn-margin">
                                            <button class="btn btn-outline-primary" id="save-btn">{{__('Update')}}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('admin.common.selector-modal')
@endsection

@section('style')
    <link rel="stylesheet" href="{{ static_asset('admin/css/dropzone.css') }}">
@endsection
@push('script')
    <script type="text/javascript" src="{{ static_asset('admin/js/dropzone.min.js') }}"></script>
@endpush
