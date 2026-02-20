<?php

namespace App\Repositories\Admin\Marketing;

use App\Models\Cart;
use App\Models\Checkout;
use App\Models\Coupon;
use App\Models\CouponLanguage;
use App\Models\User;
use App\Repositories\Interfaces\Admin\Marketing\CouponInterface;
use App\Repositories\Interfaces\Admin\Marketing\CouponLangInterface;
use App\Traits\ImageTrait;
use Carbon\Carbon;
use Sentinel;

class CouponRepository implements CouponInterface
{
    use ImageTrait;

    protected $couponLanguage;

    public function __construct(CouponLangInterface $couponLanguage)
    {
        $this->couponLanguage        = $couponLanguage;
    }

    public function get($id)
    {
        return Coupon::find($id);
    }

    public function getByLang($id, $lang)
    {
        if($lang == null):
            $couponByLang = CouponLanguage::with('coupon')->where('lang', 'en')->where('coupon_id', $id)->first();
        else:
            $couponByLang = CouponLanguage::with('coupon')->where('lang', $lang)->where('coupon_id', $id)->first();
            if (blank($couponByLang)):
                $couponByLang = CouponLanguage::with('coupon')->where('lang', 'en')->where('coupon_id', $id)->first();
                $couponByLang['translation_null'] = 'not-found';
            endif;
        endif;

        return $couponByLang;
    }

    public function all()
    {
        return Coupon::latest()->when(Sentinel::getUser()->user_type == 'seller', function ($q){
                            $q->where('user_id', Sentinel::getUser()->id);
                        });
    }

    public function paginate($request, $limit)
    {
        return $this->all()->paginate($limit);
    }

    public function store($request)
    {
        $coupon = new Coupon();
        $coupon->user_id            = Sentinel::getUser()->user_type == 'seller' ? Sentinel::getUser()->id : 1;
        $coupon->type               = $request->type;
        $coupon->code               = $request->code;
        $dates = explode(" - ", $request->date);

        $coupon->start_date         = Carbon::createFromFormat('m-d-Y g:ia', $dates[0]);
        $coupon->end_date           = Carbon::createFromFormat('m-d-Y g:ia', $dates[1]);
        $coupon->discount_type      = $request->discount_type;
        $coupon->discount           = $request->discount_type == 'percent' ? $request->discount : priceFormatUpdate($request->discount,settingHelper('default_currency'));
        $coupon->product_id         = $request->product_id;
        $coupon->minimum_shopping   = $request->minimum_shopping == '' ? 0 : priceFormatUpdate($request->minimum_shopping,settingHelper('default_currency'));
        $coupon->maximum_discount   = $request->maximum_discount == '' ? null : priceFormatUpdate( $request->maximum_discount,settingHelper('default_currency'));
        $coupon->applicable_on_discount = (int)($request->applicable_on_discount ?? 0);

        if ($request->banner != ''):
            $coupon->banner        = $this->getImageWithRecommendedSize($request->banner,145,110);
            $coupon->banner_id     = $request->banner;
        else:
            $coupon->banner        = [];
        endif;

        $coupon->save();

        $request['coupon_id'] = $coupon->id;
        if ($request->lang == ''):
            $request['lang']    = 'en';
        endif;

        $this->couponLanguage->store($request);
        return true;
    }
    public function update($request)
    {
        $coupon                     = $this->get($request->coupon_id);
        $coupon->type               = $request->type;
        $coupon->code               = $request->code;
        $dates = explode(" - ", $request->date);

        $coupon->start_date         = Carbon::createFromFormat('m-d-Y g:ia', $dates[0]);
        $coupon->end_date           = Carbon::createFromFormat('m-d-Y g:ia', $dates[1]);
        $coupon->discount_type      = $request->discount_type;
        $coupon->discount           = $request->discount_type == 'percent' ? $request->discount : priceFormatUpdate($request->discount,settingHelper('default_currency'));
        $coupon->product_id         = $request->product_id;
        $coupon->minimum_shopping   = $request->minimum_shopping == '' ? 1 : priceFormatUpdate($request->minimum_shopping,settingHelper('default_currency'));
        $coupon->maximum_discount   = $request->maximum_discount == '' ? null : priceFormatUpdate( $request->maximum_discount,settingHelper('default_currency'));
        $coupon->applicable_on_discount = (int)($request->applicable_on_discount ?? 0);

        if ($request->banner != ''):
            $this->deleteSingleFile($coupon->banner, 'image_145x110');
            $coupon->banner        = $this->getImageWithRecommendedSize($request->banner,145,110);
            $coupon->banner_id     = $request->banner;
        else:
            $coupon->banner        = [];
            $coupon->banner_id     = null;
        endif;

        $coupon->save();

        if ($request->coupon_lang_id == '') :
            $this->couponLanguage->store($request);
        else:
            $this->couponLanguage->update($request);
        endif;
        return true;
    }

