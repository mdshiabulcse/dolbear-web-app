@extends('admin.partials.master')

@section('title')
From Fan Messages
@endsection
@section('flash_message_active')
    active
@endsection
@section('all_flash_message_active')
    active
@endsection


@section('main-content')

    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">From Fan Messages
                    </h2>
                </div>
                    <div class="buttons add-button">
                        <a href="{{ route('flash-message.create') }}" class="btn btn-icon icon-left btn-outline-primary">
                            <i class='bx bx-plus'></i>Add From Fan Messages

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
                                        <th>Rating</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>

                                    @foreach ($flashMessage as $key => $item)

                                        <tr>
                                            <td>{{ $flashMessage->firstItem() + $key }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td title="{{ $item->description }}">{{ \Illuminate\Support\Str::limit($item->description, 30, '...') }}</td>
                                            <td>
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $item->rating)
                                                        <i class="bx bxs-star text-warning"></i>
                                                    @else
                                                        <i class="bx bx-star text-muted"></i>
                                                    @endif
                                                @endfor
                                                <span class="ml-1">{{ $item->rating }}/5</span>
                                            </td>
                                            <td>
                                                @if ($item->status == 1)
                                                    <span class="badge badge-pill badge-success">Success</span>
                                                @else
                                                    <span class="badge badge-pill badge-danger">Danger</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('flash-message.edit', ['id' => $item->id]) }}"
                                                    class="btn btn-outline-secondary btn-circle"
                                                    data-toggle="tooltip" title=""
                                                    data-original-title="{{ __('Edit') }}"><i
                                                        class="bx bx-edit"></i></a>

                                                <a href="javascript:void(0)"
                                                onclick="delete_row('delete/from-fan-message/', {{ $item->id }})"
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
                                {{ $flashMessage->appends(Request::except('page'))->links('pagination::bootstrap-4') }}
                            </nav>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@include('admin.common.delete-ajax')
