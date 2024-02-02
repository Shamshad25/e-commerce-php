<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'user_id',
        'last_name',
        'email',
        'mobile',
        'country_id',
        'address',
        'appartment',
        'state',
        'city',
        'zip',
    ];
}
