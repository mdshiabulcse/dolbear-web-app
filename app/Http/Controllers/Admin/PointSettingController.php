<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Repositories\Interfaces\Admin\PointSettingInterface;

class PointSettingController extends Controller
{
    protected $pointSetting;

    public function __construct(PointSettingInterface $pointSetting)
    {
        $this->pointSetting = $pointSetting;
    }

    public function index(Request $request)
    {
        try{
            $points             = $this->pointSetting->paginate($request, get_pagination('pagination'));
            return view('admin.points.points',compact('points'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function store(Request $request)
    {
        if (config('app.demo_mode')):
            Toastr::info(__('This function is disabled in demo server.'));
            return redirect()->back();
        endif;

        DB::beginTransaction();
        try {
            $this->pointSetting->store($request);
            Toastr::success(__('Created Successfully'));
            DB::commit();
            return redirect()->route('point.setting');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
   
}
