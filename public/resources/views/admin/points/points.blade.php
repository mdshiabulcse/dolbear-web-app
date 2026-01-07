@extends('admin.partials.master')
@section('title')
    {{ "Point Setting" }}
@endsection
@section('point_setting_active')
    active
@endsection
@section('point_list_active')
    active
@endsection
@section('main-content')

<section class="section">
   <div class="section-body">
    <div class="d-flex justify-content-between">
        <div class="d-block">
            <h2 class="section-title">Point Settings View</h2>
            <p class="section-lead">
                {{ __('You have total') . ' ' . $points->total() . ' ' . 'records' }}
            </p>
        </div>
    </div>
        <div class="row">
            <div class="col-{{ hasPermission('color_create') ? 'col-sm-xs-12 col-md-7' : 'col-sm-xs-12 col-md-8 middle' }}">
                <div class="card">
                    <form action="">
                        <div class="card-header input-title">
                            <h4>Point Settings Table</h4>
                        </div>
                    </form>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-md">
                                <tbody>
                                <tr>
                                    <th>{{ __('#') }}</th>
                                    <th>Point</th>
                                    <th>Point to money</th>
                                    <th>Status</th>
                                    {{-- @if (hasPermission('color_update') || hasPermission('color_delete')) --}}
                                    {{-- <th>{{ __('Options') }}</th> --}}
                                    {{-- @endif --}}
                                </tr>
                                @foreach($points as $key=>$point)
                                <tr>
                                    <td>{{ $points->firstItem() + $key }}</td>
                                    <td>
                                        <div class="d-flex">
                                            <div class="ml-1">{{  $point->point  }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <div class="ml-1">{{ $point->point_to_money }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            @if($point->status == 1)
                                            <div class="ml-1">Active</div>
                                            @else
                                            <div class="ml-1">Inactive</div>
                                            @endif
                                        </div>
                                    </td>
                                    
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <nav class="d-inline-block">
                            {{ $points->appends(Request::except('page'))->links('pagination::bootstrap-4') }}
                        </nav>
                    </div>
                </div>
            </div>

            <div class="col-sm-xs-12 col-md-5">
                <div class="card">
                    <div class="card-header input-title" id="Add">
                        <h4>Point Settings Update</h4>
                    </div>
                    <div class="card-body card-body-paddding">
                        <form method="post" action="{{ route('point.store') }}">
                            @csrf
                            <div class="form-group">
                                <label for="point">Point</label>
                                <input id="point" type="text" class="form-control" name="point" placeholder="Point" tabindex="1"
                                    required autofocus>
                                   
                             </div>
                            <div class="form-group">
                                <label for="point_to_money">Point to money</label>
                                    <input id="point_to_money" type="point_to_money" class="form-control" name="point_to_money" id="point_to_money" placeholder="Point to money">
                               
                            </div>
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-outline-primary" tabindex="4">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
   </div>
</section>

@endsection

@include('admin.common.delete-ajax')
