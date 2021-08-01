<?php

namespace App\Http\Controllers\api\v1\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CustomerApi;
use Illuminate\Database\QueryException;
use App\Traits\TraitModel;

class CtmApiController extends Controller
{
    use TraitModel;

    public function ctmPrev(Request $request)
    {
        $var['bulanrekening']='9';
        $var['tahunrekening']='2021';
        $var['nomorrekening']='38563';
        $var['lat']='-8.5570138';
        $var['lng']='115.10578';
        $var['datecatatf3']='2021-08-19 10:54:19';
        $var['accuracy']='2001';
        $var['_synced']='0';

        $data = $this->insupdCtmMapKunjungan($var);
        
        // $var['nomorpengirim']='+6282235454214';
        // $var['bulanrekening']='9';
        // $var['tahunrekening']='2021';
        // $var['datecatatf1']='2021-08-31';
        // $var['nomorrekening']='38563';
        // $var['pencatatanmeter']='2209';
        // $var['idgambar']='4107901';
        // $var['_synced']='0';

        // $data = $this->insupdCtmGambarmetersms($var);

        // $var['nomorpengirim']='+6282235454214';
        // $var['bulanrekening']='9';
        // $var['tahunrekening']='2021';
        // $var['datecatatf1']='2021-09-19';
        // $var['filegambar']='/gambar/202108/38563_2021_08.jpg';
        // $var['operator']='EKA';
        // $var['datecatatf2']='July 19, 2021, 10:54:19 am';
        // $var['filegambar1']='D:/MyAMP/www/gambar/202109/38563_2021_09.jpg';        
        // $var['_synced']='0';

        // $data = $this->insupdCtmGambarmeter($var);
        
        $nomorrekening='1';
        $month='07';
        $year='2021';
        // $data=$this->getCtmPrev($nomorrekening, $month, $year);
        // $data=$this->getCtmAvg($nomorrekening, $month, $year);
        // $data=$this->getCtmMeterPrev($nomorrekening, $month, $year);
        return response()->json([
            'message' => 'Berhasil',
            'data' => $data
        ]);
    }
    
}
