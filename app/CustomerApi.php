<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;


class CustomerApi extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'customers';

    protected $fillable = [
        'code',
        'name',
        'email',
        'password',
        'phone',
        'gender',
        'type',
        'address'
    ];

    protected $hidden = [
        'password', 
    ];
}
