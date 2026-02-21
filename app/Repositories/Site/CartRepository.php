<?php

namespace App\Repositories\Site;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\City;
use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Checkout;
use App\Models\ClassCity;
use App\Models\ProductCity;
use App\Models\ShippedCity;
use App\Models\CampaignProduct;
use Illuminate\Support\Str;
use App\Models\PointSetting;
use App\Traits\RandomStringTrait;
use Illuminate\Support\Facades\DB;
use App\Repositories\Admin\VatTaxRepository;
use App\Repositories\Admin\ShippingRepository;
use App\Repositories\Interfaces\Site\CartInterface;
use App\Repositories\Interfaces\Admin\Product\ProductInterface;

class CartRepository implements CartInterface
{
    use RandomStringTrait;

    protected $product;

    public function __construct(ProductInterface $product)
    {
        $this->product = $product;
    }

    public function all()
    {
        if (authUser()) {
            $carts = Cart::with('product.stock:id,product_id,image,name,sku,current_stock',
                'product:free_shipping,slug,shipping_fee,user_id,price,id,thumbnail,minimum_order_quantity,is_refundable,current_stock,shipping_fee_depend_on_quantity,special_discount,special_discount_start,special_discount_end,special_discount_type,is_digital','seller:user_id,shop_name,logo')
                ->where('user_id', authId())->get();
        } else {
            Cart::where('created_at','<',Carbon::now()->subDays(2))->delete();
            if (!session()->has('walk_in_id'))
            {
                session()->put('walk_in_id',Str::random(21));
            }
            $carts = Cart::with('product.stock:id,product_id,image,name,sku,current_stock',
                'product:free_shipping,slug,user_id,price,shipping_fee,id,thumbnail,minimum_order_quantity,is_refundable,current_stock,shipping_fee_depend_on_quantity,special_discount,special_discount_start,special_discount_end,special_discount_type,is_digital','seller:user_id,shop_name,logo')
                ->where('user_id', getWalkInCustomer()->id)->where('trx_id',session()->get('walk_in_id'))->get();
        }

        // CRITICAL FIX: Refresh campaign pricing for all cart items
        // This ensures cart prices update when campaigns start/end or expire
        foreach ($carts as $cart) {
            if ($cart->product && !$cart->product->is_wholesale) {
                $this->refreshCartCampaignPricing($cart);
            }
        }

        return $carts;
    }

