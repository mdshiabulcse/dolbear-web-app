@extends('admin.partials.master')

@section('title')
    Create From Fan Message
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
                    <h2 class="section-title">Create Form Fan Message</h2>
                </div>
                
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Give Info</h4>
                        </div>
                        <div class="card-body">
                            
                            <form action="{{ route('flash-message.update') }}" method="post" enctype="multipart/form-data">
                                @csrf
                               
                                <div class="form-group row mb-4">
                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" for="title">Name *</label>
                                    <div class="col-sm-12 col-md-7">
                                        
                                        <input style="display: none;" type="text" name="id" id="id" value="{{ old('id') ? old('id') : $message->id }}" >
                                        <input type="text" name="name" id="name" value="{{ old('name') ? old('name') : $message->name }}" class="form-control">

                                        @if ($errors->has('name'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('name') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group row mb-4">
                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" for="meta_description">Description</label>
                                    <div class="col-sm-12 col-md-7">
                                        <textarea name="description" class="form-control" id="description">{{ old('description') ? old('description') : $message->description }}</textarea>
                                        @if ($errors->has('description'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('description') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" for="zone">Status</label>
                                    <div class="col-sm-12 col-md-7">
                                        <select class="form-control select2" name="status" id="status">
                                            <option selected value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
    
                                        @if ($errors->has('status'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('status') }}</p>
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
