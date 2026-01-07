<table class="table table-striped table-bordered product-variant-table">
    @if(isset($variants))
        <thead>
        <tr>
            <td scope="col">{{ __('Variant') }}</td>
            <td scope="col">{{ __('Price') }} *</td>
            <td scope="col">{{ __('SKU') }} *</td>
            <td scope="col">Store & Stock*</td>
            {{-- <td scope="col">{{ __('Current Stock') }} *</td> --}}
            <td scope="col">{{ __('Image') }}</td>
            <td>{{ __('Action') }}</td>
        </tr>
        </thead>
        <tbody>

        @foreach ($variants_data as $index => $variant)
            @php
                $variant_name = '';
                $variant_ids = '';

                $stores = \App\Models\Store::all();

                $product = \App\Models\Product::latest()->first();
                $product_id = 1;
                    if($product){
                        $product_id = $product->id + 1;
                    }
                foreach ($variant as $key => $item){
                    if($key > 0 ){
                        $attribute_value = \App\Models\AttributeValues::find($item);
                        $variant_name .= '-'.str_replace(' ', '', $attribute_value->value);
                        $variant_ids .= '-'.str_replace(' ', '', $attribute_value->id);
                    }
                    else{
                        if($colors == 1){
                            $color_all = \App\Models\Color::where('id', $item)->first()->colorLanguages()->where('lang','en')->get();
                            foreach ($color_all as $color){
                                $color_name = $color->name;
                                $color_id = $color->id;
                                continue;
                            }
                            $variant_name .= $color_name;
                            $variant_ids .= $item;
                        }
                        else{
                            $attribute_value = \App\Models\AttributeValues::find($item);
                            $variant_name .= str_replace(' ', '', $attribute_value->value);
                            $variant_ids .= str_replace(' ', '', $attribute_value->id);
                        }
                    }
                }
                $variant_title = $variant_name;
                $variant_name .= '-'.$product_id;
            @endphp
            @if(strlen($variant_name) > 0)
                @foreach ($stores as $key => $store)
                <tr data-stock="stock-{{ $variant_ids }}">
                    <th scope="row" width="14%"><label class="font-normal">{{ $variant_title }}</label><input
                                type="hidden" lang="en" name="variant_name[]" value="{{ $variant_title }}"
                                class="form-control">
                        <input type="hidden" lang="en" name="variant_ids[]" value="{{ $variant_ids }}"
                               class="form-control variant-id">
                    </th>
                    <td width="14%">
                        @if ($key === 0)
                            <input type="number" lang="en" name="variant_price[]" value="0" min="0" step="any" class="form-control variant-price-input" data-variant-id="{{ $variant_ids }}" oninput="syncVariantPrices(this)">
                        @else
                        <input type="number" lang="en" name="variant_price[]" value="0" min="0" step="any"
                            class="variant-price-hidden" data-variant-id="{{ $variant_ids }}"  style="display: none;">

                        @endif
                    </td>
                    <td width="14%">
                        <input type="text" name="variant_sku[]" value="{{ $variant_name }}" class="form-control">
                        @if ($errors->has('variant_sku.'.$index))
                            <div class="invalid-feedback">
                                <p>{{ $errors->first('variant_sku.'.$index) }}</p>
                            </div>
                        @endif
                    </td>
                    {{-- store --}}
                    <td width="14%">



                                <h6><span>{{ $store->name }}</span> </h6>

                                <input type="number"
                                    name="variant_store[]"
                                    value="{{ $store->id }}"
                                    id="store"
                                    hidden
                                    >
                                    @if ($errors->has('variant_store.'.$index))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('variant_store.'.$index) }}</p>
                                        </div>
                                    @endif


                                    <input type="number" lang="en" name="variant_stock[]" value="" min="0" step="1"
                                        class="form-control">
                                    @if ($errors->has('variant_stock.'.$index))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('variant_stock.'.$index) }}</p>
                                        </div>
                                    @endif
                    </td>

                    <td width="22%">
                        <div>
                            @if($key === 0)
                                <div class="form-group">
                                <div class="input-group gallery-modal" id="btnSubmit" data-for="image" data-variant="1"
                                     data-selection="single"
                                     data-target="#galleryModal" data-dismiss="modal">
                                    <input type="hidden" name="variant_image[]" value="" class="image-selected">
                                    <span class="form-control"><span class="counter">0</span> {{ __('file') }}</span>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            {{ __('Choose') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="selected-media-box">

                                </div>
                            </div>
                            @else
                                <div style="visibility: hidden" class="form-group">
                                    <div class="input-group gallery-modal" id="btnSubmit" data-for="image" data-variant="1"
                                         data-selection="single"
                                         data-target="#galleryModal" data-dismiss="modal">
                                        <input type="hidden" name="variant_image[]" value="" class="image-selected">
                                        <span class="form-control"><span class="counter">0</span> {{ __('file') }}</span>
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                {{ __('Choose') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="selected-media-box">

                                    </div>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td width="6%">
                        @if ($key === 0)
                        <button data-stock="stock-{{ $variant_ids }}" type="button" class="btn btn-icon btn-sm btn-danger remove-menu-row remove-variant-row remove-variant-row"><i class="bx bx-trash"></i></button>
                        @endif
                    </td>
                </tr>
                @endforeach
            @endif
        @endforeach
        </tbody>
    @endif
</table>

<script>
    function syncVariantPrices(visibleInput) {
        const value = visibleInput.value;
        const variantId = visibleInput.dataset.variantId;

        document.querySelectorAll(`input[data-variant-id="${variantId}"]`).forEach(input => {
            input.value = value;
        });
    }
</script>

<script>
    $(document).ready(function() {
        $('.remove-variant-row').click(function() {
            const variantId = $(this).data('stock');
            $('tr[data-stock="' + variantId + '"]').remove();
        });
    });
</script>

