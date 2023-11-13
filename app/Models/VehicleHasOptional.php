<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleHasOptional extends Model
{
    use HasFactory;

    protected $table = 'vehicle_has_optional';

    protected $fillable = [
        'vehicle_id',
        'optional_id',
    ];
}
