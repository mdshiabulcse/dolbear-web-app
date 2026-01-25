@extends('admin.partials.master')
@section('title')
    {{ __('Subscribers') }}
@endsection
@section('marketing_active')
    active
@endsection
@section('subscriber')
    active
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Subscribers') }}</h2>
                    <p class="section-lead">
                        {{ __('You have total') . ' ' . $subscriber->total() . ' ' . __('subscribers') }}
                    </p>
                </div>
                <div class="d-block">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendMailModal">
                        <i class="bx bx-envelope"></i> {{ __('Send Mail') }}
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Subscribers') }}</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-md">
                                    <tbody>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Subscribe At') }}</th>
                                        @if(hasPermission('subscriber_delete'))
                                        <th>{{ __('Option') }}</th>
                                        @endif
                                    </tr>
                                    @foreach($subscriber as $key => $value)
                                        <tr id="{{ $key }}">
                                            <td> {{ $subscriber->firstItem() + $key }} </td>
                                            <td>{{ config('app.demo_mode') ? emailAddressMask($value->email) : $value->email }}</td>
                                            <td> {{ date('M y, Y h:i a', strtotime($value->created_at)) }}</td>
                                            <td>
                                                @if(hasPermission('subscriber_delete'))
                                                <a href="javascript:void(0)" onclick="delete_row('delete/subscribers/', {{ $value->id }})"
                                                    class="btn btn-outline-danger btn-circle" data-toggle="tooltip" title="" data-original-title="{{ __('Unsubscribe') }}">
                                                     <i class='bx bx-trash'></i>
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
                                {{ $subscriber->appends(Request::except('page'))->links('pagination::bootstrap-4') }}
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Send Mail Modal -->
    <div class="modal fade" id="sendMailModal" tabindex="-1" role="dialog" aria-labelledby="sendMailModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendMailModalLabel">{{ __('Send Mail to Subscribers') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="sendMailForm" action="{{ route('subscriber.send.mail') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="subject">{{ __('Subject') }}</label>
                            <input type="text" class="form-control" id="subject" name="subject" required placeholder="{{ __('Enter email subject') }}">
                        </div>
                        <div class="form-group">
                            <label for="message">{{ __('Message') }}</label>
                            <textarea class="form-control" id="message" name="message" rows="6" required placeholder="{{ __('Enter your message') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-send"></i> {{ __('Send Mail') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('sendMailForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            var formData = new FormData(form);
            var submitBtn = form.querySelector('button[type="submit"]');
            var originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bx bx-loader bx-spin"></i> {{ __('Sending...') }}';

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.success);
                    form.reset();
                    var modal = bootstrap.Modal.getInstance(document.getElementById('sendMailModal'));
                    modal.hide();
                } else if (data.error) {
                    toastr.error(data.error);
                }
            })
            .catch(error => {
                toastr.error('{{ __('Something went wrong') }}');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    </script>
@endsection @include('admin.common.delete-ajax')
