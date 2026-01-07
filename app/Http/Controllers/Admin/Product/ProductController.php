<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Requests\Admin\Product\ProductUpdateRequest;
use Carbon\Carbon;
use App\Models\City;
use App\Models\Store;
use App\Models\ProductCity;
use App\Models\ReviewReply;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use App\Models\ShippingClass;
use App\Utility\VariantUtility;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Admin\VatTaxRepository;
use App\Repositories\Interfaces\Admin\StoreInterface;
use App\Repositories\Interfaces\Site\ReviewInterface;
use App\Repositories\Interfaces\Admin\SellerInterface;
use App\Http\Requests\Admin\Product\ProductStoreRequest;
use App\Repositories\Interfaces\Admin\LanguageInterface;
use App\Repositories\Admin\Addon\ShippingClassRepository;
use App\Repositories\Interfaces\Admin\Product\BrandInterface;
use App\Repositories\Interfaces\Admin\Product\ColorInterface;
use App\Repositories\Interfaces\Admin\Product\ProductInterface;
use App\Repositories\Interfaces\Admin\Product\CategoryInterface;
use App\Repositories\Interfaces\Admin\Product\AttributeInterface;

class ProductController extends Controller
{
    protected $products;
    protected $categories;
    protected $brands;
    protected $colors;
    protected $attributes;
    protected $vat_tax;
    protected $languages;
    protected $sellers;
    protected $store;

