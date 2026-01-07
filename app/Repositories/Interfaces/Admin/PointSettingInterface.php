<?php

namespace App\Repositories\Interfaces\Admin;

interface PointSettingInterface
{
    public function paginate($request, $limit);

    public function store($request);
    
}