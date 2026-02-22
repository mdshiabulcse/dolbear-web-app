{{-- Event Management Modal for Products --}}
<div class="modal fade" id="eventProductModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Event Management for Product') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="bx bx-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-md">
                        <thead>
                        <tr>
                            <th>{{ __('Event') }}</th>
                            <th>{{ __('Event Price') }}</th>
                            <th>{{ __('Discount') }}</th>
                            <th>{{ __('Priority') }}</th>
                            <th>{{ __('Active') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody id="event-products-body">
                            @if(isset($product) && $product->eventProducts)
                                @foreach ($product->eventProducts as $eventProduct)
                                    <tr data-event-product-id="{{ $eventProduct->id }}">
                                        <td>{{ $eventProduct->event->event_title }}</td>
                                        <td>
                                            @if ($eventProduct->event_price)
                                                {{ get_price($eventProduct->event_price) }}
                                            @else
                                                <span class="text-muted">{{ __('Original Price') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($eventProduct->discount_amount > 0)
                                                <span class="badge badge-danger">
                                                    @if ($eventProduct->discount_type == 'percentage')
                                                        {{ $eventProduct->discount_amount }}%
                                                    @else
                                                        {{ get_price($eventProduct->discount_amount) }}
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $eventProduct->product_priority }}</td>
                                        <td>
                                            <label class="custom-switch">
                                                <input type="checkbox"
                                                       class="custom-switch-input event-product-status"
                                                       data-event-product-id="{{ $eventProduct->id }}"
                                                       {{ $eventProduct->is_active ? 'checked' : '' }}>
                                                <span class="custom-switch-indicator"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info edit-event-product-btn"
                                                    data-event-product-id="{{ $eventProduct->id }}"
                                                    data-event-id="{{ $eventProduct->event_id }}"
                                                    data-event-price="{{ $eventProduct->event_price }}"
                                                    data-discount-amount="{{ $eventProduct->discount_amount }}"
                                                    data-discount-type="{{ $eventProduct->discount_type }}"
                                                    data-product-priority="{{ $eventProduct->product_priority }}"
                                                    data-is-active="{{ $eventProduct->is_active }}"
                                                    data-badge-text="{{ $eventProduct->badge_text }}"
                                                    data-badge-color="{{ $eventProduct->badge_color }}">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger remove-event-product-btn"
                                                    data-event-product-id="{{ $eventProduct->id }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        {{ __('No events assigned yet') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Add to Event Form --}}
                <div class="mt-4 pt-4 border-top">
                    <h6>{{ __('Add Product to Event') }}</h6>
                    <form id="addToEventForm" class="row">
                        @csrf
                        @if(isset($product))
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                        @endif
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('Select Event') }}</label>
                                <select name="event_id" class="form-control" required>
                                    <option value="">{{ __('Select Event') }}</option>
                                    @if(isset($events))
                                        @foreach ($events as $event)
                                            <option value="{{ $event->id }}">{{ $event->event_title }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Event Price') }}</label>
                                <input type="number" name="event_price" class="form-control" step="0.01"
                                       placeholder="{{ __('Optional') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ __('Discount') }}</label>
                                <input type="number" name="discount_amount" class="form-control" value="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{ __('Type') }}</label>
                                <select name="discount_type" class="form-control">
                                    <option value="flat">{{ __('Flat') }}</option>
                                    <option value="percentage">{{ __('%') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bx bx-plus"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Event Product Modal --}}
<div class="modal fade" id="editEventProductModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editEventProductForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Edit Event Product Settings') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="event_id" id="edit_event_id">
                    <input type="hidden" name="product_id" id="edit_product_id">

                    <div class="form-group">
                        <label>{{ __('Event Price') }}</label>
                        <input type="number" name="event_price" id="edit_event_product_price" class="form-control" step="0.01">
                        <small class="text-muted">{{ __('Leave empty to use original product price') }}</small>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Discount Amount') }}</label>
                        <input type="number" name="discount_amount" id="edit_discount_amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Discount Type') }}</label>
                        <select name="discount_type" id="edit_discount_type" class="form-control">
                            <option value="flat">{{ __('Flat') }}</option>
                            <option value="percentage">{{ __('Percentage') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Display Priority') }}</label>
                        <input type="number" name="product_priority" id="edit_product_priority" class="form-control" min="0">
                        <small class="text-muted">{{ __('Lower number = higher priority in event display') }}</small>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Badge Text') }}</label>
                        <input type="text" name="badge_text" id="edit_badge_text" class="form-control"
                               placeholder="{{ __('e.g., Hot Deal, Limited Time') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ __('Badge Color') }}</label>
                        <input type="color" name="badge_color" id="edit_badge_color" class="form-control" value="#ff0000">
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1" checked>
                            {{ __('Active in Event') }}
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
<script>
    $(document).ready(function () {
        // Add product to event
        $('#addToEventForm').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this);
            var eventId = $form.find('select[name="event_id"]').val();

            if (!eventId) {
                toastr.error('{{ __('Please select an event') }}');
                return;
            }

            $.ajax({
                url: '/admin/events/' + eventId + '/add-product',
                type: 'POST',
                data: $form.serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || '{{ __('Error adding product to event') }}');
                }
            });
        });

        // Toggle event product status
        $('.event-product-status').on('change', function () {
            var $checkbox = $(this);
            var eventProductId = $checkbox.data('event-product-id');
            var isActive = $checkbox.is(':checked') ? 1 : 0;
            var productId = $checkbox.closest('tr').data('event-product-id');
            var $row = $checkbox.closest('tr');

            // Find event ID from the row
            var eventId = null;
            @if(isset($product) && $product->eventProducts)
                @foreach($product->eventProducts as $ep)
                    if ($row.data('event-product-id') == {{ $ep->id }}) {
                        eventId = {{ $ep->event_id }};
                    }
                @endforeach
            @endif

            if (eventId) {
                $.ajax({
                    url: '/admin/events/' + eventId + '/products/' + productId,
                    type: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        is_active: isActive
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                        }
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || '{{ __('Error updating status') }}');
                        $checkbox.prop('checked', !isActive);
                    }
                });
            }
        });

        // Edit event product
        $('.edit-event-product-btn').on('click', function () {
            var $btn = $(this);
            var productId = $btn.data('product-id');
            var eventId = $btn.data('event-id');

            $('#editEventProductForm').attr('action', '/admin/events/' + eventId + '/products/' + productId);
            $('#edit_event_id').val(eventId);
            $('#edit_product_id').val(productId);
            $('#edit_event_product_price').val($btn.data('event-price'));
            $('#edit_discount_amount').val($btn.data('discount-amount'));
            $('#edit_discount_type').val($btn.data('discount-type'));
            $('#edit_product_priority').val($btn.data('product-priority'));
            $('#edit_badge_text').val($btn.data('badge-text'));
            $('#edit_badge_color').val($btn.data('badge-color'));
            $('#edit_is_active').prop('checked', $btn.data('is-active'));

            $('#editEventProductModal').modal('show');
        });

        // Update event product
        $('#editEventProductForm').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this);

            $.ajax({
                url: $form.attr('action'),
                type: 'PUT',
                data: $form.serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        $('#editEventProductModal').modal('hide');
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || '{{ __('Error updating product') }}');
                }
            });
        });

        // Remove product from event
        $('.remove-event-product-btn').on('click', function () {
            var $btn = $(this);
            var eventProductId = $btn.data('event-product-id');
            var $row = $btn.closest('tr');

            // Find event ID
            var eventId = null;
            var productId = null;
            @if(isset($product) && $product->eventProducts)
                @foreach($product->eventProducts as $ep)
                    if ($row.data('event-product-id') == {{ $ep->id }}) {
                        eventId = {{ $ep->event_id }};
                        productId = {{ $ep->product_id }};
                    }
                @endforeach
            @endif

            if (confirm('{{ __('Are you sure you want to remove this product from the event?') }}')) {
                $.ajax({
                    url: '/admin/events/' + eventId + '/remove-product',
                    type: 'DELETE',
                    data: {
                        product_id: productId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            $row.fadeOut(300, function () {
                                $(this).remove();
                            });
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || '{{ __('Error removing product') }}');
                    }
                });
            }
        });
    });
</script>
@endpush