    public function __construct(
        ProductInterface $products,
        CategoryInterface $categories,
        BrandInterface $brands,
        ColorInterface $colors,
        AttributeInterface $attributes,
        VatTaxRepository $vat_tax,
        SellerInterface $sellers,
        LanguageInterface $languages,
        StoreInterface $store
    ) {
        $this->products         = $products;
        $this->categories       = $categories;
        $this->brands           = $brands;
        $this->colors           = $colors;
        $this->attributes       = $attributes;
        $this->vat_tax          = $vat_tax;
        $this->languages        = $languages;
        $this->sellers          = $sellers;
        $this->store            = $store;
    }
    public function index(Request $request, $status = null)
    {
        try {
            $products           = $this->products->paginate($request, $status, get_pagination('pagination'), '');

            $selected_category  = isset($request->c) ? $this->categories->get($request->c) : null;
            $selected_seller    = isset($request->sl) ? $this->sellers->getSeller($request->sl) : null;

            return view('admin.products.products.index', compact('status', 'products', 'selected_category', 'selected_seller'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function adminProducts(Request $request, $status = null)
    {
        try {
            $products       = $this->products->paginate($request, $status, get_pagination('pagination'), 'admin');
            $sellers        = $this->sellers->all()->where('is_user_banned', 0)->where('status', 1)->get();

            $selected_category  = isset($request->c) ? $this->categories->get($request->c) : null;

            return view('admin.products.products.admin-products', compact('status', 'products', 'selected_category', 'sellers'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function sellerProducts(Request $request, $status = null)
    {
        try {
            $products           = $this->products->paginate($request, $status, get_pagination('pagination'), 'seller');

            $selected_category  = isset($request->c) ? $this->categories->get($request->c) : null;
            $selected_seller    = isset($request->sl) ? $this->sellers->getSeller($request->sl) : null;

            return view('admin.products.products.seller-products', compact('status', 'products', 'selected_category', 'selected_seller'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function digitalProducts(Request $request, $status = null)
    {
        try {
            $products           = $this->products->paginate($request, $status, get_pagination('pagination'), 'digital');

            $selected_category  = isset($request->c) ? $this->categories->get($request->c) : null;
            $selected_seller    = isset($request->sl) ? $this->sellers->getSeller($request->sl) : null;

            return view('admin.products.products.digital-products', compact('status', 'products', 'selected_category', 'selected_seller'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function catalogProducts(Request $request, $status = null)
    {
        try {
            $products       = $this->products->paginate($request, $status, \Config::get('yrsetting.paginate'), 'catalog');

            $selected_category  = isset($request->c) ? $this->categories->get($request->c) : null;
            $selected_seller    = isset($request->sl) ? $this->sellers->getSeller($request->sl) : null;

            return view('admin.products.products.catalog-products', compact('status', 'products', 'selected_category', 'selected_seller'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function classifiedProducts(Request $request, $status = null)
    {
        try {
            $products           = $this->products->paginate($request, $status, \Config::get('yrsetting.paginate'), 'classified');
            $selected_category  = isset($request->c) ? $this->categories->get($request->c) : null;
            $selected_seller    = isset($request->sl) ? $this->sellers->getSeller($request->sl) : null;
            return view('admin.products.products.classified-products', compact('status', 'products', 'selected_category', 'selected_seller'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function create(Request $request)
    {
        try {
            $data = [
                'categories' => $this->categories->allCategory()->where('parent_id', null)->where('status', 1),
                'stores' => $this->store->allStore(),
                'brands' => $this->brands->all()->where('lang', 'en')->where('status', 1)->get(),
                'colors' => $this->colors->all()->where('lang', 'en')->get(),
                'attributes' => $this->attributes->all()->where('lang', 'en')->get(),
                'campaigns' => \App\Models\Campaign::where('status', 1)->where('end_date', '>', Carbon::now()->format('Y-m-d'))->get(),
                'r' => $request->r != '' ? $request->r : $request->server('HTTP_REFERER')
            ];
            if (addon_is_activated('ramdhani')) {
                $repo = new ShippingClassRepository();
                $data['shipping_classes'] = $repo->activeClasses();
            }
            return view('admin.products.products.form', $data);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function createDigitalProduct(Request $request)
    {
        $data = [
            'categories'        => $this->categories->allCategory()->where('parent_id', null)->where('status', 1),
            'brands'            => $this->brands->all()->where('lang', 'en')->where('status', 1)->get(),
            'colors'            => $this->colors->all()->where('lang', 'en')->get(),
            'attributes'        => $this->attributes->all()->where('lang', 'en')->get(),
            'campaigns'         => \App\Models\Campaign::where('status', 1)->where('end_date', '>', Carbon::now()->format('Y-m-d'))->get(),
            'r'                 => $request->r != '' ? $request->r : $request->server('HTTP_REFERER'),
            'shipping_classes'  => [],
            'is_digital'        => 1
        ];

        return view('admin.products.products.form', $data);
    }
    public function createCatalogProduct(Request $request)
    {
        $data = [
            'categories'    => $this->categories->allCategory()->where('parent_id', null)->where('status', 1),
            'brands'        => $this->brands->all()->where('lang', 'en')->where('status', 1)->get(),
            'colors'        => $this->colors->all()->where('lang', 'en')->get(),
            'attributes'    => $this->attributes->all()->where('lang', 'en')->get(),
            'campaigns'     => \App\Models\Campaign::where('status', 1)->where('end_date', '>', Carbon::now()->format('Y-m-d'))->get(),
            'r'             => $request->r != '' ? $request->r : $request->server('HTTP_REFERER'),
            'is_catalog'    => 1
        ];

        return view('admin.products.products.form', $data);
    }
    public function createClassifiedProduct(Request $request)
    {
        $data = [
            'categories'    => $this->categories->allCategory()->where('parent_id', null)->where('status', 1),
            'brands'        => $this->brands->all()->where('lang', 'en')->where('status', 1)->get(),
            'colors'        => $this->colors->all()->where('lang', 'en')->get(),
            'attributes'    => $this->attributes->all()->where('lang', 'en')->get(),
            'campaigns'     => \App\Models\Campaign::where('status', 1)->where('end_date', '>', Carbon::now()->format('Y-m-d'))->get(),
            'r'             => $request->r != '' ? $request->r : $request->server('HTTP_REFERER'),
            'is_classified' => 1
        ];

        return view('admin.products.products.form', $data);
    }

    public function edit($id, Request $request)
    {
        try {
            // session()->forget('attributes');
            $languages  = $this->languages->all()->orderBy('id', 'asc')->get();

            $lang       = $request->lang != '' ? $request->lang : app()->getLocale();
            if ($this->products->get($id) && $product_language = $this->products->getByLang($id, $lang)) :
                $data = [
                    'categories'        => $this->categories->allCategory()->where('parent_id', null)->where('status', 1),
                    'stores'            => $this->store->allStore(),
                    'brands'            => $this->brands->all()->where('lang', 'en')->where('status', 1)->get(),
                    'colors'            => $this->colors->all()->where('lang', 'en')->get(),
                    'attributes'        => $this->attributes->all()->where('lang', 'en')->get(),
                    'campaigns'         => \App\Models\Campaign::where('status', 1)->where('end_date', '>', Carbon::now()->format('Y-m-d'))->get(),
                    'r'                 => $request->r != '' ? $request->r : $request->server('HTTP_REFERER'),
                    'languages'         => $languages,
                    'product_language'  => $product_language,
                    'lang'              => $lang
                ];
                if (addon_is_activated('ramdhani')) {
                    $repo = new ShippingClassRepository();
                    $data['shipping_classes'] = $repo->activeClasses();
                }
                return view('admin.products.products.edit', $data);

            else :
                Toastr::error(__('Not found'));
                return back();
            endif;
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function statusChange(Request $request)
    {
        if (config('app.demo_mode')) :
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;

        DB::beginTransaction();
        try {
            $this->products->statusChange($request['data']);
            $response['message']    = __('Updated Successfully');
            $response['title']      = __('Success');
            $response['status']     = 'success';
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function store(ProductStoreRequest $request)
    {
        if (config('app.demo_mode')) :
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;

        DB::beginTransaction();
        try {

            $this->products->store($request);
            Toastr::success(__('Created Successfully'));
            session()->forget('attributes');
            DB::commit();
            return redirect()->route('products');
        } catch (\Exception $e) {
            info($e);
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function update(ProductUpdateRequest $request)
    {
        session()->forget('attributes');
        if ($request->variant_ids && count($request->variant_ids)) {
            ProductStock::where('product_id', $request->id)->delete();
            session()->put('attributes', count($request->variant_ids));
        }

        DB::beginTransaction();
        try {

            $this->products->update($request);
            Toastr::success(__('Updated Successfully'));
            session()->forget('attributes');
            DB::commit();
            return redirect($request->r);
        } catch (\Exception $e) {
            DB::rollBack();
            info($e);
            Toastr::error($e->getMessage());
            return back();
        }
    }


    public function variants(Request $request)
    {
        if ($request->has_variant == 1) :
            $variants = array();

            $product_price = $request->price;
            $colors       = false;

            if (!empty($request->colors)) :
                array_push($variants, $request->colors);
                $colors = true;
            endif;

            if ($request->has('attribute_sets')) :
                foreach ($request->attribute_sets as $key => $attribute_set) :
                    $attribute_values = 'attribute_values_' . $attribute_set;
                    $values = array();
                    if ($request->has($attribute_values)) :
                        foreach ($request[$attribute_values] as $value) :
                            array_push($values, $value);
                        endforeach;
                    endif;
                    if ($request->has($attribute_values)) :
                        array_push($variants, $values);
                    endif;
                endforeach;
            endif;
            $variants_data = VariantUtility::getVariants($variants);
            if (!empty($variants_data[0])) :
                return view('admin.products.products.variants', compact('variants', 'variants_data', 'product_price', 'colors'));
            else :
                return view('admin.products.products.variants');
            endif;
        else :
            return '';
        endif;
    }


    public function variantsEdit(Request $request)
    {
        $product = $this->products->get($request->id);

        if ($request->has_variant == 1) :
            $variants = array();

            $product_price = $request->price;
            $colors       = false;

            if (!empty($request->colors)) :
                array_push($variants, $request->colors);
                $colors = true;
            endif;

            if ($request->has('attribute_sets')) :
                foreach ($request->attribute_sets as $key => $attribute_set) :
                    $attribute_values = 'attribute_values_' . $attribute_set;
                    $values = array();
                    if ($request->has($attribute_values)) :
                        foreach ($request[$attribute_values] as $value) :
                            array_push($values, $value);
                        endforeach;
                    endif;
                    if ($request->has($attribute_values)) :
                        array_push($variants, $values);
                    endif;
                endforeach;
            endif;
            $variants_data = VariantUtility::getVariants($variants);
            if (!empty($variants_data[0])) :
                return view('admin.products.products.variants_edit', compact('variants', 'variants_data', 'product_price', 'product', 'colors'));
            else :
                return view('admin.products.products.variants_edit');
            endif;
        else :
            return '';
        endif;
    }

    public function getAttributeValues(Request $request)
    {
        $attributes_sets = $request->attribute_sets;

        if (!empty($attributes_sets)) :
            $attributes = $this->attributes->all()->whereIn('attributes.id', $attributes_sets)->where('lang', 'en')->get();
            return view('admin.products.products.values', compact('attributes', 'request', 'attributes_sets'));
        else :
            return '';
        endif;
    }

    public function restore($id)
    {

        DB::beginTransaction();
        try {
            $this->products->restore($id);
            Toastr::success(__('Updated successfully as unpublished'));
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function productStatusChange($status, $id)
    {
        if (config('app.demo_mode')) :
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;
        DB::beginTransaction();
        try {
            $this->products->productStatusChange($status, $id);
            Toastr::success(__('Updated successfully as ' . $status));
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function cloneProduct(Request $request, $id)
    {
        try {

            $lang       = $request->lang != '' ? $request->lang : \App::getLocale();
            if ($this->products->get($id) && $product_language = $this->products->getByLang($id, $lang)) :
                $data = [
                    'languages'         => $this->languages->all()->orderBy('id', 'asc')->get(),
                    'product_language'  => $product_language,
                    'lang'              => $lang,
                    'categories'        => $this->categories->allCategory()->where('parent_id', null)->where('status', 1),
                    'brands'            => $this->brands->all()->where('lang', 'en')->where('status', 1)->get(),
                    'colors'            => $this->colors->all()->where('lang', 'en')->get(),
                    'attributes'        => $this->attributes->all()->where('lang', 'en')->get(),
                    'r'                 => $request->r != '' ? $request->r : $request->server('HTTP_REFERER'),
                    'clone'             => 1,
                    'campaigns'         => \App\Models\Campaign::where('status', 1)->where('end_date', '>', Carbon::now()->format('Y-m-d'))->get()
                ];
                if (addon_is_activated('ramdhani')) {
                    $repo = new ShippingClassRepository();
                    $data['shipping_classes'] = $repo->activeClasses();
                }
                if ($product_language->product->is_wholesale != 1) :
                    return view('admin.products.products.edit', $data);
                else :
                    return redirect()->route('wholesale.product.clone', [$id]);
                endif;

            else :
                Toastr::error(__('Not found'));
                return back();
            endif;
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function StoreCloneProduct(ProductStoreRequest $request)
    {


        if (config('app.demo_mode')) :
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;

        DB::beginTransaction();
        try {
            $this->products->store($request);
            Toastr::success(__('Created Successfully'));
            DB::commit();
            return redirect($request->r);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function reviews(ReviewInterface $review, Request $request)
    {
        $reviews = $review->paginateReviews($request->all());
        return view('admin.products.products.review', compact('reviews'));
    }

    public function replies($review_id)
    {
        $replies = ReviewReply::where('review_id', $review_id)->paginate(15);
        return view('admin.products.products.replies', compact('replies'));
    }


    public function reviewStatusChange(ReviewInterface $review, Request $request)
    {
        if (config('app.demo_mode')) :
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;

        DB::beginTransaction();
        try {
            $review->statusChange($request['data']);
            $response['message'] = __('Updated Successfully');
            $response['title'] = __('Success');
            $response['status'] = 'success';
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function productImport(Request $request)
    {
        return view('admin.products.products.import-products');
    }

    public function manageCities($id)
    {
        try {
            $main_cities = City::count();
            $cities = ProductCity::where('product_id', $id)->count();
            if ($cities < $main_cities) {
                //inserting missing cities

                $cities_id = ProductCity::where('product_id', $id)->pluck('city_id')->toArray();
                $main_cities = City::whereNotIn('id', $cities_id)->get();

                $data = [];

                foreach ($main_cities as $city) {
                    $data[] = [
                        'product_id'    => $id,
                        'city_id'       => $city->id,
                        'status'        => $city->status,
                        'cost'          => $city->cost,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }

                if (count($data) > 0) {
                    $chunk = array_chunk($data, 1000);
                    foreach ($chunk as $c) {
                        DB::table('product_cities')->insert($c);
                    }
                }
            }
            $cities = ProductCity::with('city.state', 'city.country')->withAggregate('city', 'name')->where('product_id', $id)
                ->orderBy('city_name')->paginate(get_pagination('index_form_paginate'));
            $data = [
                'class' => $this->products->get($id),
                'cities' => $cities
            ];
            return view('admin.products.products.cities', $data);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
}
