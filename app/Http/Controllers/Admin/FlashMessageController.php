<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\Admin\FlashMessageRequest;
use App\Repositories\Interfaces\Admin\FlashMessageInterface;

class FlashMessageController extends Controller
{
    protected $flashMessage;

    public function __construct(FlashMessageInterface $flashMessage)
    {
        $this->flashMessage = $flashMessage;
    }

    public function all(){
        try {
            $flashMessage = $this->flashMessage->all();

            return response()->json(['message' => 'Data fetched successfully', 'data' => $flashMessage]);
        } catch (\Exception $e){
            Toastr::error($e->getMessage());
            return back();
        }
    }


    public function index(Request $request){
        try {
            $flashMessage = $this->flashMessage->paginate($request, get_pagination('pagination'));

            return view('admin.flash-message.index', compact('flashMessage'));
        } catch (\Exception $e){
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function create(Request $request){
        
        return view('admin.flash-message.add-flash-message');
    }

    public function store(FlashMessageRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->flashMessage->store($request);
            Toastr::success(__('Created Successfully'));
            DB::commit();
            return redirect()->route('flash-message.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

   

    public function edit($id){
        try {
            $message = $this->flashMessage->find($id);
            return view('admin.flash-message.edit-flash-message',compact('message'));
            } catch (\Exception $e) {
                Toastr::error($e->getMessage());
                return back();
                }
    }

    public function update(Request $request){

        try {
            $this->flashMessage->update($request);
            Toastr::success(__('Updated Successfully'));
            return redirect()->route('flash-message.index');
            } catch (\Exception $e) {
                Toastr::error($e->getMessage());
                return redirect()->back();
                }

    }


    public function delete($id)
    {
        try {
            $this->flashMessage->delete($id);

            return response()->json(['title' => 'Success', 'message' => 'Item Deleted Successfully', 'url' => url('/admin/from-fan-message')]);
        } catch (\Exception $e){
            Toastr::error($e->getMessage());
            return back();
        }
    }
}
