<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';
    protected $fillable = [
        'code',
        'title',
        'image',
        'video',
        'lat',
        'lng',
        'status',
        'category_id',
        'customer_id',
        'description'
    ];

    public function customer() { 
        return $this->belongsTo('App\Customer')->select('id', 'name'); 
    }

    public function category() { 
        return $this->belongsTo('App\Category')->select('id', 'name'); 
    }

    public function action() { 
        return $this->belongsTo('App\Action')->select('id'); 
    }
}
