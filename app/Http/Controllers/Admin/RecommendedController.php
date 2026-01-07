<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\Admin\Recommended\RecommendedRequest;
use App\Repositories\Interfaces\Admin\Recommended\RecommendedInterface;

class RecommendedController extends Controller
{
    protected $recommended;

    public function __construct(RecommendedInterface $recommended)
    {
        $this->recommended = $recommended;
    }

    public function all(){
        try {
            $recommendation = $this->recommended->all();

            return response()->json(['message' => 'Data fetched successfully', 'data' => $recommendation]);
        } catch (\Exception $e){
            Toastr::error($e->getMessage());
            return back();
        }
    }


    public function index(Request $request){
        try {
            $recommendation = $this->recommended->paginate($request, get_pagination('pagination'));

            return view('admin.recommended.index', compact('recommendation'));
        } catch (\Exception $e){
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function create(Request $request){
        
        return view('admin.recommended.add-recommended');
    }

    public function store(RecommendedRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->recommended->store($request);
            Toastr::success(__('Created Successfully'));
            DB::commit();
            return redirect()->route('recommended.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function getPopUpAds(){
        try {
            $recommended = $this->recommended->allRecommended();
            return response()->json($recommended);
        } catch (\Exception $e) {
            return response()->json([
                'error' =>  $e->getMessage()
            ]);
        }
    }


    public function delete($id)
    {
        try {
            $this->recommended->delete($id);

            return response()->json(['title' => 'Success', 'message' => 'Item Deleted Successfully', 'url' => url('/admin/recommended')]);
        } catch (\Exception $e){
            Toastr::error($e->getMessage());
            return back();
        }
    }
}
