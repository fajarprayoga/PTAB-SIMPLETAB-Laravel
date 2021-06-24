<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CustomerApi;
use App\Http\Requests\StoreApiCustomerRegisterPublicRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\TraitModel;

class CustomersApiController extends Controller
{
    use TraitModel;

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
                $token = Auth::user()->createToken('authToken')->accessToken;
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

    public function register_public(StoreApiCustomerRegisterPublicRequest $request)
    {

        $last_code = $this->get_last_code('customer');

        $code = acc_code_generate($last_code, 8, 3);
        
        $data = $request->all();

        $data['code'] = $code;
        
        $data['type'] = 'public';

        $customer = CustomerAPI::create($data);

        $token= $customer->createToken('appToken')->accessToken;

        return response()->json([
            'message' => 'Registrasi Berhasil',
            'token' => $token,
            'data' => $customer
        ]);
    }
}
