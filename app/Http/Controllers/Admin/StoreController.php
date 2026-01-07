<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Repositories\Interfaces\Admin\StoreInterface;

class StoreController extends Controller
{
    protected $store;

    public function __construct(StoreInterface $store)
    {
        $this->store = $store;
        $this->store = $store;
    }

    public function index(Request $request)
    {
        try{
            $stores = $this->store->paginate($request, get_pagination('pagination'));

            return view('admin.stores.index',compact('stores'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function create(Request $request)
    {
        try {
            $data = [
                'r' => $request->r != '' ? $request->r : $request->server('HTTP_REFERER')
            ];
            
            return view('admin.stores.store_create_form', $data);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function store(Request $request)
    {
        try {
             $this->store->store($request);
            Toastr::success(__('Created Successfully'));
            return redirect()->route('store.index');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function allStore(){
        $stores = $this->store->allStore();
        return response()->json($stores);
    }

    public function edit($id){
        try {
            $store = $this->store->find($id);
            return view('admin.stores.store_edit_form',compact('store'));
            } catch (\Exception $e) {
                Toastr::error($e->getMessage());
                return back();
                }
    }

    public function update(Request $request){

        try {
            $this->store->update($request);
            Toastr::success(__('Updated Successfully'));
            return redirect()->route('store.index');
            } catch (\Exception $e) {
                Toastr::error($e->getMessage());
                return redirect()->back();
                }

    }

   
}
