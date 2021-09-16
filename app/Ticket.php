<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';
    protected $dates = ['delegated_at'];
    protected $fillable = [
        'code',
        'title',
        'image',
        'video',
        'lat',
        'lng',
        'status',
        'category_id',
        'dapertement_id',
        'customer_id',
        'description',
        'area',
        'spk',
        'dapertement_receive_id',
        'delegated_at',
    ];

    public function dapertementReceive() { 
        return $this->belongsTo(Dapertement::class, 'dapertement_receive_id', 'id'); 
    }
    
    public function dapertement() { 
        return $this->belongsTo(Dapertement::class, 'dapertement_id', 'id'); 
    }

    public function department() { 
        return $this->belongsTo(Dapertement::class, 'dapertement_id', 'id'); 
    }
    
    public function customer() { 
        return $this->belongsTo(Customer::class, 'customer_id', 'nomorrekening'); 
    }

    public function category() { 
        return $this->belongsTo('App\Category')->with('categorygroup')->with('categorytype')->select('*'); 
    }

    public function action() { 
        return $this->hasMany(Action::class, 'ticket_id', 'id')->with('staff')->with('subdapertement')->select('*');
    }
    public function ticket_image()
    {
        return $this->hasMany('App\Ticket_Image', 'ticket_id', 'id');
    }

    public function scopeFilterStatus($query, $status)
    {
        if($status !=''){
        $query->where('status', $status);        
        }
        return $query;
    }

    
    public function scopeFilterDepartment($query, $department)
    {
        if($department !=''){
        $query->where('dapertement_id', $department);        
        }
        return $query;
    }

    public function scopeFilterJoinStatus($query, $status)
    {
        if($status !=''){
        $query->where('tickets.status', $status);        
        }
        return $query;
    }

    public function scopeFilterJoinDepartment($query, $department)
    {
        if($department !=''){
        $query->where('actions.dapertement_id', $department);        
        }
        return $query;
    }
}
