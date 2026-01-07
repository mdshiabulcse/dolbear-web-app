<?php

namespace App\Repositories\Interfaces\Admin;

interface FlashMessageInterface
{

    public function all();

    public function paginate($request, $limit);

    public function store($request);


    public function find($id);
    public function update($request);

    public function delete($id);

}