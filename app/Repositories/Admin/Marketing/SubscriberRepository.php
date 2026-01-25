<?php

namespace App\Repositories\Admin\Marketing;

use App\Models\Subscriber;
use App\Repositories\Interfaces\Admin\Marketing\SubscriberInterface;
use App\Traits\ImageTrait;
use DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SubscriberRepository implements SubscriberInterface
{
    use ImageTrait;


    public function get($id)
    {
        return Subscriber::find($id);
    }
    public function all()
    {
        return Subscriber::latest();
    }

    public function paginate($request, $limit)
    {
        return $this->all()->paginate($limit);
    }

    public function store($request)
    {

        DB::beginTransaction();
        try {
            // Check if $request is a string (email) or an object
            $email = is_string($request) ? $request : $request->email;

            $subscriber = new Subscriber();
            $subscriber->email = $email;
            $subscriber->save();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

}
