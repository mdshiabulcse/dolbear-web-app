<?php

namespace App\Repositories\Admin;

use Carbon\Carbon;
use App\Models\Store;
use App\Traits\ImageTrait;
use Illuminate\Http\Request;
use App\Traits\SendNotification;
use App\Traits\RandomStringTrait;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\Admin\StoreInterface;
use App\Repositories\Interfaces\Admin\LanguageInterface;

class StoreRepository implements StoreInterface
{
    use RandomStringTrait, SendNotification, ImageTrait;

    protected $lang;

    public function __construct(LanguageInterface $lang)
    {
        $this->lang         = $lang;
    }

    public function all()
    {
        return Store::get();
    }

    public function get($id)
    {
        return Store::find($id);
    }

    public function paginate($request, $limit)
    {
        $stores = Store::paginate($limit);

        return $stores;

    }

    public function store($data)
    {

        if ($data->image != '') {
           $image = $this->getImageWithRecommendedSize($data->image,400,492);
           $image_id = $data->image_id;
        } else {
            $image = [];
            $image_id = null;
        }

        Store::create([
            'name' => $data->name,
            'phone' => $data->phone,
            'address' => $data->address,
            'map' => $data->map,
            'description' => $data->description,
            'image' => $image,
            'image_id' => $image_id
        ]);

        return true;
    }

    public function allStore()
    {
        return Store::all();
    }

    public function find($id)
    {
        return Store::find($id);
    }

    public function update($data){

        if ($data->image != '') {
            $image = $this->getImageWithRecommendedSize($data->image,400,492);
            $image_id = $data->image_id;
        } else {
            $image = [];
            $image_id = null;
        }

        $store = Store::find($data->id);
        $store->name = $data->name;
        $store->phone = $data->phone;
        $store->address = $data->address;
        $store->map = $data->map;
        $store->description = $data->description;
        $store->image = $image;
        $store->image_id = $image_id;
        $store->save();
        
        return true;
        
    }
}