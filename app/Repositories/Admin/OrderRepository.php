<?php

namespace App\Repositories\Admin;

use App\Constants\DeliveryMethodConstant;
use App\Jobs\OrderSyncJob;
use App\Models\DeliveryAddress;
use App\Models\Division;
use App\Models\State;
use App\Repositories\Erp\CustomerRepository;
use App\Repositories\Erp\OrderErpRepository;
use App\Services\ElitbuzzSmsService;
use Exception;
use PDF;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Checkout;
use App\Models\City;
use App\Traits\pdfTrait;
use App\Models\ClassCity;
use App\Models\OrderDetail;
use App\Models\ProductCity;
use App\Models\ProductStock;
use App\Traits\PaymentTrait;
use App\Models\SellerProfile;
use App\Traits\SendMailTrait;
use App\Models\PaymentHistory;
use App\Models\DeliveryHistory;
use App\Traits\SendNotification;
use App\Models\CommissionHistory;
use App\Models\PointSetting;
use App\Traits\RandomStringTrait;
use Illuminate\Support\Facades\DB;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Repositories\Admin\Addon\WalletRepository;
use App\Repositories\Interfaces\Admin\OrderInterface;
use App\Repositories\Interfaces\Admin\LanguageInterface;
use App\Repositories\Interfaces\Admin\Product\ProductInterface;

class OrderRepository implements OrderInterface
{
    use RandomStringTrait, PaymentTrait, SendMailTrait, pdfTrait, SendNotification;

    protected $lang;
    protected $product;
    protected $wallet;
    protected $smsService;

    public function __construct(LanguageInterface $lang, ProductInterface $product, \App\Repositories\Interfaces\Admin\Addon\WalletInterface $wallet, ElitbuzzSmsService $smsService)
    {
        $this->lang = $lang;
        $this->product = $product;
        $this->wallet = $wallet;
        $this->smsService = $smsService;
    }



    public function all()
    {
        return Order::latest();
    }

    public function get($id)
    {
        return Order::where('id', $id)
            ->orWhere(function ($q) use ($id) {
                $q->where('erp_code', $id)
                    ->whereNotNull('erp_code');
            })
            ->orWhere(function ($q) use ($id) {
                // Search by code field (with or without # prefix)
                $q->where('code', $id)
                    ->orWhere('code', '#' . $id);
            })
            ->first();
    }

    public function getDetail($id)
    {
        return OrderDetail::find($id);
    }

