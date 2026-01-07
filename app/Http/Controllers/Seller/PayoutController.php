<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\PayoutRequest;
use App\Repositories\Interfaces\Admin\AccountInterface;
use App\Repositories\Interfaces\Admin\SellerPayoutInterface;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Sentinel;

class PayoutController extends Controller
{
    protected $payouts;
    protected $account;
    public function __construct(SellerPayoutInterface $payout, AccountInterface $account)
    {
        $this->payouts = $payout;
        $this->account = $account;
    }

    public function payouts(){
        $id = Sentinel::getUser();
        $payouts = $this->payouts->paginate(get_pagination('pagination') ,'','seller');
        $payment_types = $this->account->allPaymentType($id);
        return view('seller.payouts.payouts', compact('payouts','payment_types'));
    }
    public function sendPayoutRequest(PayoutRequest $request)
    {
        try {
            if (authUser()->balance < priceFormatUpdate($request->amount))
            {
                Toastr::error(__('Insufficient Balance'));
                return back()->withInput();
            }
            DB::beginTransaction();
            
            $payout = $this->payouts->sendPayoutRequest($request);
            DB::commit();
            Toastr::success(__('Request Send Successfully'));
            return back();
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error($e->getMessage());
            return back()->withInput();
        }
    }
    public function sendPayoutReject($id){
        if ($this->payouts->sendPayoutReject($id)):
            return redirect()->back();
        else:
            return back()->withInput();
        endif;
    }
}
