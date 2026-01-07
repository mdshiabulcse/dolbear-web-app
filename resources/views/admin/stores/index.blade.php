@extends('admin.partials.master')

@section('store_active')
    active
@endsection

@section('all_store_active')
    active
@endsection

@section('title')
    All Stores
@endsection

@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">
                        Stores
                    </h2>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-sm-xs-12 col-md-12">
                    <div class="card">
                        
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-md">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stores as $key => $item)
                                            
                                        <tr>
                                            {{-- <td>{{ $item->firstItem() + $key }}</td> --}}
                                            <td>{{ $stores->firstItem() + $key }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->address }}</td>
                                            <td>
                                                <a href="{{ route('store.edit', ['id' => $item->id]) }}"
                                                    class="btn btn-outline-secondary btn-circle"
                                                    data-toggle="tooltip" title=""
                                                    data-original-title="{{ __('Edit') }}"><i
                                                        class="bx bx-edit"></i></a>

                                                <a href="javascript:void(0)"
                                                    onclick="delete_row('delete/stores/', {{ $item->id }})"
                                                    class="btn btn-outline-danger btn-circle"
                                                    data-toggle="tooltip" title=""
                                                    data-original-title="{{ __('Permanent Delete') }}">
                                                    <i class='bx bx-trash'></i>


                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <nav class="d-inline-block">
                                {{ $stores->appends(Request::except('page'))->links('pagination::bootstrap-4') }}
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@include('admin.common.delete-ajax')

