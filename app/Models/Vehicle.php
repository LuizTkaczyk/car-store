<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicle';

    protected $fillable = [
        'model',
        'brand_id',
        'category_id',
        'year',
        'price'
    ];
}
