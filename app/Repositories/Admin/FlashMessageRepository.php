<?php

namespace App\Repositories\Admin;
use App\Models\FlashMessage;


use App\Traits\ImageTrait;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\Admin\FlashMessageInterface;

class FlashMessageRepository implements FlashMessageInterface
{

    use ImageTrait;


    public function store($request)
    {
        try {

            $flashMessage = new FlashMessage();
            $flashMessage->name = $request->name;
            $flashMessage->status = $request->status;
            $flashMessage->description = $request->description;

            $flashMessage->save();
        
            return true;
        } catch (\Exception $e) {
            
            Log::error($e->getMessage());
        
        }
    }

    public function all()
    {
        return FlashMessage::where('status', 1)->get();
    }

    public function paginate($request, $limit)
    {
        return FlashMessage::paginate($limit);
    }

    public function update($request)
    {
        $data = FlashMessage::find($request->id);
    
        $data->name = $request->name;
        $data->status = $request->status;
        $data->description = $request->description;
    
        $data->save();
    
        return true;
    }

    public function find($id)
    {
        return FlashMessage::find($id);
    }

    public function delete($id)
    {
        $data = FlashMessage::find($id);
    
        // Check if the record exists
        if (!$data) {
            return response()->json(['message' => 'Record not found'], 404);
        }
    
        // Delete the record
        $data->delete();
    
        return true;
    }

    public function allMessage()
    {
        return FlashMessage::get();
    }

}