    public function statusChange($request){
            $coupon            = $this->get($request['id']);
            $coupon->status    = $request['status'];
            $coupon->save();
            return true;
    }

    public function couponPage()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        return Coupon::with('currentLanguage')->where('start_date','<=',$now)->where('end_date','>=',$now)->where('status',1)->latest()->paginate(10);
    }

    public function pointPage()
    {
        $authUser = User::where('id', authUser()->id)->first();

        return $authUser->point;
    }

    public function sellerCoupons($id)
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        return  Coupon::with('currentLanguage')->where(function ($q) use ($now) {
            $q->where('end_date', '>=', $now);
        })->where('user_id', $id)->where('status', 1)->latest()->paginate(16);
    }

    public function deleteCoupon($request)
    {
        // Update Checkout status to 0 (soft delete)
        Checkout::where('coupon_id',$request->coupon_id)
            ->where('user_id',$request->user_id)
            ->where('trx_id',$request->trx_id)
            ->update(['status' => 0]);

        // CRITICAL FIX: Get all carts for this user/trx to refresh pricing
        $carts = Cart::where('user_id',$request->user_id)
            ->where('trx_id',$request->trx_id)
            ->get();

        // Reset coupon_discount and recalculate prices for each cart item
        foreach ($carts as $cart) {
            // Store original price before any calculations
            $original_price = $cart->original_price ?? $cart->product->price;

            // Reset coupon-related fields
            $cart->coupon_discount = 0;
            $cart->coupon_applied = 0;

            // Recalculate price with campaign/special discount (without coupon)
            $price = $original_price;
            $discount = 0;

            if (class_exists(\App\Services\CampaignPricingService::class)) {
                $pricingService = app(\App\Services\CampaignPricingService::class);
                $campaignPricing = $pricingService->getCampaignPrice($cart->product_id);

                if ($campaignPricing && isset($campaignPricing['has_campaign']) && $campaignPricing['has_campaign']) {
                    // Campaign price
                    $price = $campaignPricing['price'];
                    $discount = $original_price - $price;
                } else {
                    // Check for special discount
                    if ($cart->product && $cart->product->special_discount > 0) {
                        $specialStart = $cart->product->special_discount_start;
                        $specialEnd = $cart->product->special_discount_end;
                        $now = now()->format('Y-m-d H:i:s');

                        if ($specialStart <= $now && $specialEnd >= $now) {
                            if ($cart->product->special_discount_type == 'percent') {
                                $discountAmount = $original_price * ($cart->product->special_discount / 100);
                            } else {
                                $discountAmount = $cart->product->special_discount;
                            }
                            $discount = min($discountAmount, $original_price);
                            $price = $original_price - $discount;
                        }
                    }
                }
            }

            // Update cart with recalculated prices
            $cart->price = max(0, $price);
            $cart->discount = max(0, $discount);
            $cart->save();
        }

        return true;
    }

}
