@extends('admin.partials.master')
@section('title')
    {{ __('Import Products') }}
@endsection
@section('product_active')
    active
@endsection
@section('product_import')
    active
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Import Products') }}</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-xs-12 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Import Products') }}</h4>
                        </div>
                        <div class="card-body col-sm-xs-12">
                            <form method="POST" action="{{ route('admin.product.import.post') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="file">{{ __('Import File') }} <span class="text-danger">*</span></label>
                                    <small class="form-text text-muted d-block mb-2">{{ __('.csv/.xlsx/.xls File') }}</small>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input"
                                               accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" name="file" id="customFile"/>
                                        <label class="custom-file-label" for="customFile">{{ __('Choose file...') }}</label>
                                    </div>
                                    @if($errors && $errors->any())
                                        @foreach($errors->all() as $error)
                                            <div class="nk-block-des text-danger">
                                                <p>{{ $error }}</p>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary" tabindex="4">
                                        <i class="bx bx-upload"></i> {{ __('Import Products') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-sm-xs-12 col-md-6">
                    <div class="card">
                        <div class="card-header input-title">
                            <h4>{{ __('Product Import Procedures') }}</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-info"><i class="bx bx-info-circle"></i> {{ __('Please follow these instructions before importing your file') }}:</p>
                            <ol>
                                <li>{{ __('Supported file types') }}: <strong>.csv, .xlsx, .xls</strong></li>
                                <li>{{ __('Required columns') }}: <span class="badge badge-primary">name</span>, <span class="badge badge-primary">category_id</span>, <span class="badge badge-primary">price</span>, <span class="badge badge-primary">unit</span>, <span class="badge badge-primary">current_stock</span></li>
                                <li>{{ __('Optional columns') }}: brand_id, slug, barcode, sku, minimum_order_quantity (defaults to 1), tags, video_provider (youtube/vimeo/mp4), video_url, is_approved (0/1), is_catalog (0/1), external_link, is_refundable (0/1), cash_on_delivery (0/1), short_description, description, meta_title, meta_description</li>
                            </ol>

                            <div class="alert alert-info">
                                <strong>{{ __('Tips') }}:</strong>
                                <ul class="mb-0 pl-3">
                                    <li>{{ __('Download category & brand lists to get correct IDs') }}</li>
                                    <li>{{ __('Use the sample CSV file as a template') }}</li>
                                    <li>{{ __('Make sure category_id and brand_id exist in your system') }}</li>
                                </ul>
                            </div>

                            <a href="{{asset('excel/product-import-sample.csv')}}" target="_blank" class="btn btn-outline-primary btn-block">
                                <i class="bx bx-download"></i> {{ __('Download Sample CSV') }}
                            </a>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header input-title">
                            <h4>{{ __('Category & Brand List with ID') }}</h4>
                        </div>
                        <div class="card-body d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.list.download','category') }}" target="_blank" class="btn btn-outline-info">
                                <i class="bx bx-download"></i> {{ __('Categories') }}
                            </a>
                            <a href="{{ route('admin.list.download','brand') }}" target="_blank" class="btn btn-outline-info">
                                <i class="bx bx-download"></i> {{ __('Brands') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('CSV Column Reference') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Column Name') }}</th>
                                            <th>{{ __('Required') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Example') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>name</code></td>
                                            <td><span class="badge badge-danger">{{ __('Yes') }}</span></td>
                                            <td>{{ __('Product name') }}</td>
                                            <td>Sample Product</td>
                                        </tr>
                                        <tr>
                                            <td><code>category_id</code></td>
                                            <td><span class="badge badge-danger">{{ __('Yes') }}</span></td>
                                            <td>{{ __('Category ID from system') }}</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <td><code>brand_id</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Brand ID from system') }}</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <td><code>price</code></td>
                                            <td><span class="badge badge-danger">{{ __('Yes') }}</span></td>
                                            <td>{{ __('Selling price (numeric)') }}</td>
                                            <td>150.00</td>
                                        </tr>
                                        <tr>
                                            <td><code>purchase_cost</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Cost price (numeric)') }}</td>
                                            <td>100.00</td>
                                        </tr>
                                        <tr>
                                            <td><code>sku</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Stock Keeping Unit (can be numeric or text)') }}</td>
                                            <td>SKU001, 12345</td>
                                        </tr>
                                        <tr>
                                            <td><code>unit</code></td>
                                            <td><span class="badge badge-danger">{{ __('Yes') }}</span></td>
                                            <td>{{ __('Unit of measurement') }}</td>
                                            <td>Piece, KG, Pc</td>
                                        </tr>
                                        <tr>
                                            <td><code>current_stock</code></td>
                                            <td><span class="badge badge-danger">{{ __('Yes') }}</span></td>
                                            <td>{{ __('Available stock quantity') }}</td>
                                            <td>50</td>
                                        </tr>
                                        <tr>
                                            <td><code>minimum_order_quantity</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Minimum order quantity (defaults to 1 if empty)') }}</td>
                                            <td>1, 5, 10 (or leave empty)</td>
                                        </tr>
                                        <tr>
                                            <td><code>slug</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('URL slug (auto-generated if empty)') }}</td>
                                            <td>sample-product</td>
                                        </tr>
                                        <tr>
                                            <td><code>barcode</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Product barcode') }}</td>
                                            <td>123456789</td>
                                        </tr>
                                        <tr>
                                            <td><code>tags</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Comma separated tags') }}</td>
                                            <td>tag1,tag2,tag3</td>
                                        </tr>
                                        <tr>
                                            <td><code>is_approved</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Auto-approved for admin (0/1)') }}</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <td><code>is_catalog</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Is catalog product (0/1)') }}</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <td><code>external_link</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('External link (required if is_catalog=1)') }}</td>
                                            <td>https://example.com</td>
                                        </tr>
                                        <tr>
                                            <td><code>is_refundable</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Is refundable (0/1)') }}</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <td><code>cash_on_delivery</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Allow COD (0/1)') }}</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <td><code>short_description</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Short product description') }}</td>
                                            <td>Great product</td>
                                        </tr>
                                        <tr>
                                            <td><code>description</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Full product description') }}</td>
                                            <td>Detailed specs...</td>
                                        </tr>
                                        <tr>
                                            <td><code>video_provider</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Video provider: youtube/vimeo/mp4') }}</td>
                                            <td>youtube</td>
                                        </tr>
                                        <tr>
                                            <td><code>video_url</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('Video URL (required if video_provider set)') }}</td>
                                            <td>https://youtube.com/watch?v=xxx</td>
                                        </tr>
                                        <tr>
                                            <td><code>meta_title</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('SEO meta title') }}</td>
                                            <td>Product Title</td>
                                        </tr>
                                        <tr>
                                            <td><code>meta_description</code></td>
                                            <td><span class="badge badge-secondary">{{ __('No') }}</span></td>
                                            <td>{{ __('SEO meta description') }}</td>
                                            <td>Product description</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@push('script')
    <script>
        // Show selected file name
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });
    </script>
@endpush
@endsection
