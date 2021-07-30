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
        $nomorrekening='1';
        $month='07';
        $year='2021';
        // $data=$this->getCtmPrev($nomorrekening, $month, $year);
        // $data=$this->getCtmAvg($nomorrekening, $month, $year);
        $data=$this->getCtmMeterPrev($nomorrekening, $month, $year);
        return response()->json([
            'message' => 'Berhasil',
            'data' => $data
        ]);
    }
    
}
