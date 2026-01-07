<?php

namespace App\Repositories\Admin\Recommended;
use App\Models\Recommendation;
use App\Models\User;


use App\Traits\ImageTrait;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\Admin\Recommended\RecommendedInterface;

class RecommendedRepository implements RecommendedInterface
{

    use ImageTrait;


    public function store($request)
    {
        try {

            $recommended = new Recommendation();
            $recommended->name = $request->name;
            $recommended->description = $request->description; 
        
            if ($request->banner != '') {
                $recommended->image = $this->getImageWithRecommendedSize($request->banner,400,492);
                $recommended->image_id = $request->banner;
            } else {
                $recommended->image = [];
                $recommended->image_id = null;
            }
        

            $recommended->save();
        
            return true;
        } catch (\Exception $e) {
            
            Log::error($e->getMessage());
        
        }
    }

    public function all()
    {
        $recommendation = Recommendation::get();
        return $recommendation;
    }

    public function paginate($request, $limit)
    {
        $stores = Recommendation::paginate($limit);

        return $stores;
    }

    public function allRecommended()
    {
        try {
            $data = Recommendation::select(['id', 'name', 'image_id', 'image', 'description', 'status'])->where('status', 1)->get();
            return $data;
        } catch (\Exception $e) {
            info($e->getMessage());
        }
    }

    public function delete($id)
    {
        $data = Recommendation::find($id);
    
        // Check if the record exists
        if (!$data) {
            return response()->json(['message' => 'Record not found'], 404);
        }
    
        // Delete the record
        $data->delete();
    
        return true;
    }

}