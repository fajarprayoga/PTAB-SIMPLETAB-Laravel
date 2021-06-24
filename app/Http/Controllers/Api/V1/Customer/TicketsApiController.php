<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiTicketRequest;
use Illuminate\Http\Request;


class TicketsApiController extends Controller
{
    public function store(StoreApiTicketRequest $request)
    {
        // return response()->json([
        //     'data' => $request->all()
        // ]);

        if($request->image !=null){
            return 'gambar iis';
        }

        return 'gambar kosong';
    }
}
