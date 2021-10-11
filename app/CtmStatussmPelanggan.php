<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CtmStatussmPelanggan extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'tblstatussmpelanggan';

    public function scopeFilterMonth($query, $month)
    {
        if($month !=''){
            $query->where('tblstatussmpelanggan.bulan', $month);
        }        
        return $query;
    }

    public function scopeFilterYear($query, $year)
    {
        if($year !=''){
            $query->where('tblstatussmpelanggan.tahun', $year);
        }        
        return $query;
    }
}
