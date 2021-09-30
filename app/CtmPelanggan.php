<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CtmPelanggan extends Model
{   
    // use SoftDeletes;
    protected $connection = 'mysql2';

    protected $table = 'tblpelanggan';
    
}
