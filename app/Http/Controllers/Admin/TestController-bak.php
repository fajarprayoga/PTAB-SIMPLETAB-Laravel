<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\TestModel;

class TestController extends Controller
{
    public function customers(Request $request)
    {
        $req_obj = array("code"=>$request->code);
        $qry = TestModel::FilterInput($req_obj)
        ->get();
        return $qry;
        //dd($qry);
    }

    
}
