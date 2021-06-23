<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CustomerApi;
use Illuminate\Database\QueryException;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Hashids;
use Auth;

class CustomersApiController extends Controller
{
    public function login(Request $request)
    {
        try {
            $customer = CustomerApi::where('email', request('email'))->first();

            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if(Hash::check($request->password, $customer->password)){
                Auth::login($customer);
                $success['token'] = Auth::user()->createToken('authToken')->accessToken;
                return response()->json([
                    'success' => 'success login',
                    'token' => $success,
                    'data' => $customer
                ]);
            }else{
                return response()->json([
                    'success' => 'Email Atau Password Yang Di masukkan Salah',
                ]);
            }

        } catch (QueryException $e) {
            return response()->json([
                'message' => $e->message
            ]);
        }
    }
}
