@extends('admin.partials.master')
@section('title')
    {{ __('Cities') }}
@endsection
@section('shipping_active')
    active
@endsection
@section('shipping-classes')
    active
@endsection
@section('main-content')
    <section class="section city_section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __("Class Cities",['name' => $class->name]) }}</h2>
                    <p class="section-lead">
                        {{ __('You have total') . ' ' . $cities->total() . ' ' . __('City') }}
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="'col-8 col-md-8 col-lg-8 middle">
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-md city_table_font">
                                    <tbody>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('State') }}</th>
                                        <th>{{ __('Country') }}</th>
                                        <th>{{ __('Cost') }}</th>
                                        @if(hasPermission('city_update') || hasPermission('city_delete'))
                                            <th>{{ __('Option') }}</th>
                                        @endif
                                    </tr>
                                    @foreach($cities as $key => $value)
                                        <tr id="{{ $key }}">
                                            <td> {{ $cities->firstItem() + $key }} </td>
                                            <td> {{ $value->city->name }} </td>
                                            <td> {{ @$value->city->state->name }} </td>
                                            <td> {{ @$value->city->country->name }} </td>
                                            <td> {{ get_price($value->cost) }} </td>
                                            <td>
                                                @if(hasPermission('city_update'))
                                                    <a href="{{ route('class.city.edit', $value->id) }}"
                                                       class="btn btn-outline-secondary btn-circle"
                                                       data-toggle="tooltip" title=""
                                                       data-original-title="{{ __('Edit') }}"><i class="bx bx-edit"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <nav class="d-inline-block">
                                {{ $cities->appends(Request::except('page'))->links('pagination::bootstrap-4') }}
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@include('admin.common.delete-ajax')

@push('script')
    <script type="text/javascript" src="{{ static_asset('admin/js/ajax-live-search.js') }}"></script>
@endpush