<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gambarmetersms extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'gambarmetersms';

    protected $primaryKey = 'idurutan';

    public $timestamps = false;

    protected $fillable = [
        'nomorpengirim',
        'bulanrekening',
        'tahunrekening',
        'tanggal',
        'nomorrekening',
        'pencatatanmeter',
        'idgambar',
        '_synced'
    ];
}
