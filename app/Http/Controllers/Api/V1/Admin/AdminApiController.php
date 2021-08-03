<?php

namespace App\Http\Controllers\Api\v1\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\TraitModel;
class AdminApiController extends Controller
{
    public function login(Request $request)
    {
        try {
            $admin = User::where('email', request('email'))->with('roles')->first();

            $credentials = $request->validate([
                'email' => ['required'],
                'password' => ['required'],
            ]);

            if(Hash::check($request->password, $admin->password)){
                //  $this->smsApi($admin->phone, $request->OTP);
                Auth::login($admin);
                $token = Auth::user()->createToken('authToken')->accessToken;

                // $data = [
                //     'success' =>  true,
                //     'message' => 'success login',
                //     'token' => $token,
                //     'data' => $admin,
                // ];
                return response()->json([
                    'success' =>  true,
                    'message' => 'success login',
                    'token' => $token,
                    'data' => $admin,
                ]);
            }else{
                return response()->json([
                    'success' =>  false,
                    'message' => 'Email Atau Password Yang Di masukkan Salah',
                ]);
                // $data =[
                //     'message' => 'Email Atau Password Yang Di masukkan Salah',
                // ];
            }

        } catch (QueryException $e) {
            return response()->json([
                'message' => $e->message
            ]);
            // $data = [
            //     'message' => $e->message
            // ];
        }
    }
}
