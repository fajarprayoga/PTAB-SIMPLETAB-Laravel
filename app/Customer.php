<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
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

    public function ticket() { 
        return $this->belongsTo('App\Ticket'); 
    }
}
