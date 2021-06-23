<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\User;
use App\Student;
use App\Http\Controllers\Controller;
use Auth;
use Hashids;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;

class CustomersApiController extends Controller
{

    public function login()
    {

        $user = User::where('email', request('email'))
            ->first();
        if ((Hash::check(request('password'), $user->password)) && ($user->status_block == 0)) {
            Auth::login($user);
            $success['token'] = Auth::user()->createToken('authToken')->accessToken;
            return response()->json([
                'success' => true,
                'token' => $success,
                'user' => $user,
            ]);
        } else {
            //if authentication is unsuccessfull, notice how I return json parameters
            $message = 'Email & Password yang Anda masukkan salah. Salah memasukkan Email & Password lebih dari 3x maka Account akan otomatis di blokir.';
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 401);
        }
    }

    public function students()
    {
        try {
            $members = Student::select('*')
                ->get();
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kosong.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

}
