<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'point',
        'point_to_money',
        'status'
    ];
}
