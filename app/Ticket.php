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

    public function dapertement() { 
        return $this->belongsTo(Dapertement::class, 'dapertement_id', 'id'); 
    }

    public function customer() { 
        return $this->belongsTo(Customer::class, 'customer_id', 'nomorrekening'); 
    }

    public function category() { 
        return $this->belongsTo('App\Category')->select('id', 'name'); 
    }

    public function action() { 
        return $this->belongsTo('App\Action')->select('*'); 
    }

    public function ticket_image()
    {
        return $this->hasMany('App\Ticket_Image');
    }
}
