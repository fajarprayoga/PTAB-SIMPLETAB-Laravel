<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lock extends Model
{
    protected $table = 'locks';
    protected $fillable = [
        'code',
        'customer_id',
        'status',
        'description',
        'subdapertement_id',
        'start',
        'end'
    ];

    public function customer() { 
        return $this->belongsTo(Customer::class, 'customer_id', 'nomorrekening'); 
    }
    public function subdapertement() { 
        return $this->belongsTo(Subdapertement::class, 'subdapertement_id', 'id'); 
    }

    public function scopeFilterStatus($query, $status)
    {
        if($status !=''){
        $query->where('status', $status);        
        }
        return $query;
    }

    
    public function scopeFilterSubDepartment($query, $subdepartment)
    {
        if($subdepartment !=''){
        $query->where('subdapertement_id', $subdepartment);        
        }
        return $query;
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'lock_staff', 'lock_id', 'staff_id');
    }

    public function dapertement() { 
        return $this->belongsTo(Dapertement::class, 'dapertement_id', 'id'); 
    }

   


}