    public function paginate($request, $limit)
    {
        $start_date = $end_date = null;

        if ($request->dt != null):
            $dates = explode(" - ", $request->dt);
            $start_date = Carbon::createFromFormat('m-d-Y g:ia', $dates[0]);
            $end_date = Carbon::createFromFormat('m-d-Y g:ia', $dates[1]);
        endif;
        $orders = Order::with('orderDetails.product')->withCount('orderDetails')
            ->when($request->ds != null, function ($query) use ($request) {
                $query->where('delivery_status', $request->ds);
            })
            ->when($request->dt != null, function ($query) use ($start_date, $end_date) {
                $query->whereDate('orders.created_at', '>=', $start_date)
                    ->whereDate('orders.created_at', '<=', $end_date);
            })
            ->when($request->sl != null, function ($query) use ($request) {
                $seller = sellerProfile::find($request->sl);
                $query->where('orders.seller_id', $seller->user_id);
            })
            ->when($request->q != null, function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->q . '%');
                $query->orwhereHas('user', function ($q) use ($request) {
                    $q->where('first_name', 'like', '%' . $request->q . '%');
                    $q->orwhere('last_name', 'like', '%' . $request->q . '%');
                    $q->orWhere(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'LIKE', "%" . $request->q . "%");
                    $q->orwhere('phone', 'like', '%' . $request->q . '%');
                });
            })
            /*->when(settingHelper('seller_system') != 1, function ($q) {
                $q->where('seller_id',1);
            })*/ ;

        $sorting = $request->s;
        switch ($sorting) {
            case 'latest_on_top':
                $orders->orderByDesc('id');
                break;
            case 'oldest_on_top':
                $orders->orderBy('id');
                break;
            case 'price_high':
                $orders->orderByDesc('total_amount');
                break;
            case 'price_low':
                $orders->orderBy('total_amount');
                break;
            case 'total_product_high':
                $orders->orderByDesc('order_details_count');
                break;
            case 'total_product_low':
                $orders->orderBy('order_details_count');
                break;
            default:
                $orders->orderBy('id', 'desc');
                break;
        }
        return $orders->paginate($limit);
    }

    public function sellerOrder($request, $limit)
    {
        $start_date = null;
        $end_date = null;
        if ($request->dt != null):
            $dates = explode(" - ", $request->dt);
            $start_date = Carbon::createFromFormat('m-d-Y g:ia', $dates[0]);
            $end_date = Carbon::createFromFormat('m-d-Y g:ia', $dates[1]);
        endif;
        $orders = Order::withCount('orderDetails')
            ->where('seller_id', '!=', 1)
            ->with(['orderDetails.product.sellerProfile'])
            ->when($request->ds != null, function ($query) use ($request) {
                $query->where('delivery_status', $request->ds);
            })
            ->when($request->dt != null, function ($query) use ($start_date, $end_date) {
                $query->whereDate('orders.created_at', '>=', $start_date)
                    ->whereDate('orders.created_at', '<=', $end_date);
            })
            ->when($request->q != null, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->where('first_name', 'like', '%' . $request->q . '%');
                        $q->orwhere('last_name', 'like', '%' . $request->q . '%');
                        $q->orWhere(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'LIKE', "%" . $request->q . "%");
                        $q->orwhere('phone', 'like', '%' . $request->q . '%');
                    });
                });
                $query->orWhere(function ($query) use ($request) {
                    $query->where('code', 'like', '%' . $request->q . '%');
                });
            })
            ->when($request->sl != null, function ($query) use ($request) {
                $seller = SellerProfile::find($request->sl);
                $query->where('orders.seller_id', $seller->user_id);
            })->where('status', 1);

        $sorting = $request->s;
        switch ($sorting) {
            case 'latest_on_top':
                $orders->orderByDesc('id');
                break;
            case 'oldest_on_top':
                $orders->orderBy('id');
                break;
            case 'price_high':
                $orders->orderByDesc('total_amount');
                break;
            case 'price_low':
                $orders->orderBy('total_amount');
                break;
            case 'total_product_high':
                $orders->orderByDesc('order_details_count');
                break;
            case 'total_product_low':
                $orders->orderBy('order_details_count');
                break;
            default:
                $orders->orderBy('id', 'desc');
                break;
        }
        return $orders->paginate($limit);

    }

    public function adminOrder($request, $limit)
    {
        $start_date = null;
        $end_date = null;
        if ($request->dt != null):
            $dates = explode(" - ", $request->dt);
            $start_date = Carbon::createFromFormat('m-d-Y g:ia', $dates[0]);
            $end_date = Carbon::createFromFormat('m-d-Y g:ia', $dates[1]);
        endif;
        $orders = Order::with('orderDetails.product')->withCount('orderDetails')
            ->where('seller_id', 1)
            ->when($request->ds != null, function ($query) use ($request) {
                $query->where('delivery_status', $request->ds);
            })
            ->when($request->dt != null, function ($query) use ($start_date, $end_date) {
                $query->whereDate('orders.created_at', '>=', $start_date)
                    ->whereDate('orders.created_at', '<=', $end_date);
            })
            ->when($request->q != null, function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->q . '%');
                $query->orwhereHas('user', function ($q) use ($request) {
                    $q->where('first_name', 'like', '%' . $request->q . '%');
                    $q->orwhere('last_name', 'like', '%' . $request->q . '%');
                    $q->orWhere(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'LIKE', "%" . $request->q . "%");
                    $q->orwhere('phone', 'like', '%' . $request->q . '%');
                });
            })->where('status', 1);

        $sorting = $request->s;
        switch ($sorting) {
            case 'latest_on_top':
                $orders->orderByDesc('id');
                break;
            case 'oldest_on_top':
                $orders->orderBy('id');
                break;
            case 'price_high':
                $orders->orderByDesc('total_amount');
                break;
            case 'price_low':
                $orders->orderBy('total_amount');
                break;
            case 'total_product_high':
                $orders->orderByDesc('order_details_count');
                break;
            case 'total_product_low':
                $orders->orderBy('order_details_count');
                break;
            default:
                $orders->orderBy('id', 'desc');
                break;
        }
        return $orders->paginate($limit);
    }

    public function pickupHubOrder($request, $limit)
    {
        $start_date = null;
        $end_date = null;

        if ($request->dt != null):
            $dates = explode(" - ", $request->dt);
            $start_date = Carbon::createFromFormat('m-d-Y g:ia', $dates[0]);
            $end_date = Carbon::createFromFormat('m-d-Y g:ia', $dates[1]);
        endif;

        $orders = Order::with('pickupHub')->withCount('orderDetails')
            ->whereNotNull('pickup_hub_id')
            ->when($request->ds != null, function ($query) use ($request) {
                $query->where('delivery_status', $request->ds);
            })
            ->when($request->dt != null, function ($query) use ($start_date, $end_date) {
                $query->whereDate('orders.created_at', '>=', $start_date)
                    ->whereDate('orders.created_at', '<=', $end_date);
            })
            ->when($request->sl != null, function ($query) use ($request) {
                $seller = sellerProfile::find($request->sl);
                $query->where('seller_id', $seller->user_id);
            })
            ->when($request->q != null, function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->q . '%');
            })
            ->when(settingHelper('seller_system') != 1, function ($q) {
                $q->where('seller_id', 1);
            })
            ->where('status', 1);

        $sorting = $request->s;
        switch ($sorting) {
            case 'latest_on_top':
                $orders->orderByDesc('id');
                break;
            case 'oldest_on_top':
                $orders->orderBy('id');
                break;
            case 'price_high':
                $orders->orderByDesc('total_amount');
                break;
            case 'price_low':
                $orders->orderBy('total_amount');
                break;
            case 'total_product_high':
                $orders->orderByDesc('order_details_count');
                break;
            case 'total_product_low':
                $orders->orderBy('order_details_count');
                break;
            default:
                $orders->orderBy('id', 'desc');
                break;
        }
        return $orders->paginate($limit);
    }

    public function sellerProfile($id)
    {
        return SellerProfile::find($id);
    }

    public function invoiceDownload($id)
    {
        $order = $this->get($id);
        $order['font_name'] = $this->commonSetting();
        $pdf = PDF::loadView('admin.orders.invoice', [
            'order' => $order,
        ]);
        return $pdf->stream($order->code . '.pdf');
    }
    public function invoiceDownloadApi($id)
    {
        try {
            $order = $this->get($id);
            $order['font_name'] = $this->commonSetting();
            $pdf = PDF::loadView('admin.orders.invoice', [
                'order' => $order,
            ]);

            return $pdf->download($order->code . '.pdf');
        } catch (\Exception $e) {
            return false;
        }
    }

    public function assignDeliveryHero($request)
    {
        $order = $this->get($request->id);
        $order->delivery_hero_id = $request->delivery_hero;
        $order->delivery_hero_assign_at = Carbon::now();
        $order->save();

        $old_assign_history = DeliveryHistory::where('order_id', $order->id)->where('event', 'delivery_hero_assigned')->first();
        $delivery_history = new DeliveryHistory();
        $delivery_history->event = $old_assign_history ? 'delivery_hero_changed' : 'delivery_hero_assigned';
        $delivery_history->user_id = Sentinel::getUser()->id;
        $delivery_history->order_id = $order->id;
        $delivery_history->delivery_hero_id = $request->delivery_hero;

        $delivery_history->save();
        return true;
    }

    public function orderCreate($request)
    {
        return [];
    }

    public function deliveryStatusChange($request)
    {
        DB::beginTransaction();
        try {

            //            if ($request->delivery_status == 'confirm'):
//
//                $store = $request->store;
//                if (empty($store)):
//                    DB::rollback();
//                    return 'store_not_selected';
//                endif;
//
//                $orderDetails = OrderDetail::where('order_id', $request->id)->get();
//
//                foreach ($orderDetails as $orderDetail):
//                    $productStockData = ProductStock::where('product_id', $orderDetail->product_id)
//                        ->where('store_id', $request->store);
//
//                    if ($productStockData->exists() && $productStockData->first()->current_stock >= $orderDetail->quantity):
//                        $productStockData->decrement('current_stock', $orderDetail->quantity);
//                    else:
//                        DB::rollback();
//                        return 'product_not_available';
//                    endif;
//                endforeach;
//            endif;

            $id = !empty($request->code) ? $request->code : $request->id;

            $order = $this->get($id);
            $previous_status = $order->delivery_status;
            $order->delivery_status = $request->delivery_status;

            if ($request->delivery_status == 'canceled'):
                $this->cancelOrder($order, '', '', $request->has('user_id') ? $request->user_id : '');
            else:
                //                if ($request->delivery_status == 'delivered'):
                // $this->wallet->manageDeliveredOrder($order);
//                    foreach ($order->orderDetails as $key => $orderDetail) :
//                        $this->saleUpdate($orderDetail);
//                    endforeach;

                //update user point
//                    $this->updateUserPoint($order->user_id, $order->sub_total);

                //                endif;

                if ($previous_status == 'canceled'):
                    if (!$this->adjustQuantity($order, true)):
                        DB::rollback();
                        return 'product_not_available';
                    endif;
                endif;
                $this->deliveryEvent('order_' . $request->delivery_status . '_event', $order->id, $order->delivery_hero_id, '', $request->has('user_id') ? $request->user_id : '');
            endif;

            $order->save();

            if (settingHelper('disable_email_confirmation') != 1) {
                $this->SendNotification(Sentinel::findById($order->user_id), __("Your order (:code) status is updated", ['code' => $order->code]), 'success', "invoice/{$order->trx_id}", __('Your order (:code) status is updated is :delivery_status now.', ['code' => $order->code, 'delivery_status' => $request->delivery_status]));
                //            sendMail($order->user, $order, 'order_status_update', '',);
                if ($order->billing_address['email']) {
                    $this->SendMail($order->billing_address['email'], 'Order Status Updated', $order, 'email.order-status-update', url('invoice/' . $order->trx_id));
                }

                if (
                    $request->delivery_status === 'confirm' &&
                    (isset($order->user->phone) || isset($order->billing_address['phone_no']))
                ) {
                    $this->smsService->orderConfirm($order->billing_address['phone_no'] ?? $order->user->phone, [
                        'customer' => $order->billing_address['name'] ?? $order->user->first_name,
                        'tracking_number' => $order->code,
                        'invoice_url' => url("invoice/{$order->trx_id}"),
                    ]);
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function updateUserPoint($user_id, $price)
    {
        try {
            $point = PointSetting::where('status', 1)->first();

            $newPurchasePoints = ($point->point_to_money / $point->point) * $price;


            $user = User::find($user_id);
            $user->increment('point', $newPurchasePoints);
        } catch (\Exception $e) {
            return false;
        }

    }

    //    public function manageProductStock($orders)
//    {
//        foreach ($orders->orderDetails as $order):
//            $product = $this->product->get($order->product_id);
//            if ($product):
//                $product->current_stock += $order->quantity;
//                $product->save();
//            endif;
//            $product_stock = ProductStock::where('product_id', $order->product_id)
//                ->where('name', $order->variation)
//                ->first();
//            if ($product_stock) :
//                $product_stock->current_stock += $order->quantity;
//                $product_stock->save();
//            endif;
//        endforeach;
//    }

    public function paymentStatusChange($request)
    {
        $order = $this->get($request->id);
        $delivery_hero = $order->deliveryHero;
        if (!$delivery_hero && array_key_exists('paid_to_delivery_man', $request->all())) {
            return false;
        }

        $previous_payment_status = $order->payment_status;
        if ($request->payment_status == 'paid'):
            $order->payment_status = 'paid';
            // $this->wallet->insertPayment($order, $request->all());
            $this->paymentHistoryEvent("order_payment_{$request->payment_status}_event", $order->id, "With {$request['payment_type']}", $request->has('user_id') ? $request->user_id : '');
        elseif ($request->payment_status == 'unpaid'):

            $order->payment_status = 'unpaid';
            $this->paymentHistoryEvent("order_payment_{$request->payment_status}_event", $order->id, '', $request->has('user_id') ? $request->user_id : '');

            // if ($previous_payment_status == 'paid'):
            //     $order->payment_status = 'unpaid';
            //     // $this->wallet->removePayment($order, 'payment_status_changed');
            //     $this->paymentHistoryEvent('order_payment_refunded_to_wallet_event', $order->id, '',$request->has('user_id') ? $request->user_id : '');
            // else:
            //     $order->payment_status = 'unpaid';
            //     $this->paymentHistoryEvent("order_payment_{$request->payment_status}_event", $order->id, '',$request->has('user_id') ? $request->user_id : '');
            // endif;
        elseif ($request->payment_status == 'offline_payment'):
            $order->payment_status = 'paid';
            $this->paymentHistoryEvent("order_payment_{$request->payment_status}_event", $order->id, "With offline payment", $request->has('user_id') ? $request->user_id : '');
        endif;
        $order->save();
        return true;
    }

    //for frontend api
    public function orderByCode($orderCode)
    {
        return Order::with('orderDetails.product', 'deliveryHistories')
            ->where('code', $orderCode)->first();
    }

    public function orders($take)
    {
        if (authUser()) {
            return OrderDetail::with('product:id,thumbnail,slug,product_file_id', 'order')
                ->whereHas('order', function ($query) {
                    $query->where('user_id', authId());
                    $query->where('is_deleted', 0);
                    /*$query->when(settingHelper('seller_system') != 1, function ($q) {
                        $q->where('seller_id',1);
                    });*/
                    $query->where(function ($q) {
                        $q->where('status', 1)->orWhere('payment_type', 0);
                    });
                })->groupBy('order_id')->latest()->paginate($take);
        } else {
            return [];
        }
    }
    public function digitalProductOrders($limit, $token = null)
    {
        $user_id = authUser() ? authId() : $token;
        if ($user_id) {
            return OrderDetail::with('product:id,thumbnail,slug,product_file_id', 'order')
                ->whereHas('order', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                    $query->where('payment_status', 'paid');
                    $query->where('is_deleted', 0);
                    $query->where('status', 1);
                    $query->when(settingHelper('seller_system') != 1, function ($q) {
                        $q->where('seller_id', 1);
                    });
                })->whereHas('product', function ($q) {
                    $q->where('product_file_id', '!=', null);
                    $q->where('is_digital', 1);
                })
                ->Where(function ($qu) {
                    $qu->whereHas('refund', function ($q) {
                        $q->where('status', '!=', 'approved');
                        $q->Where('status', '!=', 'processed');
                    });
                    $qu->orWhereDoesntHave('refund');
                })

                ->groupBy('order_id')
                ->latest()
                ->paginate($limit);
        } else {
            return [];
        }
    }

    public function productOrderList($item, $user)
    {
        if ($user) {
            return OrderDetail::with([
                'product' => function ($q) use ($user) {
                    $q->withTrashed();
                },
                'order'
            ])
                ->whereHas('order', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                    $query->where('is_deleted', 0);
                    $query->where(function ($q) {
                        $q->where('status', 1)->orWhere('payment_type', 0);
                    });
                    $query->when(settingHelper('seller_system') != 1, function ($q) {
                        $q->where('seller_id', 1);
                    });
                })->groupBy('order_id')->latest()->paginate($item);
        } else {
            return [];
        }
    }

    public function deleteOrder($id, $user)
    {

        $order = $this->get($id);
        if ($order && $order->user_id == authId()) {
            $order->is_deleted = 1;
            $order->save();
        } else {
            return abort(403);
        }

        $this->SendNotification(Sentinel::findById(1), ' A Order has been removed', 'danger', "orders", __('See it in Details'));

        return $order;
    }

    //latest
    public function confirmOrder($checkout, $carts, $data, $user)
    {

        $walk_in_customer = getWalkInCustomer();

        $orders = [];

        if ($user && $carts->first()->user_id != $user->id) {
            abort(403);
        } elseif (!$user && $carts->first()->user_id != $walk_in_customer->id) {
            abort(403);
        }
        $coupon_discount = 0;
        $coupon_codes = '';
        if (count($carts) > 0) {
            foreach ($checkout as $key => $cart_group) {
                $sub_total = $total_discount = $total_tax = $shipping_cost = 0;
                if (array_key_exists('seller_id', $cart_group)) {

                    foreach ($carts->whereIn('seller_id', $cart_group['seller_id']) as $item) {

                        $sub_total += $item->price * $item->quantity;
                        $total_discount += $item->discount * $item->quantity;
                        $total_tax += $item->tax * $item->quantity;
                        $shipping_cost += $item->shipping_cost;

                        //sum all stocks
                        $totalStock = $item->product->stock->sum('current_stock');

                        if ($totalStock < $item->quantity) {
                            throw new Exception($item->product->product_name . ' out of stock');

                            return $item->product->product_name . ' is out of Stock';
                        }

                    }

                    $checkouts = Checkout::with('coupon')
                        ->where('trx_id', $data['trx_id'])
                        ->where('status', 1)
                        ->get();

                    $coupon_discount = $checkouts->sum('coupon_discount');
                    $coupon_codes = $checkouts->pluck('coupon.code')->implode(',');

                }

                // $shipping_cost += $cart_group['shipping_cost'];

                if (array_key_exists('pick_hub_id', $data) && !empty($data['pick_hub_id'])) {
                    $shipping_cost = 0;
                } elseif (settingHelper('shipping_fee_type') == 'area_base') {

                    $city_id = $data['form']['thana_id'];
                    $cityData = City::where('id', $city_id)->first();

                    $deliveryMethod = $data['deliveryMethod'];

                    switch ($deliveryMethod) {
                        case DeliveryMethodConstant::EXPRESS_DELIVERY:
                            $shipping_cost = $cityData->express_delivery_cost;
                            break;
                        case DeliveryMethodConstant::PICK_FROM_STORE:
                            $shipping_cost = 0;
                            //                            $shipping_cost = $cityData->pickup_store_cost;
                            break;
                        default:
                            $shipping_cost = $cityData->cost;
                            break;
                    }

                    $hasFreeShipping = $carts->contains(function ($cartItem) {
                        return isset($cartItem->product) && $cartItem->product->free_shipping == 1;
                    });

                    if ($hasFreeShipping) {
                        $shipping_cost = 0;
                    }

                }

                $total_tax += $cart_group['tax'];

                $total_amount = ($sub_total + $total_tax) - ($total_discount + $coupon_discount);
                $total_payable = $total_amount + $shipping_cost;

                $tax = [
                    'vat_tax_type' => settingHelper('vat_and_tax_type'),
                    'tax_type' => settingHelper('vat_type'),
                ];

                $billing_address = $delivery_address = [
                    'name' => $data['form']['name'],
                    'phone_no' => $data['form']['phone_no'],
                    'email' => $data['form']['email'],
                    'address_ids' => [
                        'division_id' => $data['form']['division_id'],
                        'district_id' => $data['form']['district_id'],
                        'thana_id' => $data['form']['thana_id'],
                    ],
                    'division' => Division::findOrFail($data['form']['division_id'])->name ?? "",
                    'district' => State::findOrFail($data['form']['district_id'])->name ?? "",
                    'thana' => City::findOrFail($data['form']['thana_id'])->name ?? "",
                    'address' => $data['form']['address'] ?? "",
                ];

                if (authUser()) {
                    $delivery_address['user_id'] = authId();

                    $existingAddress = DeliveryAddress::where('user_id', authId())->first();

                    if ($existingAddress) {
                        $existingAddress->update($delivery_address);
                    } else {
                        DeliveryAddress::create($delivery_address);
                    }

                } else {
                    // Store delivery address in session if user not logged in
                    session(['guest_delivery_address' => $delivery_address]);
                }

                $order = Order::create([
                    'seller_id' => $key,
                    'user_id' => $user ? $user->id : $walk_in_customer->id,
                    'billing_address' => $billing_address,
                    'shipping_address' => $billing_address,
                    'payment_type' => $data['payment_form']['payment_method'],
                    'delivery_method' => $data['deliveryMethod'],
                    'sub_total' => round($sub_total),
                    'discount' => round($total_discount),
                    'coupon_discount' => round($coupon_discount),
                    'coupon_codes' => $coupon_codes,
                    'total_tax' => round($total_tax),
                    'total_amount' => round($total_amount),
                    'shipping_cost' => round($shipping_cost),
                    'total_payable' => round($total_payable),
                    'code' => settingHelper('order_prefix') . '-' . $this->generate_random_string(10, 'number'),
                    'trx_id' => $carts->first()->trx_id,
                    'store_id' => array_key_exists('store_id', $data) && !empty($data['store_id']) ? $data['store_id'] : null,
                    'pickup_hub_id' => array_key_exists('pick_hub_id', $data) && !empty($data['pick_hub_id']) ? $data['pick_hub_id'] : null,
                    'date' => now(),
                    'shipping_method' => settingHelper('shipping_fee_type'), ///need to change
                    'is_coupon_system_active' => settingHelper('coupon_system'),
                    'tax_method' => $tax,
                ]);

                $orders[] = $order;

                foreach ($carts->whereIn('seller_id', $cart_group['seller_id']) as $item) {

                    $shipping_cost = [
                        'type' => '',
                        'depend_on_quantity' => 0,
                        'per_cost' => 0,
                        'total_cost' => 0
                    ];
                    $coupon = [
                        'code' => '',
                        'discount' => 0,
                    ];

                    if ($item->shipping_cost > 0 && settingHelper('shipping_fee_type') != 'invoice_base') {
                        $product = $item->product;
                        $shipping_cost = [
                            'type' => $product->shipping_type,
                            'depend_on_quantity' => $product->shipping_fee_depend_on_quantity,
                            'per_cost' => $product->shipping_fee_depend_on_quantity == 1 ? $product->shipping_fee : 0,
                            'total_cost' => $item->shipping_cost
                        ];
                    }

                    if ($item->coupon_discount > 0) {
                        $coupon = [
                            'code' => $cart_group['code'],
                            'discount' => $item->coupon_discount,
                        ];
                    }

                    $orderDetails = OrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'variation' => $item->variant,
                        'price' => $item->price,
                        'tax' => $item->tax,
                        'discount' => $item->discount,
                        'shipping_cost' => $shipping_cost,
                        'coupon_discount' => $coupon,
                        'quantity' => $item->quantity,
                        'is_refundable' => $item->product->is_refundable,
                    ]);
                }
            }
        } else {
            return abort(403);
        }

        return $orders;
    }

    public function deliveryAddress($data)
    {
        $delivery_address = [
            'name' => $data['form']['name'],
            'phone_no' => $data['form']['phone_no'],
            'email' => $data['form']['email'],
            'address_ids' => [
                'division_id' => $data['form']['division_id'],
                'district_id' => $data['form']['district_id'],
                'thana_id' => $data['form']['thana_id'],
            ],
            'division' => Division::findOrFail($data['form']['division_id'])->name,
            'district' => State::findOrFail($data['form']['district_id'])->name,
            'thana' => City::findOrFail($data['form']['thana_id'])->name,
            'address' => $data['form']['address'] ?? "",
        ];

        if (authUser()) {
            $delivery_address['user_id'] = authId();

            $existingAddress = DeliveryAddress::where('user_id', authId())->first();

            if ($existingAddress) {
                $existingAddress->update($delivery_address);
            } else {
                DeliveryAddress::create($delivery_address);
            }
        }

        return true;
    }

    public function takePaymentOrder($trx_id)
    {
        return Order::where('trx_id', $trx_id)
            ->with(['orderDetails.product.productLanguages'])
            ->when(settingHelper('seller_system') != 1, function ($q) {
                $q->where('seller_id', 1);
            })
            ->where('status', 0)->get();
    }

    /**
     * Find orders by payment gateway transaction ID (e.g., SSLCommerz tran_id)
     * This is used for IPN callbacks where the gateway sends their transaction ID
     *
     * @param string $gateway_tran_id
     * @return mixed
     */
    public function takePaymentOrderByGatewayTranId($gateway_tran_id)
    {
        return Order::where('gateway_tran_id', $gateway_tran_id)
            ->with(['orderDetails.product.productLanguages'])
            ->when(settingHelper('seller_system') != 1, function ($q) {
                $q->where('seller_id', 1);
            })
            ->where('status', 0)->get();
    }


    public function completeOrder($data, $user, $offline)
    {
        $payment_details = $carts = [];

        $walk_in_user = getWalkInCustomer();
        if (array_key_exists('guest', $data) && $data['guest'] == 1) {
            $carts = Cart::where('user_id', $walk_in_user->id)->where('trx_id', $data['trx_id'])->when(session()->get('is_buy_now'), function ($query) {
                $query->where('is_buy_now', session()->get('is_buy_now'));
            })->get();
            $orders = Order::where('trx_id', $data['trx_id'])->where('user_id', $walk_in_user->id)->get();

        } else {

            if (array_key_exists('code', $data) && !empty($data['code']) && $data['code'] != 'undefined') {
                $orders = $this->orderByCodes($data['code']);
                if (!$user) {
                    Sentinel::login($orders->first()->user);
                    $user = authUser();
                }
                // FIX: Get carts for the user when using code path
                $carts = Cart::where('user_id', $user->id)->where('trx_id', $orders->first()->trx_id)->when(session()->get('is_buy_now'), function ($query) {
                    $query->where('is_buy_now', session()->get('is_buy_now'));
                })->get();

            } else {

                if ($user) {
                    $carts = Cart::where('user_id', $user->id)->where('trx_id', $data['trx_id'])->when(session()->get('is_buy_now'), function ($query) {
                        $query->where('is_buy_now', session()->get('is_buy_now'));
                    })->get();
                }

                $trx_id = array_key_exists('trx_id', $data) ? $data['trx_id'] : $carts->first()->trx_id;
                $orders = $this->takePaymentOrder($trx_id);

                if (!$user) {
                    if (count($orders) == 0) {
                        $orders = Order::where('trx_id', $trx_id)->where('status', 1)->get();
                    }
                    Sentinel::login($orders->first()->user);
                    $user = authUser();
                    $carts = Cart::where('user_id', $user->id)->where('trx_id', $trx_id)->when(session()->get('is_buy_now'), function ($query) {
                        $query->where('is_buy_now', session()->get('is_buy_now'));
                    })->get();
                }
            }
        }

        if ($user || (array_key_exists('guest', $data) && $data['guest'] == 1)) {
            $payment_details = $this->methodCheck($data, $orders, $user);

            foreach ($carts as $cart):
                $product = $this->product->get($cart->product_id);
                if ($product != null):
                    $product->current_stock -= $cart->quantity;
                    $product->save();
                endif;
                // delete from product stock
                // Fetch all stocks for the product and variant, ordered by store (e.g., smallest store_id first)
                $product_stocks = ProductStock::where('product_id', $cart->product_id)
                    ->where('name', $cart->variant)
                    ->orderBy('id')
                    ->get();


                $quantityToDeduct = $cart->quantity;

                foreach ($product_stocks as $product_stock) {
                    if ($quantityToDeduct <= 0) {
                        break; // Stop if no more quantity needs to be deducted
                    }

                    if ($product_stock->current_stock <= 0) {
                        continue; // Skip if this store has no stock
                    }

                    // Deduct the lesser of the available stock or the remaining required quantity
                    $deduction = min($quantityToDeduct, $product_stock->current_stock);
                    $product_stock->current_stock -= $deduction;
                    $product_stock->save();

                    $quantityToDeduct -= $deduction; // Reduce the remaining quantity
                }

                // If `quantityToDeduct > 0` here, it means there wasn't enough stock across all stores
                if ($quantityToDeduct > 0) {
                    throw new Exception('Not enough stock available to fulfill the order.');
                }

                $cart->delete();
            endforeach;

            foreach ($orders as $order) {

                $order->payment_type = $data['payment_type'];
                $this->deliveryEvent('order_create_event', $order->id);

                if (!in_array($data['payment_type'], ['cash_on_delivery', 'pay_later', 'pickup_from_shop'])) {
                    $order->payment_status = array_key_exists('id', $data) && $data['id'] ? 'unpaid' : 'paid';
                    $order->offline_method_id = array_key_exists('id', $data) && $data['id'] ? $data['id'] : null;
                    $order->offline_method_file = array_key_exists('image', $data) && $data['image'] ? $data['image'] : [];
                    $order->payment_details = $payment_details ?: [];

                    if ($order->payment_status == 'paid'):
                        $this->wallet->managePlacedOrder($order, $data);
                    endif;
                }

                $order->status = 1;

                // Log payment status update for debugging
                Log::info('ORDER_PAYMENT_STATUS: Updating order payment status', [
                    'order_code' => $order->code,
                    'trx_id' => $order->trx_id,
                    'gateway_tran_id' => $order->gateway_tran_id,
                    'payment_type' => $data['payment_type'],
                    'status_before' => 'pending (0)',
                    'status_after' => 'completed (1)',
                    'payment_status' => $order->payment_status,
                    'is_paid' => $order->status == 1 && $order->payment_status == 'paid',
                    'offline_method_id' => $order->offline_method_id,
                ]);

                $order->save();
                $this->paymentHistoryEvent('order_payment_' . $order->payment_status . '_event', $order->id, 'With_' . $data['payment_type']);
                $url = "orders/view/{$order->id}";
                if ($order->seller_id != 1):
                    $this->SendNotification(Sentinel::findById($order->seller_id), __('New order is created.') . 'z', 'success', $url, __('See it in Details'));
                endif;
                $this->SendNotification(Sentinel::findById(1), __('New order is created.'), 'success', $url, __('See it in Details'));
                if ($order->pickup_hub_id):
                    $this->SendNotification(Sentinel::findById($order->pickupHub->user_id), __('New order is created.') . 'y', 'success', $url, __('See it in Details'));
                endif;

                if ($order->billing_address['email']) {
                    $this->SendMail($order->billing_address['email'], 'Order Placed', $order, 'email.order-complete-email', url('/') . '/get-invoice/' . $order->code);
                }

                if (isset($order->user->phone) || isset($order->billing_address['phone_no'])) {
                    $this->smsService->orderCreate($order->billing_address['phone_no'] ?? $order->user->phone, [
                        'customer' => $order->billing_address['name'] ?? $order->user->first_name,
                        'tracking_number' => $order->code,
                        'invoice_url' => url("invoice/{$order->trx_id}"),
                    ]);
                }

                $admin = User::query()->where('user_type', 'admin')->first();

                if ($admin && $admin->email) {
                    $this->SendMail($admin->email, 'New order is created', $order, 'email.new-order-email', url('/') . '/admin/orders/view/' . $order->id);
                }

                foreach ($order->orderDetails as $orderDetail):
                    if ($orderDetail->product->stock_notification == 1):
                        if ($orderDetail->product->low_stock_to_notify >= $orderDetail->product->current_stock):
                            $this->SendNotification(Sentinel::findById($orderDetail->product->user_id), __('Product stock in low.'), 'warning', "edit-product/{$orderDetail->product->id}", __('Product Stock in low'));
                        endif;
                    endif;
                endforeach;

            }
            session()->put('trx_id', $data['trx_id']);
            session()->forget('bkash_token');
            session()->forget('is_buy_now');
        }

        return $payment_details;
    }

    public function deliveryEvent($event, $order_id, $delivery_hero_id = null, $remarks = '', $user_id = '')
    {
        $data = [
            'event' => $event,
            'user_id' => authUser() ? authId() : ($user_id != '' ? $user_id : getWalkInCustomer()->id),
            'order_id' => $order_id,
            'remarks' => $remarks,
            'delivery_hero_id' => $delivery_hero_id,
        ];
        return DeliveryHistory::create($data);
    }
    public function paymentHistoryEvent($event, $order_id, $remarks = '', $user_id = '')
    {
        $data = [
            'event' => $event,
            'user_id' => authUser() ? authId() : ($user_id != '' ? $user_id : getWalkInCustomer()->id),
            'order_id' => $order_id,
            'remarks' => $remarks,
        ];
        return PaymentHistory::create($data);
    }

    public function invoiceByTrx($trx_id)
    {
        return Order::with(['orderDetails.product', 'store'])
            ->when(settingHelper('seller_system') != 1, function ($q) {
                $q->where('seller_id', 1);
            })->where(function ($query) use ($trx_id) {
                $query->where('trx_id', $trx_id)
                    ->orWhere('code', $trx_id);
            })->get();
    }

    //frontend api completed
    public function cancelOrder($order, $remarks = '', $order_updated = false, $user_id = '')
    {
        if (!$order_updated):
            $order->delivery_status = 'canceled';
            // if ($order->payment_status == 'paid'):
            //wallet & balance transaction for users
            // $this->wallet->manageCanceledOrder($order);


            // $order->payment_status = 'refunded_to_wallet';
            // endif;

            if ($user_id != ''):
                $order->cancel_request = 1;
                $order->cancel_request_at = Carbon::now();
            endif;

            $order->save();
        endif;
        //        $this->manageProductStock($order);
        $this->adjustQuantity($order, false);

        $this->SendNotification(Sentinel::findById(1), ' A Order has been canceled', 'danger', "orders/view/" . $order->id, __('See it in Details'));
        $this->deliveryEvent('order_canceled_event', $order->id, $order->delivery_hero_id, $remarks, $user_id);
        return $order;
    }

    //for frontend api
    public function orderByCodes($orderCode)
    {
        return Order::with('orderDetails.product:id,thumbnail')
            ->when(settingHelper('seller_system') != 1, function ($q) {
                $q->where('seller_id', 1);
            })->where('code', $orderCode)->get();
    }

    public function adjustQuantity($order, $remove_quantity = false)
    {
        foreach ($order->orderDetails as $key => $orderDetail):
            return $this->updateQuantity($orderDetail, $remove_quantity);
        endforeach;

        return true;
    }

    public function updateQuantity($orderDetail, $remove_quantity = false)
    {
        $product = $this->product->get($orderDetail->product_id);
        if ($product != null):
            if ($remove_quantity):
                if ($orderDetail->quantity <= $product->current_stock):
                    $product->current_stock -= $orderDetail->quantity;
                else:
                    return false;
                endif;
            else:
                $product->current_stock += $orderDetail->quantity;
            endif;

            $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                ->where('name', $orderDetail->variation)
                ->first();
            if ($product_stock != null):
                if ($remove_quantity):
                    if ($orderDetail->quantity <= $product_stock->current_stock):
                        $product_stock->current_stock -= $orderDetail->quantity;
                    else:
                        return false;
                    endif;
                else:
                    $product_stock->current_stock += $orderDetail->quantity;
                endif;
                $product_stock->save();
            endif;
            $product->save();
        endif;

        return true;
    }

    public function saleUpdate($orderDetail, $remove_sale = false)
    {
        $product = $this->product->get($orderDetail->product_id);
        if ($product != null):
            if ($remove_sale):
                if ($orderDetail->quantity <= $product->current_stock):
                    $product->total_sale -= $orderDetail->quantity;
                else:
                    return false;
                endif;
            else:
                $product->total_sale += $orderDetail->quantity;
            endif;
            $product->save();
        endif;

        return true;
    }

    public function checkCodByTrx($trx_id)
    {
        return Order::whereHas('orderDetails.product', function ($query) {
            $query->where('cash_on_delivery', 0);
        })->when(settingHelper('seller_system') != 1, function ($q) {
            $q->where('seller_id', 1);
        })->where('trx_id', $trx_id)->exists();
    }

    public function checkCodByCode($code)
    {
        return Order::whereHas('orderDetails.product', function ($query) {
            $query->where('cash_on_delivery', 0);
        })->when(settingHelper('seller_system') != 1, function ($q) {
            $q->where('seller_id', 1);
        })->where('code', $code)->exists();
    }

    public function allOrder($take, $user)
    {
        return Order::where('user_id', $user->id)->when(settingHelper('seller_system') != 1, function ($q) {
            $q->where('seller_id', 1);
        })->where('is_deleted', 0)->where(function ($query) {
            $query->where('status', 1)->orWhere('payment_type', 0);
        })->latest()->paginate($take);
    }

    public function apiSellerOrder($user, $data): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return OrderDetail::with('order.user', 'product')->whereHas('product')->whereHas('order', function ($q) use ($user, $data) {
            $q->where('seller_id', $user->id)->where('status', 1)
                ->when($data['status'] == 'pending', function ($q) {
                    $q->where('delivery_status', 'pending');
                })->when($data['status'] == 'delivered', function ($q) {
                    $q->where('delivery_status', 'delivered');
                })->when($data['status'] == 'cancelled', function ($q) {
                    $q->where('delivery_status', 'canceled');
                })->when(arrayCheck('start_time', $data) && arrayCheck('end_time', $data), function ($query) use ($data) {
                    $query->whereBetween('date', [$data['start_time'], $data['end_time']]);
                });
        })->latest()->paginate($data['paginate']);
    }

    public function userCommission($request)
    {
        $date_diff = Carbon::parse($request['end_time'])->diffInDays(Carbon::parse($request['start_time']));
        $date_diff = $date_diff + 1;

        return CommissionHistory::when('seller_id', function ($query) use ($request) {
            $query->where('seller_id', $request['seller_id']);
        })->whereHas('order', function ($query) {
            $query->where('payment_status', 'paid')->whereNotIn('delivery_status', ['canceled', 'pending']);
        })->when(arrayCheck('start_time', $request) && arrayCheck('end_time', $request), function ($query) use ($request) {
            $query->whereBetween('created_at', [$request['start_time'], $request['end_time']]);
        })->when($date_diff == 1, function ($query) use ($request) {
            $query->selectRaw('SUM(seller_earning) as amount, hour(created_at) as label')
                ->groupBy(DB::raw('hour(created_at)'));
        })->when($date_diff > 1 && $date_diff <= 31, function ($query) use ($request) {
            $query->selectRaw('SUM(seller_earning) as amount, DATE(created_at) as label')
                ->groupBy(DB::raw('DATE(created_at)'));
        })->when($date_diff > 31 && $date_diff <= 366, function ($query) use ($request) {
            $query->selectRaw('SUM(seller_earning) as amount, MONTHNAME(created_at) as label')
                ->groupBy(DB::raw('MONTHNAME(created_at)'));
        })->when($date_diff > 366, function ($query) use ($request) {
            $query->selectRaw('SUM(seller_earning) as amount, YEAR(created_at) as label')
                ->groupBy(DB::raw('YEAR(created_at)'));
        })->orderBy('label', 'asc')->get();
    }
}
