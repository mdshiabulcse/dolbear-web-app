<?php

namespace App\Repositories\Interfaces\Admin\Recommended;

interface RecommendedInterface
{

    public function all();

    public function paginate($request, $limit);

    public function store($request);

    public function allRecommended();

    public function delete($id);

}