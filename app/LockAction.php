<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LockAction extends Model
{
    protected $table = 'lock_action';
    protected $fillable = [
        'lock_id',
        'code',
        'type',
        'image',
        'memo',
        'lat',
        'lng',
    ];
    public function customer() { 
        return $this->belongsTo(Customer::class, 'customer_id', 'nomorrekening'); 
    }
    public function subdapertement() { 
        return $this->belongsTo(Subdapertement::class, 'subdapertement_id', 'id'); 
    }
    public function lock() { 
        return $this->belongsTo(Lock::class, 'lock_id', 'id'); 
    }
}
