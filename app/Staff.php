<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staffs';
    protected $fillable = [
        'code',
        'name',
        'phone',
        'dapertement_id',
        'subdapertement_id'
    ];

    public function dapertement() { 
        return $this->belongsTo(Dapertement::class, 'dapertement_id', 'id'); 
    }

    public function subdapertement() { 
        return $this->belongsTo(Subdapertement::class, 'subdapertement_id', 'id'); 
    }

    public function action()
    {
        return $this->belongsToMany(Action::class, 'action_staff', 'action_id', 'staff_id')
            ->withPivot([
                'status'
            ]);
    }
    public function area()
    {
        return $this->belongsToMany(CtmWilayah::class, 'ptabroot_simpletab.area_staff','staff_id', 'area_id');
    }
    public function scopeFilterDapertement($query, $dapertement)
    {
        if($dapertement !=''){
        $query->where('dapertement_id', $dapertement);        
        }
        return $query;
    }
}
