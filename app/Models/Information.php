<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    use HasFactory;
    
    protected $table = 'information';

    protected $fillable = [
        'company_name',
        'cnpj_cpf',
        'address',
        'address_number',
        'city',
        'state',
        'logo'
    ];
}