    /**
     * Refresh campaign pricing for a cart item
     * Updates cart->price and cart->discount based on current active campaigns
     */
    protected function refreshCartCampaignPricing($cart)
    {
        try {
            if (class_exists(\App\Services\CampaignPricingService::class)) {
                $pricingService = app(\App\Services\CampaignPricingService::class);
                $campaignPricing = $pricingService->getCampaignPrice($cart->product_id);

                // Determine original_price:
                // Priority: variant stock price -> first stock price -> product price
                $product_stock = null;
                $original_price = $cart->product->price; // Default to product price

                // Only look up variant if variant name is not empty
                if (!empty($cart->variant)) {
                    $product_stock = $cart->product->stock->where('name', $cart->variant)->first();
                    if ($product_stock && isset($product_stock->price) && $product_stock->price > 0) {
                        $original_price = $product_stock->price;
                    }
                }

                // Ensure original_price is valid
                if ($original_price <= 0) {
                    $original_price = $cart->product->price;
                }

                // Log before refresh
                \Log::info('refreshCartCampaignPricing BEFORE', [
                    'cart_id' => $cart->id,
                    'variant' => $cart->variant,
                    'original_price' => $original_price,
                    'cart_price' => $cart->price,
                    'cart_discount' => $cart->discount,
                    'has_campaign' => !empty($campaignPricing),
                ]);

                if ($campaignPricing && isset($campaignPricing['price']) && $campaignPricing['price'] < $original_price) {
                    // Campaign is active - use campaign price as selling price
                    $cart->price = $campaignPricing['price'];
                    // Discount = original_price - campaign_price
                    $cart->discount = $original_price - $campaignPricing['price'];
                } elseif (special_discount_applicable($cart->product)) {
                    // No active campaign - use special discount
                    if ($cart->product->special_discount_type == 'flat') {
                        $cart->discount = $cart->product->special_discount;
                        $cart->price = $original_price - $cart->discount;
                    } elseif ($cart->product->special_discount_type == 'percentage') {
                        $cart->discount = ($original_price * $cart->product->special_discount) / 100;
                        $cart->price = $original_price - $cart->discount;
                    }
                } else {
                    // No discount - selling price equals original price
                    $cart->price = $original_price;
                    $cart->discount = 0.00;
                }

                // Validate and fix cart values if needed
                if ($cart->price === null || $cart->price <= 0) {
                    $cart->price = $original_price;
                    $cart->discount = 0.00;
                }
                if ($cart->discount < 0) {
                    $cart->discount = 0.00;
                }

                // Log after refresh
                \Log::info('refreshCartCampaignPricing AFTER', [
                    'cart_id' => $cart->id,
                    'new_price' => $cart->price,
                    'new_discount' => $cart->discount,
                    'is_dirty' => $cart->isDirty('price') || $cart->isDirty('discount'),
                ]);

                // Always save to ensure cart has correct values
                // This fixes old cart items that have wrong price/discount values
                if ($cart->isDirty('price') || $cart->isDirty('discount')) {
                    $cart->save();
                    \Log::info('Cart saved', ['cart_id' => $cart->id]);
                }
            }
        } catch (\Exception $e) {
            // Log error but don't break cart loading
            \Log::error('Error refreshing cart campaign pricing', [
                'cart_id' => $cart->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function get($id)
    {
        return Cart::find($id);
    }

    public function addToCart($request, $product,$user)
    {
        $this->deleteBuyNow();
        if (arrayCheck('is_buy_now', $request->all()) && $request['is_buy_now'] == 1) {
            $trx_id = Str::random(21);
            session()->put('is_buy_now', 1);
        }
        else{
            $trx_id = $request['trx_id'] ?: Str::random(21);
        }

        if ($user):
            $user_id = $user->id;
            $carts = Cart::where('user_id', $user_id)->where('product_id', $product->id)->get();
        else:

            if (!session()->has('walk_in_id'))
            {
                session()->put('walk_in_id',$trx_id);
            }
            else{
                $trx_id = session()->get('walk_in_id');
            }

            $carts = Cart::where('user_id', getWalkInCustomer()->id)->where('trx_id',$trx_id)->where('product_id', $product->id)->get();
        endif;

        // Find product stock - handle both variant and non-variant products
        $product_stock = null;
        $variants_name = $request['variants_name'] ?? null;
        $variant_ids = $request['variants_ids'] ?? null;

        // Try to find exact variant match
        if (!empty($variants_name) && !empty($variant_ids)) {
            $product_stock = $product->stock
                ->where('name', $variants_name)
                ->where('variant_ids', $variant_ids)
                ->first();
        } elseif (!empty($variants_name)) {
            // Only variant name provided
            $product_stock = $product->stock
                ->where('name', $variants_name)
                ->first();
        }

        $product_stock_count = $product->stock;
        $totalStock = $product_stock_count->sum('current_stock');

        if ($totalStock < $request['quantity']) {
            return 'out_of_stock';
        }

        // Determine base/original price
        // Priority: variant stock price -> first stock price -> product price
        if ($product_stock && isset($product_stock->price) && $product_stock->price > 0) {
            $original_price = $product_stock->price;
        } else {
            $first_stock = $product->stock->first();
            $original_price = ($first_stock && isset($first_stock->price) && $first_stock->price > 0)
                ? $first_stock->price
                : $product->price;
        }

        // Ensure original_price is valid
        if ($original_price <= 0) {
            $original_price = $product->price;
        }

        $price = $original_price; // Default: no discount
        $discount = 0.00;

        //wholesale product will not be applicable for discount
        //and price will be the actual price without campaign discounts
        if ($product->is_wholesale):
            $wholesale_price = null;
            if ($product_stock) {
                $wholesale_price = $product_stock->wholeSalePrice
                    ->where('min_qty', '<=', $request->quantity)
                    ->where('max_qty', '>=', $request->quantity)
                    ->first();
            }
            if (!blank($wholesale_price) && $wholesale_price->price > 0):
                $price = $wholesale_price->price;
                $original_price = $price; // Update original for wholesale
            endif;
        else:
            // Check campaign pricing first (highest priority)
            try {
                if (class_exists(\App\Services\CampaignPricingService::class)) {
                    $pricingService = app(\App\Services\CampaignPricingService::class);
                    $campaignPricing = $pricingService->getCampaignPrice($product->id);

                    if ($campaignPricing && isset($campaignPricing['price']) && $campaignPricing['price'] < $original_price) {
                        // Campaign pricing applies
                        $price = $campaignPricing['price'];
                        $discount = $original_price - $price;
                    } elseif (special_discount_applicable($product)) {
                        // No active campaign, use special discount
                        if ($product->special_discount_type == 'flat'):
                            $discount = $product->special_discount;
                        elseif ($product->special_discount_type == 'percentage'):
                            $discount = ($original_price * $product->special_discount) / 100;
                        endif;
                        $price = $original_price - $discount;
                    }
                } elseif (special_discount_applicable($product)) {
                    // CampaignPricingService not available, fallback to special discount only
                    if ($product->special_discount_type == 'flat'):
                        $discount = $product->special_discount;
                    elseif ($product->special_discount_type == 'percentage'):
                        $discount = ($original_price * $product->special_discount) / 100;
                    endif;
                    $price = $original_price - $discount;
                }
            } catch (\Exception $e) {
                // If campaign pricing fails, fallback to special discount
                if (special_discount_applicable($product)):
                    if ($product->special_discount_type == 'flat'):
                        $discount = $product->special_discount;
                    elseif ($product->special_discount_type == 'percentage'):
                        $discount = ($original_price * $product->special_discount) / 100;
                    endif;
                    $price = $original_price - $discount;
                endif;
            }
        endif;

        // Validate final price and discount
        if ($price <= 0 || $price === null) {
            $price = $original_price;
            $discount = 0.00;
        }
        if ($discount < 0) {
            $discount = 0.00;
        }

        // Log for debugging
        \Log::info('addToCart final values', [
            'product_id' => $product->id,
            'variant' => $variants_name,
            'original_price' => $original_price,
            'price' => $price,
            'discount' => $discount,
        ]);

        //tax calculation
        $tax = 0;
        /*if(!$request['variants_name']){
            $request['variants_name'] = $product_stock->sku;
        }*/
        $variant = $request['variants_name'];

        if (settingHelper('vat_and_tax_type') == 'product_base'):
            foreach ($product->vatTaxes($product) as $product_tax) :
                $tax += ($price * $product_tax->percentage) / 100;
            endforeach;
        endif;

        //shipping calculation
        $shipping_cost = 0;
        $shipping_type = settingHelper('shipping_fee_type');

        if ($shipping_type == 'product_base') {
            $fee_type = $product->shipping_type;
            $fee = $product->shipping_fee;

            if ($fee_type == 'flat_rate') {
                if ($product->shipping_fee_depend_on_quantity) {
                    $fee = $request['quantity'] * $fee;
                }
                $shipping_cost = $fee;
            }
        }

        if ($totalStock < $request['quantity']) {
            return 'out_of_stock';
        }

        $parse_cart = $this->userCarts($request, $carts, $variant, $product, $price, $discount, $tax, $shipping_cost,$user,$trx_id);

        if (is_string($parse_cart) && $parse_cart == 'out_of_stock') {
            return 'out_of_stock';
        }

        // CRITICAL FIX: Recalculate coupon discounts for all cart items after adding new product
        // This ensures coupon discounts are updated when a new product is added
        $this->recalculateCouponDiscounts($trx_id, $user);

        return $parse_cart;
    }

    protected function userCarts($request, $carts, $variant, $product, $price, $discount, $tax, $shipping_cost,$user,$trx_id)
    {
        if (count($carts) > 0):
            $cart_exist = false;
            foreach ($carts as $cart_item):
                $cart_product       = $this->product->all()->where('id', $cart_item['product_id'])->first();

                $product_stock      = $cart_product->stock->where('name', $variant)->first();
//                $current_stock      = $product_stock ? $product_stock->current_stock : 0;
                $current_stock      = $cart_product->stock->sum('current_stock') ?? 0;

                if (($current_stock < $cart_item['quantity'] + $request['quantity']) && $variant == $cart_item['variant']):
                    return 'out_of_stock';
                endif;
                if (($variant != null && $cart_item['variant'] == $variant) || $variant == null):
                    $cart_exist     = true;


                    $cart_item->quantity += $request['quantity'];
                    //wholesale product so price will be the actual price without campaign discounts
                    if ($cart_product->is_wholesale):
                        $wholesale_price = $product_stock->wholeSalePrice->where('min_qty', '<=', $request->quantity)->where('max_qty', '>=', $request->quantity)->first();
                        if (!blank($wholesale_price)):
                            $price = $wholesale_price->price;
                        endif;
                    endif;

                    $cart_item->price = $price;

                    $cart_item->save();
                endif;

            endforeach;
            if (!$cart_exist) :
                //inserting new record to cart as cart not available for this variant
                $cart_item                  = new Cart();
                $cart_item->seller_id       = 4;
//                $cart_item->seller_id       = $product->user_id;
                $cart_item->user_id         = $user ? $user->id : getWalkInCustomer()->id;
                $cart_item->guest_id        = null;
                $cart_item->product_id      = $product->id;
                $cart_item->variant         = $variant ?: '';
                $cart_item->quantity        = $request['quantity'];
                $cart_item->is_buy_now      = $request['is_buy_now'];
                $cart_item->price           = $price;
                $cart_item->discount        = $discount;
                $cart_item->trx_id          = $trx_id;
                $cart_item->tax             = $tax;
                $cart_item->shipping_cost   = $shipping_cost;
                $cart_item->shipping_type   = settingHelper('shipping_fee_type');
                $cart_item->save();
            endif;
        else:
            //inserting new record to cart as cart not available
            $cart_item                      = new Cart();
            $cart_item->seller_id           = 4;
            $cart_item->user_id             = $user ? $user->id : getWalkInCustomer()->id;
            $cart_item->guest_id            = null;
            $cart_item->product_id          = $product->id;
            $cart_item->variant             = $variant ?: '';
            $cart_item->quantity            = $request['quantity'];
            $cart_item->is_buy_now          = $request['is_buy_now'];
            $cart_item->price               = $price;
            $cart_item->discount            = $discount;
            $cart_item->tax                 = $tax;
            $cart_item->trx_id              = $trx_id;
            $cart_item->shipping_cost       = $shipping_cost;
            $cart_item->shipping_type       = settingHelper('shipping_fee_type');
            $cart_item->save();
        endif;

        return true;
    }

    protected function sessionCarts($request, $carts, $variant, $product, $price, $discount, $tax, $shipping_cost)
    {
        if ($carts && count($carts)) {
            $cart_exist = false;

            foreach ($carts as $key => $cart_item) {
                if ($cart_item['product_id'] == $product->id) {
                    $cart_product = $this->product->all()->where('id', $cart_item['product_id'])->first();
                    if ($cart_product) {
                        $product_stock = $cart_product->stock->where('name', $variant)->first();

                        $current_stock = $product_stock->current_stock;
                        if (($current_stock < $cart_item['quantity'] + $request['quantity']) && $variant == $cart_item['variant']) {
                            return 'out_of_stock';
                        }
                        if (($variant != null && $cart_item['variant'] == $variant) || $variant == null) {
                            $cart_exist = true;

                            if ($cart_product->is_wholesale) {
                                $wholesale_price = $product_stock->wholeSalePrice->where('min_qty', '<=', $request->quantity)->where('max_qty', '>=', $request->quantity)->first();
                                if (!blank($wholesale_price)) {
                                    $price = $wholesale_price->price;
                                }
                            }
                            $image = $this->getCartImages($product, $product_stock);
                            $carts[$key] = [
                                'id'                    => $cart_item['slug'],
                                'product_name'          => $product->product_name,
                                'seller_id'             => $product->user_id,
                                'product_id'            => $product->id,
                                'slug'                  => $product->slug,
                                'image_40x40'           => $image['image_40x40'],
                                'image_72x72'           => $image['image_72x72'],
                                'min_quantity'          => $product->minimum_order_quantity,
                                'current_stock'         => $product_stock->current_stock,
                                'quantity'              => $cart_item['quantity'] + $request['quantity'],
                                'shop_name'             => @$product->sellerProfile->shop_name,
                                'shop_image'            => @$product->sellerProfile->image_90x60,
                                'price'                 => $price,
                                'discount_price'        => $product->discount_percentage,
                                'variant'               => $variant,
                                'discount'              => $discount,
                                'tax'                   => $tax,
                                'shipping_cost'         => $shipping_cost,
                                'is_digital'            => $product->is_digital,
                            ];
                        }
                    }
                }
            }
            if (!$cart_exist) {
                $slug                       = Str::random(21);
                $product_stock              = $product->stock->where('name', $variant)->first();
                $image                      = $this->getCartImages($product, $product_stock);
                $carts[count($carts)] = [
                    'id'                    => $slug,
                    'product_name'          => $product->product_name,
                    'seller_id'             => $product->user_id,
                    'product_id'            => $product->id,
                    'slug'                  => $product->slug,
                    'thumbnail'             => $product->slug,
                    'image_40x40'           => $image['image_40x40'],
                    'image_72x72'           => $image['image_72x72'],
                    'min_quantity'          => $product->minimum_order_quantity,
                    'current_stock'         => $product_stock->current_stock,
                    'quantity'              => $request['quantity'],
                    'shop_name'             => @$product->sellerProfile->shop_name,
                    'shop_image'            => @$product->sellerProfile->image_90x60,
                    'price'                 => $price,
                    'discount_price'        => $product->discount_percentage,
                    'variant'               => $variant,
                    'discount'              => $discount,
                    'tax'                   => $tax,
                    'shipping_cost'         => $shipping_cost,
                    'is_digital'            => $product->is_digital,
                ];
            }
        } else {
            $slug           = Str::random(21);
            $carts          = [];
            $product_stock  = $product->stock()->where('name', $variant)->first();
            $image          = $this->getCartImages($product, $product_stock);
            $carts[]     = [
                'id'                    => $slug,
                'product_name'          => $product->product_name,
                'seller_id'             => $product->user_id,
                'product_id'            => $product->id,
                'slug'                  => $product->slug,
                'min_quantity'          => $product->minimum_order_quantity,
                'current_stock'         => $product_stock->current_stock,
                'thumbnail'             => $product->slug,
                'image_40x40'           => $image['image_40x40'],
                'image_72x72'           => $image['image_72x72'],
                'quantity'              => $request['quantity'],
                'shop_name'             => @$product->sellerProfile->shop_name,
                'shop_image'            => @$product->sellerProfile->image_90x60,
                'price'                 => $price,
                'discount_price'        => $product->discount_percentage,
                'variant'               => $variant,
                'discount'              => $discount,
                'tax'                   => $tax,
                'shipping_cost'         => $shipping_cost,
                'is_digital'            => $product->is_digital,
            ];
        }

        return $carts;
    }

    protected function getCartImages($product, $product_stock): array
    {
        if ($product_stock && is_array($product_stock->image) && array_key_exists('image_40x40', $product_stock->image) && is_file_exists($product_stock->image['image_40x40'], $product_stock->image['storage'])) {
            $image_40 = get_media(@$product_stock->image['image_40x40'], @$product_stock->image['storage']);
        } elseif ($product && is_array($product->thumbnail) && array_key_exists('image_40x40', $product->thumbnail) && is_file_exists($product->thumbnail['image_40x40'], $product->thumbnail['storage'])) {
            $image_40 = get_media(@$product->thumbnail['image_40x40'], @$product->thumbnail['storage']);
        } else {
            $image_40 = static_asset('images/default/default-image-40x40.png');
        }

        if ($product_stock && is_array($product_stock->image) && array_key_exists('image_72x72', $product_stock->image) && is_file_exists($product_stock->image['image_72x72'], $product_stock->image['storage'])) {
            $image_72 = get_media(@$product_stock->image['image_72x72'], @$product_stock->image['storage']);
        } elseif ($product && is_array($product->thumbnail) && array_key_exists('image_40x40', $product->thumbnail) && is_file_exists($product->thumbnail['image_40x40'], $product->thumbnail['storage'])) {
            $image_72 = get_media(@$product->thumbnail['image_72x72'], @$product->thumbnail['storage']);
        } else {
            $image_72 = static_asset('images/default/default-image-72x72.png');
        }

        return [
            'image_40x40' => $image_40,
            'image_72x72' => $image_72,
        ];
    }

    public function updateCart($request)
    {
        $cart_item = $this->get($request->id);


        //get the requested cart item and product price by stock
        $product        = $this->product->get($cart_item->product_id);
        $product_stock  = $product->stock->where('name', $cart_item->variant)->first();
        $quantity       = $product->stock->sum('current_stock');
        $price          = $product_stock->price;

        $total_quantity = $cart_item->quantity + $request->quantity;

        if ($quantity >= $total_quantity) {
            if ($total_quantity >= $product->minimum_order_quantity):
                $cart_item->quantity += $request->quantity;
            else:
                return [
                    'msg' => 'min_qty',
                    'qty' => $product->minimum_order_quantity
                ];
            endif;
        } else
            return 'out_of_stock';

        //check for wholesale product
        $discount = 0.00;
        if ($product->is_wholesale):
            $wholesale_price = $product_stock->wholeSalePrice->where('min_qty', '<=', $cart_item->quantity + $request->quantity)->where('max_qty', '>=', $cart_item->quantity + $request->quantity)->first();
            if (!blank($wholesale_price)):
                $price = $wholesale_price->price;
            endif;
        else:
            // CRITICAL FIX: Re-check campaign pricing on cart update
            // This ensures that if campaign expires or becomes active, cart prices update
            try {
                if (class_exists(\App\Services\CampaignPricingService::class)) {
                    $pricingService = app(\App\Services\CampaignPricingService::class);
                    $campaignPricing = $pricingService->getCampaignPrice($product->id);

                    if ($campaignPricing && $campaignPricing['price'] < $price) {
                        // Campaign pricing applies - calculate discount from original price
                        $price = $campaignPricing['price'];
                        $discount = $campaignPricing['original_price'] - $price;
                    } elseif (special_discount_applicable($product)) {
                        // No active campaign, use special discount
                        if ($product->special_discount_type == 'flat'):
                            $discount   = $product->special_discount;
                        elseif ($product->special_discount_type == 'percentage'):
                            $discount   = ($price * $product->special_discount) / 100;
                        endif;
                    }
                } elseif (special_discount_applicable($product)) {
                    // CampaignPricingService not available, fallback to special discount only
                    if ($product->special_discount_type == 'flat'):
                        $discount   = $product->special_discount;
                    elseif ($product->special_discount_type == 'percentage'):
                        $discount   = ($price * $product->special_discount) / 100;
                    endif;
                }
            } catch (\Exception $e) {
                // If campaign pricing fails, fallback to special discount
                if (special_discount_applicable($product)):
                    if ($product->special_discount_type == 'flat'):
                        $discount   = $product->special_discount;
                    elseif ($product->special_discount_type == 'percentage'):
                        $discount   = ($price * $product->special_discount) / 100;
                    endif;
                endif;
            }
        endif;

        //shipping calculation
        $shipping_cost = 0;
        $shipping_type = settingHelper('shipping_fee_type');

        if ($shipping_type == 'product_base') {
            $fee_type = $product->shipping_type;
            $fee = $product->shipping_fee;

            if ($fee_type == 'flat_rate') {
                if ($product->shipping_fee_depend_on_quantity) {
                    $fee = $cart_item->quantity * $fee;
                }
                $shipping_cost = $fee;
            }
        }

        $cart_item->price = $price;
        $cart_item->discount = $discount;
        $cart_item->shipping_cost = $shipping_cost;
        $cart_item->save();

        // CRITICAL FIX: Recalculate coupon discounts for all cart items after cart update
        // This ensures coupon discounts are updated when quantity changes
        $user = authUser() ?? null;
        $this->recalculateCouponDiscounts($cart_item->trx_id, $user);

        return $cart_item;
    }

    /**
     * Recalculate coupon discounts in checkouts table based on current cart subtotal
     * This ensures coupon_discount field stays in sync when cart quantities change
     */
    protected function recalculateCheckoutCouponDiscounts($trx_id)
    {
        if (!$trx_id) {
            return;
        }

        // Get campaign product IDs for all carts (single query for efficiency)
        $now = now()->format('Y-m-d H:i:s');
        $campaignProductIds = CampaignProduct::whereHas('campaign', function($q) {
                $q->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
            })
            ->pluck('product_id')
            ->toArray();

        // Get all active checkouts for this transaction
        $checkouts = Checkout::where('trx_id', $trx_id)->where('status', 1)->with('coupon')->get();

        foreach ($checkouts as $checkout) {
            $coupon = $checkout->coupon;

            if (!$coupon) {
                continue;
            }

            // Get carts for this checkout (filtered by seller_id if coupon is seller-specific)
            $carts = Cart::with('product:id,special_discount,special_discount_type,special_discount_start,special_discount_end')->where('trx_id', $trx_id)
                ->when($coupon->user_id > 1, function ($query) use ($coupon) {
                    $query->where('seller_id', $coupon->user_id);
                })
                ->get();

            if ($coupon->type == 'product_base' && $coupon->product_id) {
                // Product-based coupon: only calculate discount for specific products
                $duplicates = array_intersect($coupon->product_id, $carts->pluck('product_id')->toArray());
                $checkout_carts = $carts->whereIn('product_id', $duplicates);

                // First pass: calculate total discount for all eligible products
                $calculated_discount = 0;
                foreach ($checkout_carts as $cart) {
                    $isDiscountedProduct = false;

                    // Check if product is discounted
                    if ($cart->product) {
                        // Check for special discount
                        if ($cart->product->special_discount > 0 &&
                            $cart->product->special_discount_start <= $now &&
                            $cart->product->special_discount_end >= $now) {
                            $isDiscountedProduct = true;
                        }
                        // Check for campaign product
                        if (in_array($cart->product_id, $campaignProductIds)) {
                            $isDiscountedProduct = true;
                        }
                    }

                    // Calculate discount amount based on applicable_on_discount setting
                    if ($coupon->applicable_on_discount == 0) {
                        // Only apply to non-discounted products
                        if (!$isDiscountedProduct) {
                            $calculated_discount += $this->calculateDiscount($coupon, ($cart->price * $cart->quantity));
                        }
                    } else {
                        // Apply to all products - use selling price for discounted products
                        if ($isDiscountedProduct) {
                            $calculated_discount += $this->calculateDiscount($coupon, (($cart->price - $cart->discount) * $cart->quantity));
                        } else {
                            $calculated_discount += $this->calculateDiscount($coupon, ($cart->price * $cart->quantity));
                        }
                    }
                }

                // Apply maximum discount cap
                $total_coupon_discount = min($calculated_discount, $coupon->maximum_discount);

                // Second pass: distribute the capped discount proportionally among eligible products
                $eligible_total = 0;
                $eligible_carts = [];

                foreach ($checkout_carts as $cart) {
                    $isDiscountedProduct = false;

                    // Check if product is discounted
                    if ($cart->product) {
                        // Check for special discount
                        if ($cart->product->special_discount > 0 &&
                            $cart->product->special_discount_start <= $now &&
                            $cart->product->special_discount_end >= $now) {
                            $isDiscountedProduct = true;
                        }
                        // Check for campaign product
                        if (in_array($cart->product_id, $campaignProductIds)) {
                            $isDiscountedProduct = true;
                        }
                    }

                    // Collect eligible carts based on applicable_on_discount
                    if ($coupon->applicable_on_discount == 0) {
                        // Only include non-discounted products
                        if (!$isDiscountedProduct) {
                            $eligible_total += $cart->price * $cart->quantity;
                            $eligible_carts[] = $cart;
                        }
                    } else {
                        // Include all products - use selling price for discounted products
                        if ($isDiscountedProduct) {
                            $eligible_total += ($cart->price - $cart->discount) * $cart->quantity;
                        } else {
                            $eligible_total += $cart->price * $cart->quantity;
                        }
                        $eligible_carts[] = $cart;
                    }

                    $cart->coupon_applied = 1;
                    $cart->coupon_discount = 0;
                }

                // Distribute discount proportionally among eligible products
                foreach ($eligible_carts as $cart) {
                    $isDiscountedProduct = false;

                    // Re-check if product is discounted
                    if ($cart->product) {
                        if ($cart->product->special_discount > 0 &&
                            $cart->product->special_discount_start <= $now &&
                            $cart->product->special_discount_end >= $now) {
                            $isDiscountedProduct = true;
                        }
                        if (in_array($cart->product_id, $campaignProductIds)) {
                            $isDiscountedProduct = true;
                        }
                    }

                    // Calculate cart total for proportion
                    if ($coupon->applicable_on_discount == 1 && $isDiscountedProduct) {
                        $cart_total = ($cart->price - $cart->discount) * $cart->quantity;
                    } else {
                        $cart_total = $cart->price * $cart->quantity;
                    }

                    $proportion = $eligible_total > 0 ? $cart_total / $eligible_total : 0;
                    $cart->coupon_discount = $total_coupon_discount * $proportion;
                    $cart->save();
                }

                // Update checkout coupon_discount
                $checkout->coupon_discount = round($total_coupon_discount, 2);
                $checkout->save();

            } else {
                // Cart-wide coupon: calculate discount based on eligible products only
                $coupon_base_amount = 0;

                foreach ($carts as $cart) {
                    $isDiscountedProduct = false;

                    // Check if product is discounted
                    if ($cart->product) {
                        // Check for special discount
                        if ($cart->product->special_discount > 0 &&
                            $cart->product->special_discount_start <= $now &&
                            $cart->product->special_discount_end >= $now) {
                            $isDiscountedProduct = true;
                        }
                        // Check for campaign product
                        if (in_array($cart->product_id, $campaignProductIds)) {
                            $isDiscountedProduct = true;
                        }
                    }

                    $cart->coupon_applied = 1;
                    $cart->coupon_discount = 0;

                    // Calculate eligible amount based on applicable_on_discount
                    if ($coupon->applicable_on_discount == 0) {
                        // Only include non-discounted products
                        if (!$isDiscountedProduct) {
                            $coupon_base_amount += $cart->price * $cart->quantity;
                        }
                    } else {
                        // Include all products - use selling price for discounted products
                        if ($isDiscountedProduct) {
                            $coupon_base_amount += ($cart->price - $cart->discount) * $cart->quantity;
                        } else {
                            $coupon_base_amount += $cart->price * $cart->quantity;
                        }
                    }
                    $cart->save();
                }

                // Calculate discount amount based on eligible amount
                $discount_amount = $this->calculateDiscount($coupon, $coupon_base_amount);
                $max_discount = $coupon->maximum_discount;
                $coupon_discount = min($discount_amount, $max_discount);

                // Distribute coupon discount proportionally among eligible products
                $eligible_total = 0;
                $eligible_carts = [];

                foreach ($carts as $cart) {
                    $isDiscountedProduct = false;

                    // Check if product is discounted
                    if ($cart->product) {
                        // Check for special discount
                        if ($cart->product->special_discount > 0 &&
                            $cart->product->special_discount_start <= $now &&
                            $cart->product->special_discount_end >= $now) {
                            $isDiscountedProduct = true;
                        }
                        // Check for campaign product
                        if (in_array($cart->product_id, $campaignProductIds)) {
                            $isDiscountedProduct = true;
                        }
                    }

                    // Collect eligible carts based on applicable_on_discount
                    if ($coupon->applicable_on_discount == 0) {
                        // Only include non-discounted products
                        if (!$isDiscountedProduct) {
                            $eligible_total += $cart->price * $cart->quantity;
                            $eligible_carts[] = $cart;
                        }
                    } else {
                        // Include all products - use selling price for discounted products
                        if ($isDiscountedProduct) {
                            $eligible_total += ($cart->price - $cart->discount) * $cart->quantity;
                        } else {
                            $eligible_total += $cart->price * $cart->quantity;
                        }
                        $eligible_carts[] = $cart;
                    }
                }

                // Distribute discount proportionally among eligible products
                foreach ($eligible_carts as $cart) {
                    $isDiscountedProduct = false;

                    // Re-check if product is discounted
                    if ($cart->product) {
                        if ($cart->product->special_discount > 0 &&
                            $cart->product->special_discount_start <= $now &&
                            $cart->product->special_discount_end >= $now) {
                            $isDiscountedProduct = true;
                        }
                        if (in_array($cart->product_id, $campaignProductIds)) {
                            $isDiscountedProduct = true;
                        }
                    }

                    // Calculate cart total for proportion
                    if ($coupon->applicable_on_discount == 1 && $isDiscountedProduct) {
                        $cart_total = ($cart->price - $cart->discount) * $cart->quantity;
                    } else {
                        $cart_total = $cart->price * $cart->quantity;
                    }

                    $proportion = $eligible_total > 0 ? $cart_total / $eligible_total : 0;
                    $cart->coupon_discount = $coupon_discount * $proportion;
                    $cart->save();
                }

                // Update checkout coupon_discount
                $checkout->coupon_discount = round($coupon_discount, 2);
                $checkout->save();
            }
        }
    }

    public function removeFromCart($id): bool
    {
        $cart = Cart::find($id);
        $trx_id = $cart ? $cart->trx_id : null;

        $result = Cart::destroy($id);

        // CRITICAL FIX: Recalculate coupon discounts after cart item is removed
        if ($result && $trx_id) {
            $user = authUser() ?? null;
            $this->recalculateCouponDiscounts($trx_id, $user);
        }

        return $result;
    }

    public function userCart($take = null)
    {
        if (authUser()) {
            $carts = Cart::with('product:id,thumbnail,slug')->where('user_id', authId())->groupBy('carts.id')
                ->paginate($take);
        } else {
            $carts = session()->get('carts') ?: [];
        }
        return $carts;
    }

    public function applyCoupon($data,$user)
    {
        $coupon = Coupon::where('status', 1)->where(DB::raw('BINARY `code`'), $data['coupon_code'])->first();

        if ($coupon) {
            $coupon_user = $coupon->user;
            $seller      = $coupon_user->sellerProfile;
            if ($coupon->start_date <= now() && $coupon->end_date > now()) {

                if ($coupon->user_id > 1)
                {
                    if ($coupon_user->status == 0)
                    {
                        return __('seller_is_disabled');
                    }
                    if ($coupon_user->is_user_banned == 1)
                    {
                        return __('seller_is_banned');
                    }

                    if (!$seller->verified_at)
                    {
                        return __('seller_is_unverified');
                    }
                    if (!$seller)
                    {
                        return __('seller_coupon_is_disabled');
                    }
                }
                $carts  = Cart::with('product:id,special_discount,special_discount_type,special_discount_start,special_discount_end')->where('user_id', $user->id)->when($coupon->user_id > 1,function ($q) use($coupon){
                    $q->where('seller_id', $coupon->user_id);
                })->where('trx_id',$data['trx_id'])->get();


                $checkout       = Checkout::where('user_id', $user->id)->where('seller_id', $coupon->user_id)->where('trx_id',$data['trx_id'])->where('status',1)->first();
                $coupon_used    = Checkout::where('user_id', $user->id)->where('coupon_id', $coupon->id)->where('trx_id',$data['trx_id'])->where('status',1)->first();

                if ($checkout) {
                    return __('This Seller Coupon is Already Used');
                }

                if ($coupon_used) {
                    return __('This Coupon is Already Used');
                }

                // Get campaign product IDs for all carts using CampaignPricingService
                // This ensures proper time-based validation (daily events, date ranges)
                $campaignProductIds = [];
                try {
                    if (class_exists(\App\Services\CampaignPricingService::class)) {
                        $pricingService = app(\App\Services\CampaignPricingService::class);
                        $activeCampaigns = $pricingService->getActiveCampaigns();

                        if ($activeCampaigns && $activeCampaigns->isNotEmpty()) {
                            foreach ($activeCampaigns as $campaign) {
                                // Get all products for this actively running campaign
                                $campaignProducts = $campaign->activeEventProducts()
                                    ->whereIn('product_id', $carts->pluck('product_id'))
                                    ->where('is_active', 1)
                                    ->where('status', 'active')
                                    ->pluck('product_id')
                                    ->toArray();
                                $campaignProductIds = array_merge($campaignProductIds, $campaignProducts);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Campaign check failed: ' . $e->getMessage());
                }
                $campaignProductIds = array_unique($campaignProductIds);

                $now = now()->format('Y-m-d H:i:s');

                // Calculate full cart subtotal for minimum shopping check
                $full_cart_subtotal = 0;
                foreach ($carts as $cart) {
                    $full_cart_subtotal += $cart->price * $cart->quantity;
                }

                // Calculate coupon base amount based on applicable_on_discount setting
                $coupon_base_amount = 0;
                foreach ($carts as $cart) {
                    $isDiscountedProduct = false;

                    // Skip if product doesn't exist
                    if (!$cart->product) {
                        continue;
                    }

                    // Check for special discount (active only)
                    if ($cart->product->special_discount > 0 &&
                        $cart->product->special_discount_start <= $now &&
                        $cart->product->special_discount_end >= $now) {
                        $isDiscountedProduct = true;
                    }
                    // Check for campaign product (active campaigns only)
                    if (in_array($cart->product_id, $campaignProductIds)) {
                        $isDiscountedProduct = true;
                    }

                    // Calculate amount for coupon base
                    if ($coupon->applicable_on_discount == 0) {
                        // Only include non-discounted products
                        if (!$isDiscountedProduct) {
                            $coupon_base_amount += $cart->price * $cart->quantity;
                        }
                    } else {
                        // Include all products - use selling price (cart->price is already after discount)
                        // For discounted products, cart->price IS the selling price
                        // For non-discounted products, cart->price is the original price
                        $coupon_base_amount += $cart->price * $cart->quantity;
                    }
                }

                // Special validation for applicable_on_discount = 0
                // Check if there are any eligible products (non-discounted) for the coupon
                if ($coupon->applicable_on_discount == 0 && $coupon_base_amount == 0) {
                    return __('This coupon is not applicable as all products in your cart already have discounts.');
                }

                $data['sub_total'] = $full_cart_subtotal;

                if ($coupon->type == 'product_base' && $coupon->product_id) {
                    $duplicates = array_intersect($coupon->product_id, $carts->pluck('product_id')->toArray());
                    if (count($duplicates) > 0) {
                        $checkout_carts = $carts->whereIn('product_id',$duplicates);

                        // Calculate eligible amount for minimum shopping check
                        $eligible_product_amount = 0;
                        foreach ($checkout_carts as $cart) {
                            $isDiscountedProduct = false;

                            // Check if product is discounted
                            if ($cart->product) {
                                // Check for special discount
                                if ($cart->product->special_discount > 0 &&
                                    $cart->product->special_discount_start <= $now &&
                                    $cart->product->special_discount_end >= $now) {
                                    $isDiscountedProduct = true;
                                }
                                // Check for campaign product
                                if (in_array($cart->product_id, $campaignProductIds)) {
                                    $isDiscountedProduct = true;
                                }
                            }

                            // Calculate eligible amount based on applicable_on_discount
                            if ($coupon->applicable_on_discount == 0) {
                                // Only include non-discounted products
                                if (!$isDiscountedProduct) {
                                    $eligible_product_amount += $cart->price * $cart->quantity;
                                }
                            } else {
                                // Include all products - use selling price (cart->price is already after discount)
                                $eligible_product_amount += $cart->price * $cart->quantity;
                            }
                        }

                        // Check minimum shopping against eligible product amount
                        if ($coupon->minimum_shopping > $eligible_product_amount) {
                            return __("You've to Purchase Minimum of " . format_price($coupon->minimum_shopping));
                        }

                        // Calculate total discount first before applying max cap
                        $calculated_discount = 0;
                        foreach ($checkout_carts as $cart) {
                            $isDiscountedProduct = false;

                            // Check if product is discounted
                            if ($cart->product) {
                                // Check for special discount
                                if ($cart->product->special_discount > 0 &&
                                    $cart->product->special_discount_start <= $now &&
                                    $cart->product->special_discount_end >= $now) {
                                    $isDiscountedProduct = true;
                                }
                                // Check for campaign product
                                if (in_array($cart->product_id, $campaignProductIds)) {
                                    $isDiscountedProduct = true;
                                }
                            }

                            // Calculate discount amount based on applicable_on_discount setting
                            if ($coupon->applicable_on_discount == 0) {
                                // Only apply to non-discounted products
                                if (!$isDiscountedProduct) {
                                    $calculated_discount += $this->calculateDiscount($coupon, ($cart->price * $cart->quantity));
                                }
                            } else {
                                // Apply to all products - use selling price (cart->price is already after discount)
                                $calculated_discount += $this->calculateDiscount($coupon, ($cart->price * $cart->quantity));
                            }
                        }

                        // Apply maximum discount cap
                        $total_coupon_discount = min($calculated_discount, $coupon->maximum_discount);

                        // Now distribute the capped discount proportionally among eligible products
                        $eligible_total = 0;
                        $eligible_carts = [];

                        foreach ($checkout_carts as $cart) {
                            $isDiscountedProduct = false;

                            // Check if product is discounted
                            if ($cart->product) {
                                // Check for special discount
                                if ($cart->product->special_discount > 0 &&
                                    $cart->product->special_discount_start <= $now &&
                                    $cart->product->special_discount_end >= $now) {
                                    $isDiscountedProduct = true;
                                }
                                // Check for campaign product
                                if (in_array($cart->product_id, $campaignProductIds)) {
                                    $isDiscountedProduct = true;
                                }
                            }

                            // Collect eligible carts based on applicable_on_discount
                            if ($coupon->applicable_on_discount == 0) {
                                // Only include non-discounted products
                                if (!$isDiscountedProduct) {
                                    $eligible_total += $cart->price * $cart->quantity;
                                    $eligible_carts[] = $cart;
                                }
                            } else {
                                // Include all products - use selling price (cart->price is already after discount)
                                $eligible_total += $cart->price * $cart->quantity;
                                $eligible_carts[] = $cart;
                            }

                            $cart->coupon_applied = 1;
                            $cart->coupon_discount = 0;
                        }

                        // Distribute discount proportionally among eligible products
                        foreach ($eligible_carts as $cart) {
                            $isDiscountedProduct = false;

                            // Re-check if product is discounted
                            if ($cart->product) {
                                if ($cart->product->special_discount > 0 &&
                                    $cart->product->special_discount_start <= $now &&
                                    $cart->product->special_discount_end >= $now) {
                                    $isDiscountedProduct = true;
                                }
                                if (in_array($cart->product_id, $campaignProductIds)) {
                                    $isDiscountedProduct = true;
                                }
                            }

                            // Calculate cart total for proportion
                            // Use selling price (cart->price is already after discount)
                            $cart_total = $cart->price * $cart->quantity;

                            $proportion = $eligible_total > 0 ? $cart_total / $eligible_total : 0;
                            $cart->coupon_discount = $total_coupon_discount * $proportion;
                            $cart->save();
                        }

                        return Checkout::create([
                            'user_id'           => $user->id,
                            'seller_id'         => $coupon->user_id > 1 ? $seller->id : 1,
                            'coupon_id'         => $coupon->id,
                            'trx_id'            => $data['trx_id'],
                            'coupon_discount'   => $total_coupon_discount,
                            'status'            => 1,
                        ]);
                    } else {
                        return __('Invalid Coupon');
                    }
                } else {
                    // Cart-based coupon
                    if ($coupon->minimum_shopping <= $full_cart_subtotal) {
                        // Calculate discount amount based on coupon base amount (eligible products only)
                        $discount_amount            = $this->calculateDiscount($coupon, $coupon_base_amount);
                        $max_discount               = $coupon->maximum_discount;
                        $coupon_discount            = min($discount_amount, $max_discount);

                        // Distribute coupon discount among eligible products
                        $eligible_total = 0;
                        $eligible_carts = [];

                        foreach ($carts as $cart) {
                            $isDiscountedProduct = false;

                            // Check if product is discounted
                            if ($cart->product) {
                                // Check for special discount
                                if ($cart->product->special_discount > 0 &&
                                    $cart->product->special_discount_start <= $now &&
                                    $cart->product->special_discount_end >= $now) {
                                    $isDiscountedProduct = true;
                                }
                                // Check for campaign product
                                if (in_array($cart->product_id, $campaignProductIds)) {
                                    $isDiscountedProduct = true;
                                }
                            }

                            $cart->coupon_applied = 1;
                            $cart->coupon_discount = 0;

                            // Calculate eligible amount based on applicable_on_discount
                            if ($coupon->applicable_on_discount == 0) {
                                // Only include non-discounted products
                                if (!$isDiscountedProduct) {
                                    $eligible_total += $cart->price * $cart->quantity;
                                    $eligible_carts[] = $cart;
                                }
                            } else {
                                // Include all products - use selling price (cart->price is already after discount)
                                $eligible_total += $cart->price * $cart->quantity;
                                $eligible_carts[] = $cart;
                            }
                            $cart->save();
                        }

                        // Distribute discount proportionally among eligible products
                        foreach ($eligible_carts as $cart) {
                            $isDiscountedProduct = false;

                            // Re-check if product is discounted
                            if ($cart->product) {
                                if ($cart->product->special_discount > 0 &&
                                    $cart->product->special_discount_start <= $now &&
                                    $cart->product->special_discount_end >= $now) {
                                    $isDiscountedProduct = true;
                                }
                                if (in_array($cart->product_id, $campaignProductIds)) {
                                    $isDiscountedProduct = true;
                                }
                            }

                            // Calculate cart total for proportion
                            // Use selling price (cart->price is already after discount)
                            $cart_total = $cart->price * $cart->quantity;

                            $proportion = $eligible_total > 0 ? $cart_total / $eligible_total : 0;
                            $cart->coupon_discount = $coupon_discount * $proportion;
                            $cart->save();
                        }

                        return Checkout::create([
                            'seller_id'         => $coupon->user_id > 1 ? $seller->id : 1,
                            'user_id'           => $user->id,
                            'coupon_id'         => $coupon->id,
                            'trx_id'            => $data['trx_id'],
                            'coupon_discount'   => $coupon_discount,
                            'status'            => 1,
                        ]);
                    } else {
                        return __("You've to Purchase Minimum of " . format_price($coupon->minimum_shopping));
                    }

                }

            } else {
                return __("Oops....You're too late, Coupon is Expired");
            }

        } else {
            return __('Coupon Not Found');
        }
    }

    public function applyPoints($data,$user)
    {
        info($data['use_purchase_point']);
        info($user->id);

        $pointData = PointSetting::where('status', 1)->first();

        if ($pointData->status == 1) {
            $point = $user->point;
            $point_value = ($pointData->point_to_money / $pointData->point);
            $point_amount = $data['use_purchase_point'] * $point_value;
        
            info($point_amount);
        
            if ($point >= $data['use_purchase_point']) {
                $carts = Cart::where('user_id', $user->id)->where('trx_id',$data['trx_id'])->get();
                $sub_total = 0;
                $amount = 0; 

                //find auth user
                $authUser = User::where('id', authUser()->id)->first();

                $authUser->point = $point - $data['use_purchase_point'];
                $authUser->save();
        
                // foreach ($carts as $cart) {
                //     $sub_total += $cart->price * $cart->quantity;
                //     $amount = $this->calculatePointDiscount($point_amount, $cart->price * $cart->quantity); // Update $amount inside the loop
                //     // $cart->point_discount = $amount;
                //     $cart->save();
                // }

                info($amount);
        
                // return Checkout::create([
                //     'user_id'           => $user->id,
                //     'seller_id'         => 1,
                //     'point_discount'    => $point_amount, // Now $amount is defined in this scope
                //     'trx_id'            => $data['trx_id'],
                //     'status'            => 1,
                // ]);
            } 
        
        } else {
            return __('Point System is Disabled');
        }


    }

    protected function calculatePointDiscount($point_amount, $price)
    {
        return ($price - $point_amount);
    }

    protected function calculateDiscount($coupon, $price)
    {
        if ($coupon->discount_type == 'flat') {
            $coupon_discount = $coupon->discount;
        } else {
            $coupon_discount = $price * ($coupon->discount / 100);
        }

        return $coupon_discount;
    }

    public function checkoutCoupon($carts, $data,$user): array
    {
        $trx_id = '';
        $walk_user = getWalkInCustomer();

        $group_carts = $fee = [];

        if (count($carts) > 0) {
            $trx_id = $carts->first()->trx_id;
            $seller_carts = $carts->groupBy('seller_id');

            $shipping_cost = 0;

            if (settingHelper('shipping_fee_type') == 'flat_rate') {
                $shipping_cost = settingHelper('shipping_fee_flat_rate');
            }

            if (settingHelper('shipping_fee_type') == 'area_base' && request()->route()->getName() == 'user.addresses') {
                if ($user)
                {
                    $shipping_address   = $user->addresses()->where('default_shipping', 1)->first();
                }
                else{
                    $shipping_address   = $walk_user->addresses()->where('default_shipping', 1)->first();
                }

                if ($shipping_address && $shipping_address->address_ids) {
                    $city_id        = $shipping_address->address_ids['city_id'];
                    $shipping_repo  = new ShippingRepository();
                    $city           = $shipping_repo->getCity($city_id);
                    $shipping_cost  = $city ? $city->cost : 0;
                }
            }
            elseif (addon_is_activated('ramdhani') && settingHelper('shipping_fee_type') == 'product_base')
            {
                $city_id = 0;
                if ($user)
                {
                    $shipping_address   = $user->addresses()->where('default_shipping', 1)->first();
                }
                else{
                    $shipping_address   = $walk_user->addresses()->where('default_shipping', 1)->first();
                }
                if ($shipping_address && $shipping_address->address_ids) {
                    $city_id        = $shipping_address->address_ids['city_id'];
                }
                $class_ids = Product::whereIn('id',$carts->pluck('product_id')->toArray())->pluck('shipping_class_id')->toArray();
                $shipping_cost = ClassCity::whereIn('shipping_class_id',$class_ids)->where('city_id',$city_id)->sum('cost');
            }

            $tax_amount = [];

            if (settingHelper('vat_and_tax_type') == 'order_base') {
                foreach ($seller_carts as $key => $seller_cart) {
                    $sub_total = 0;
                    $tax_amount[$key] = 0;
                    foreach ($seller_cart as $cart) {
                        if (!addon_is_activated('ramdhani') && settingHelper('shipping_fee_type') == 'product_base')
                        {
                            $shipping_cost = $cart->shipping_cost;
                        }

                        if (settingHelper('vat_type') == 'before_tax')
                        {
                            $sub_total += $cart->price * $cart->quantity;
                        }
                        else{
                            $sub_total += (($cart->price * $cart->quantity) + ($shipping_cost * $cart->quantity)) - (($cart->discount * $cart->quantity) + $cart->coupon_discount);
                        }
                    }

                    if (settingHelper('order_wise_tax_percentage'))
                    {
                        $tax_amount[$key] += ($sub_total * settingHelper('order_wise_tax_percentage')) / 100;
                    }
                }
            }

            if (settingHelper('shipping_fee_type') == 'invoice_base') {
                foreach ($seller_carts as $key => $seller_cart) {
                    $sub_total              = 0;
                    foreach ($seller_cart as $cart) {
                        $sub_total          += $cart->price * $cart->quantity;
                    }
                    foreach (settingHelper('invoice_based_shipping_fee') as $invoice_key=> $item) {
                        if ($item['min_amount'] <= $sub_total && $item['max_amount'] >= $sub_total)
                        {
                            $fee[$key] = $item['fee'];
                            break;
                        }
                        else{
                            $fee[$key] = settingHelper('shipping_fee_default_rate');
                        }
                    }
                }
            }
            $i = 0;
            foreach ($seller_carts as $key => $seller_cart) {
                $group_carts[$key] = [
                    'id'            => '',
                    'seller_id'     => $key,
                    'name'          => @$seller_cart[0]->seller->shop_name,
                    'image'         => @$seller_cart[0]->seller->image_90x60,
                    'code'          => '',
                    'is_applied'    => 0,
                    'discount'      => 0,
                    'shipping_cost' => settingHelper('shipping_fee_type') == 'invoice_base' ? (array_key_exists($key,$fee) ? $fee[$key] : 0) : $shipping_cost,
                    'tax'           => array_key_exists($key, $tax_amount) ? $tax_amount[$key] : 0,
                ];
                $i++;
            }

            if ($user)
            {
                $id = $user->id;
            }
            else{
                $id = $walk_user->id;
            }

            $orders = Order::where('trx_id', $carts->first()->trx_id)->where('user_id', $id)->where('status', 0)->get();

            foreach ($orders as $order) {
                $order->orderDetails()->delete();
                $order->delete();
            }

        }


        return $group_carts;
    }

    protected function sessionCheckouts($carts): array
    {
        $group_carts = $tax_amount = $seller_carts = [];

        $shipping_cost = 0;

        foreach ($carts as $cart)
        {
            if (!array_key_exists($cart['seller_id'],$seller_carts))
            {
                $seller_carts[$cart['seller_id']] = $cart;
            }
        }


        if (settingHelper('shipping_fee_type') == 'flat_rate') {
            $shipping_cost = settingHelper('shipping_fee_flat_rate');
        }

        if (settingHelper('vat_and_tax_type') == 'order_base') {
            $tax_repo       = new VatTaxRepository();
            $texes          = $tax_repo->activeTaxes();

            foreach ($seller_carts as $key => $seller_cart) {
                $tax_amount[$key]       = 0;

                $sub_total              = $seller_cart['price'] * $seller_cart['quantity'];

                foreach ($texes as $tax) :
                    $tax_amount[$key]   += ($sub_total * $tax->percentage) / 100;
                endforeach;
            }
        }

        /*if (settingHelper('shipping_fee_type') == 'invoice_base') {
            $fee = [];
            foreach ($seller_carts as $key => $seller_cart) {
                $sub_total    = $seller_cart['price'] * $seller_cart['quantity'];
                foreach (settingHelper('invoice_based_shipping_fee') as $invoice_key=> $item) {
                    if ($item['min_amount'] <= $sub_total && $item['max_amount'] >= $sub_total)
                    {
                        $fee[$key] = $item['fee'];
                    }
                }
                if (!array_key_exists($key,$fee))
                    $fee[$key] = settingHelper('shipping_fee_default_rate');


            }

            $shipping_cost = array_sum($fee);
        }*/

        foreach ($seller_carts as $key=> $seller_cart)
        {
            $group_carts[$key] = [
                'id'            => '',
                'seller_id'     => $key,
                'name'          => @$seller_cart['shop_name'],
                'image'         => @$seller_cart['shop_image'],
                'code'          => '',
                'is_applied'    => 0,
                'discount'      => 0,
                'shipping_cost' => $shipping_cost,
                'tax'           => array_key_exists($key, $tax_amount) ? $tax_amount[$key] : 0,
            ];
        }

        return $group_carts;
    }

    public function shippingCostFind($carts, $data)
    {
        $shipping_cost  = 0;

        $seller_carts   = $carts->groupBy('seller_id');
        $shipping_repo  = new ShippingRepository();
        $city           = $shipping_repo->getCity($data['city_id']);
        $cost           = 0;
        $deliveryMethod = $data["deliveryMethod"] ?? "Standard";

        if ($city) {
            // Validate Express Delivery - only available in Dhaka district
            if ($deliveryMethod === "Express Delivery") {
                // Check if the city/state is in Dhaka district
                // Assuming Dhaka district has a specific state_id or name
                $dhakaStateId = 3045; // You may need to adjust this based on your database
                $isDhakaDistrict = ($city->state_id == $dhakaStateId ||
                                   stripos($city->state->name ?? '', 'Dhaka') !== false ||
                                   stripos($city->name ?? '', 'Dhaka') !== false);

                if (!$isDhakaDistrict) {
                    // Express delivery is not available for non-Dhaka districts
                    // Return error or fall back to standard delivery
                    return [
                        'error' => 'Express delivery is only available in Dhaka district',
                        'shipping_cost' => 0
                    ];
                }
            }

            switch ($deliveryMethod) {
                case "Pick from Store":
                    $cost = $city->pickup_store_cost ?? 0;
                    break;
                case "Express Delivery":
                    $cost = $city->express_delivery_cost ?? 0;
                    break;
                default:
                    // Standard delivery
                    $cost = $city->cost ?? 0;

                    // Check if ANY product has free shipping (standard delivery is free if at least one product has free_shipping)
                    $hasFreeShipping = false;
                    foreach ($carts as $cart) {
                        if ($cart->product && $cart->product->free_shipping == 1) {
                            $hasFreeShipping = true;
                            break;
                        }
                    }

                    // If any product has free shipping, no delivery charge for standard
                    if ($hasFreeShipping) {
                        $cost = 0;
                    }
                    break;
            }
        }

        foreach ($seller_carts as $key => $seller_cart) {
            $shipping_cost += $cost;
        }

        return $shipping_cost;
    }

    public function cartFind($product_id,$variant)
    {
        return Cart::where('user_id',authId())->where('product_id',$product_id)->where('variant',$variant)->first();
    }

    //mobile api
    public function cartList($user,$data=[])
    {
        if ($user)
        {
            $carts = Cart::with('product.stock:id,product_id,image,name,sku,current_stock',
                'product:slug,user_id,price,id,thumbnail,minimum_order_quantity,is_refundable,current_stock,shipping_fee_depend_on_quantity,special_discount,special_discount_start,special_discount_end,special_discount_type,is_digital','seller:user_id,shop_name,logo')
                ->where('user_id', $user->id)->latest()->get();
        }
        else{
            if (array_key_exists('trx_id',$data))
            {
                $trx_id = $data['trx_id'];
            }
            else{
                $trx_id = Str::random(21);
            }

            Cart::where('created_at','<',Carbon::now()->subDays(2))->delete();

            $carts = Cart::with('product.stock:id,product_id,image,name,sku,current_stock',
                'product:slug,user_id,price,id,thumbnail,minimum_order_quantity,is_refundable,current_stock,shipping_fee_depend_on_quantity,special_discount,special_discount_start,special_discount_end,special_discount_type,is_digital','seller:user_id,shop_name,logo')
                ->where('user_id', getWalkInCustomer()->id)->where('trx_id',$trx_id)->latest()->get();
        }

        return $carts;
    }

    public function appliedCoupons($request)
    {
        $coupons = [];
        if (array_key_exists('trx_id',$request)):
            $checkouts = Checkout::with('coupon')->where('trx_id',$request['trx_id'])->where('status',1)->get();
            foreach ($checkouts as $key =>$checkout) {
                $coupon = $checkout->coupon;
                $productIds = [];

                // Determine if this is a product-based coupon
                // Check for both 'product_base' type OR null type with product_ids set
                $isProductBased = ($coupon->type == 'product_base' ||
                                   (empty($coupon->type) && !empty($coupon->product_id)));

                if ($isProductBased && $coupon->product_id) {
                    $productIds = is_array($coupon->product_id) ? $coupon->product_id : json_decode($coupon->product_id, true);
                    if (!is_array($productIds)) {
                        $productIds = [];
                    }
                }

                // If type is null but product_ids exist, treat as product_base
                $couponType = $coupon->type;
                if (empty($couponType) && !empty($productIds)) {
                    $couponType = 'product_base';
                }

                $coupons[] = [
                    'coupon_id'    => $coupon->id,
                    'code'         => $coupon->code,
                    'title'        => $coupon->title,
                    'status'       => $coupon->status,
                    'discount_type'=> $coupon->discount_type,
                    'discount'     => $checkout->coupon_discount,
                    'coupon_discount'=> $coupon->discount,
                    'applicable_on_discount' => $coupon->applicable_on_discount ?? 1,
                    'type'         => $couponType,
                    'product_ids'  => $productIds,
                ];
            }
        endif;
        return $coupons;
    }

    /**
     * Recalculate coupon discounts for all cart items
     * Called after adding/updating cart to ensure coupon discounts are current
     * This method follows the same logic as applyCoupon() to ensure consistency
     */
    protected function recalculateCouponDiscounts($trx_id, $user)
    {
        // Reset all coupon discounts to 0 first
        Cart::where('trx_id', $trx_id)
            ->update([
                'coupon_discount' => 0,
                'coupon_applied' => 0
            ]);

        // Get all active coupons for this transaction
        $checkouts = \App\Models\Checkout::with('coupon')
            ->where('trx_id', $trx_id)
            ->where('status', 1)
            ->get();

        if ($checkouts->isEmpty()) {
            return; // No active coupons
        }

        // Get all cart items for this transaction
        $carts = Cart::with('product:id,special_discount,special_discount_type,special_discount_start,special_discount_end')
            ->where('trx_id', $trx_id)
            ->get();

        if ($carts->isEmpty()) {
            return;
        }

        $now = now()->format('Y-m-d H:i:s');

        // Get campaign product IDs using CampaignPricingService
        // This ensures proper time-based validation for active campaigns
        $campaignProductIds = [];
        try {
            if (class_exists(\App\Services\CampaignPricingService::class)) {
                $pricingService = app(\App\Services\CampaignPricingService::class);
                $activeCampaigns = $pricingService->getActiveCampaigns();

                if ($activeCampaigns && $activeCampaigns->isNotEmpty()) {
                    foreach ($activeCampaigns as $campaign) {
                        // Get all products for this actively running campaign
                        // Using activeEventProducts relation which respects is_active and status
                        $campaignProducts = $campaign->activeEventProducts()
                            ->whereIn('product_id', $carts->pluck('product_id'))
                            ->where('is_active', 1)
                            ->where('status', 'active')
                            ->pluck('product_id')
                            ->toArray();
                        $campaignProductIds = array_merge($campaignProductIds, $campaignProducts);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Campaign check failed: ' . $e->getMessage());
        }
        $campaignProductIds = array_unique($campaignProductIds);

        // Process each coupon
        foreach ($checkouts as $checkout) {
            $coupon = $checkout->coupon;

            if (!$coupon || $coupon->status != 1) {
                continue;
            }

            // Check coupon date validity
            if ($coupon->start_date > $now || $coupon->end_date < $now) {
                continue;
            }

            // Determine eligible carts
            $eligibleCarts = $carts;

            // Filter by seller if coupon is seller-specific
            if ($coupon->user_id > 1) {
                $eligibleCarts = $eligibleCarts->where('seller_id', $coupon->user_id);
            }

            // Filter by product if coupon is product-based
            if ($coupon->type == 'product_base' && $coupon->product_id) {
                $productIds = is_array($coupon->product_id) ? $coupon->product_id : json_decode($coupon->product_id, true);
                $eligibleCarts = $eligibleCarts->whereIn('product_id', $productIds);
            }

            if ($eligibleCarts->isEmpty()) {
                continue;
            }

            // Calculate total discount for this coupon
            $calculated_discount = 0;
            foreach ($eligibleCarts as $cart) {
                // Check if product is discounted
                $isDiscountedProduct = false;
                if ($cart->product) {
                    if ($cart->product->special_discount > 0 &&
                        $cart->product->special_discount_start <= $now &&
                        $cart->product->special_discount_end >= $now) {
                        $isDiscountedProduct = true;
                    }
                    if (in_array($cart->product_id, $campaignProductIds)) {
                        $isDiscountedProduct = true;
                    }
                }

                // Calculate discount based on applicable_on_discount
                if ($coupon->applicable_on_discount == 0) {
                    // Only apply to non-discounted products
                    if (!$isDiscountedProduct) {
                        $cartPriceTotal = $cart->price * $cart->quantity;
                        $calculated_discount += $this->calculateDiscountForCoupon($coupon, $cartPriceTotal);
                    }
                } else {
                    // Apply to all products - use selling price (cart->price is already after discount)
                    $cartPriceTotal = $cart->price * $cart->quantity;
                    $calculated_discount += $this->calculateDiscountForCoupon($coupon, $cartPriceTotal);
                }
            }

            // Apply maximum discount cap
            $total_coupon_discount = min($calculated_discount, $coupon->maximum_discount);

            // Now distribute the discount proportionally among eligible products
            $eligible_total = 0;
            $eligible_carts = [];

            foreach ($eligibleCarts as $cart) {
                $isDiscountedProduct = false;
                if ($cart->product) {
                    if ($cart->product->special_discount > 0 &&
                        $cart->product->special_discount_start <= $now &&
                        $cart->product->special_discount_end >= $now) {
                        $isDiscountedProduct = true;
                    }
                    if (in_array($cart->product_id, $campaignProductIds)) {
                        $isDiscountedProduct = true;
                    }
                }

                // Collect eligible carts based on applicable_on_discount
                if ($coupon->applicable_on_discount == 0) {
                    if (!$isDiscountedProduct) {
                        $eligible_total += $cart->price * $cart->quantity;
                        $eligible_carts[] = $cart;
                    }
                } else {
                    // Include all products - use selling price (cart->price is already after discount)
                    $eligible_total += $cart->price * $cart->quantity;
                    $eligible_carts[] = $cart;
                }
            }

            // Distribute discount proportionally among eligible products
            foreach ($eligible_carts as $cart) {
                $isDiscountedProduct = false;
                if ($cart->product) {
                    if ($cart->product->special_discount > 0 &&
                        $cart->product->special_discount_start <= $now &&
                        $cart->product->special_discount_end >= $now) {
                        $isDiscountedProduct = true;
                    }
                    if (in_array($cart->product_id, $campaignProductIds)) {
                        $isDiscountedProduct = true;
                    }
                }

                // Calculate cart total for proportion
                // Use selling price (cart->price is already after discount)
                $cart_total = $cart->price * $cart->quantity;

                $proportion = $eligible_total > 0 ? $cart_total / $eligible_total : 0;
                $cart->coupon_discount += $total_coupon_discount * $proportion;
                $cart->coupon_applied = 1;
            }
        }

        // Save all updated cart items
        foreach ($carts as $cart) {
            $cart->save();
        }
    }

    /**
     * Calculate discount amount for a coupon based on amount
     * Used by recalculateCouponDiscounts to match applyCoupon logic
     */
    protected function calculateDiscountForCoupon($coupon, $amount)
    {
        if ($coupon->discount_type == 'flat') {
            return $coupon->discount;
        } else {
            // Percentage discount
            return $amount * ($coupon->discount / 100);
        }
    }

    public function deleteBuyNow()
    {
        return Cart::when(authUser(),function ($query){
            $query->where('user_id',authId());
        })->when(!authUser(),function ($query){
            $query->where('user_id',getWalkInCustomer()->id);
        })->where('is_buy_now',1)->delete();
    }
}
