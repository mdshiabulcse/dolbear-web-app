@extends('admin.partials.master')
@section('title')
    {{ __('Division') }}
@endsection
@section('shipping_active')
    active
@endsection
@section('available-countries')
    active
@endsection
@php
    $q              = null;
    if(isset($_GET['q'])){
        $q          = $_GET['q'];
    }
@endphp
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Divisions') }}</h2>
                    
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('All Divisions') }}</h4>
                            <div class="card-header-form">
                                <form class="form-inline" id="sorting">

                                    <div class="input-group">
                                        <input type="text" class="form-control" name="q" value="{{ $q != null ? $q : "" }}" placeholder="{{ __('Search') }}">
                                        <div class="input-group-btn">
                                            <button class="btn btn-outline-primary"><i class="bx bx-search"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-md">
                                    <tbody>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                    @foreach($divisions as $key => $value)
                                        <tr id="{{ $key }}">
                                            <td> {{ $divisions->firstItem() + $key  }} </td>
                                            <td> {{ $value->name }} </td>
                                            
                                            @if ( $value->status == 1)
                                                <td><span class="badge badge-success">{{ __('Active') }}</span></td>
                                            @else
                                                <td><span class="badge badge-danger">{{ __('Inactive') }}</span></td>
                                            @endif
                                            

                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <nav class="d-inline-block">
                                {{ $divisions->appends(Request::except('page'))->links('pagination::bootstrap-4') }}
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection @include('admin.common.delete-ajax')
