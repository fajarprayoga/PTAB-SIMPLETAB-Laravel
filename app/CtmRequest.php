<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CtmRequest extends Model
{   
    // use SoftDeletes;

    protected $table = 'ctm_requests';
    protected $fillable = [
        'norek',
        'wmmeteran',
        'namastatus',
        'opp',
        'lat',
        'lng',
        'accuracy',
        'operator',
        'nomorpengirim',
        'statusonoff',
        'description',
        'img',
        'img1',
        'status',
        'datecatatf1',
        'datecatatf2',
        'datecatatf3',
        'year',
        'month',
    ];

    public function customer() { 
        return $this->belongsTo(Customer::class, 'norek', 'nomorrekening'); 
    }
}
