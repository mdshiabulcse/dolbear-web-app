<?php

namespace App\Http\Controllers\Admin\Marketing;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Repositories\Interfaces\Admin\Marketing\SubscriberInterface;

class SubscriberController extends Controller
{
    protected $subscriber;

    public function __construct(SubscriberInterface $subscriber)
    {
        $this->subscriber = $subscriber;
    }
    public function index(Request $request){

        $subscriber = $this->subscriber->paginate($request ,get_pagination('pagination'));
        return view('admin.marketing.subscriber', compact('subscriber'));
    }

    public function store(Request $request)
    {
        //validate 
        $request->validate([
            'email' => 'required|email|unique:subscribers',
        ]);

        info($request);

        DB::beginTransaction();
        try {
            $this->subscriber->store($request);
            Toastr::success(__('Created Successfully'));
            DB::commit();
           return response()->json(['success' => __('Created Successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
