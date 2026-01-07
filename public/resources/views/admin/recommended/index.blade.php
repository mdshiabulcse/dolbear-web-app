@extends('admin.partials.master')

@section('title')
    Recommended
@endsection
@section('recommended_active')
    active
@endsection
@section('all_recommended_active')
    active
@endsection


@section('main-content')

    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">Recommended</h2>
                </div>
                    <div class="buttons add-button">
                        <a href="{{ route('recommended.create') }}" class="btn btn-icon icon-left btn-outline-primary">
                            <i class='bx bx-plus'></i>Add Recommended
                        </a>
                    </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="clearfix mb-3"></div>

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tr>
                                        <th>{{__('#')}}</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                        
                                        
                                    </tr>

                                    @foreach ($recommendation as $key => $item)

                                        <tr>
                                            <td>{{ $recommendation->firstItem() + $key }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td title="{{ $item->description }}">{{ \Illuminate\Support\Str::limit($item->description, 30, '...') }}</td>

                                            <td><a href="javascript:void(0)"
                                                onclick="delete_row('delete/recommended/', {{ $item->id }})"
                                                class="btn btn-outline-danger btn-circle"
                                                data-toggle="tooltip" title=""
                                                data-original-title="{{ __('Permanent Delete') }}">
                                                <i class='bx bx-trash'></i>
                                            </a></td>
                                            
                                        </tr>

                                    @endforeach
                                    
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <nav class="d-inline-block">
                                {{ $recommendation->appends(Request::except('page'))->links('pagination::bootstrap-4') }}
                            </nav>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@include('admin.common.delete-ajax')
