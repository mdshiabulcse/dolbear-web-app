<?php

namespace App\Repositories\Interfaces\Admin;

interface StoreInterface
{
    public function paginate($request, $limit);

    public function store($request);
    
    public function allStore();
    
    public function find($id);
    
    public function update($request);

    
}