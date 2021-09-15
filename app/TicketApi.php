<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketApi extends Model
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
        'dapertement_id',
        'customer_id',
        'description',
        'area',
        'spk',
        'dapertement_receive_id',
        'delegated_at',
    ];

    public function department() { 
        return $this->belongsTo(DapertementApi::class, 'dapertement_id', 'id'); 
    }
    
    public function customer() { 
        return $this->belongsTo(CustomerApi::class, 'customer_id', 'nomorrekening'); 
    }

    public function category() { 
        return $this->belongsTo('App\Category')->select('id', 'name'); 
    }

    public function action() { 
        return $this->hasMany('App\Action', 'ticket_id', 'id');
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
