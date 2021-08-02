<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subdapertement extends Model
{
    protected $table = 'subdapertements';
    protected $fillable = [
        'dapertement_id',
        'code',
        'name',
        'description'
    ];
}
