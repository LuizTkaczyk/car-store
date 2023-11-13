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
        'price',
        'description'
    ];

    public function images()
    {
        return $this->hasMany(Images::class, 'vehicle_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function optional()
    {
        return $this->belongsToMany(Optional::class, 'vehicle_has_optional', 'vehicle_id', 'optional_id');
    }
}
