<?php

namespace App\Repositories\Admin;

use Carbon\Carbon;
use App\Models\PointSetting;
use Illuminate\Http\Request;
use App\Traits\SendNotification;
use App\Traits\RandomStringTrait;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\Admin\LanguageInterface;
use App\Repositories\Interfaces\Admin\PointSettingInterface;

class PointSettingRepository implements PointSettingInterface
{
    use RandomStringTrait, SendNotification;

    protected $lang;

    public function __construct(LanguageInterface $lang)
    {
        $this->lang         = $lang;
    }

    public function all()
    {
        return PointSetting::latest();
    }

    public function get($id)
    {
        return PointSetting::find($id);
    }

    public function paginate($request, $limit)
    {
        $pointInfo = PointSetting::where('status', 1)
                             ->orderByDesc('id');

        return $pointInfo->paginate($limit);

    }

    public function store($data)
    {
        PointSetting::where('status', 1)->update(['status' => 0]);

        PointSetting::create([
            'point' => $data->point,
            'point_to_money' => $data->point_to_money,
            'status' => 1,
        ]);

        return true;
    }
}