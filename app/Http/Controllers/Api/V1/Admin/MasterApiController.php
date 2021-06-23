<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\User;
use App\Student;
use App\Teacher;
use App\Periode;
use App\Semester;
use App\Grade;
use App\Http\Controllers\Controller;
use Auth;
use Hashids;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Team;
use App\Subject;

class MasterApiController extends Controller
{

    public function subjects()
    {
        try {
            $datas = Subject::select('*')
                ->get();
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kosong.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $datas,
        ]);
    }

    public function subjectshow($id)
    {
        $datas = Subject::find($id);

        //Check if datas found or not.
        if (is_null($datas)) {
            $message = 'Data not found.';
            $status = false;
            return response()->json([
                'success' => $status,
                'message' => $message,
                'data' => $datas,
            ]);
        }
        $message = 'Data retrieved successfully.';
        $status = true;

        //Call function for response data
        return response()->json([
            'success' => $status,
            'message' => $message,
            'data' => $datas,
        ]);
    }
    
    public function teams()
    {
        try {
            $datas = Team::select('*')
                ->get();
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kosong.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $datas,
        ]);
    }

    public function teamshow($id)
    {
        $datas = Team::find($id);

        //Check if datas found or not.
        if (is_null($datas)) {
            $message = 'Data not found.';
            $status = false;
            return response()->json([
                'success' => $status,
                'message' => $message,
                'data' => $datas,
            ]);
        }
        $message = 'Data retrieved successfully.';
        $status = true;

        //Call function for response data
        return response()->json([
            'success' => $status,
            'message' => $message,
            'data' => $datas,
        ]);
    }
    
    public function grades()
    {
        try {
            $datas = Grade::select('*')
                ->get();
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kosong.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $datas,
        ]);
    }

    public function gradeshow($id)
    {
        $datas = Grade::find($id);

        //Check if datas found or not.
        if (is_null($datas)) {
            $message = 'Data not found.';
            $status = false;
            return response()->json([
                'success' => $status,
                'message' => $message,
                'data' => $datas,
            ]);
        }
        $message = 'Data retrieved successfully.';
        $status = true;

        //Call function for response data
        return response()->json([
            'success' => $status,
            'message' => $message,
            'data' => $datas,
        ]);
    }

    public function semesters()
    {
        try {
            $datas = Semester::select('*')
                ->get();
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kosong.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $datas,
        ]);
    }

    public function semestershow($id)
    {
        $datas = Semester::find($id);

        //Check if datas found or not.
        if (is_null($datas)) {
            $message = 'Data not found.';
            $status = false;
            return response()->json([
                'success' => $status,
                'message' => $message,
                'data' => $datas,
            ]);
        }
        $message = 'Data retrieved successfully.';
        $status = true;

        //Call function for response data
        return response()->json([
            'success' => $status,
            'message' => $message,
            'data' => $datas,
        ]);
    }

    public function periods()
    {
        try {
            $datas = Periode::select('*')
                ->get();
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kosong.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $datas,
        ]);
    }

    public function periodshow($id)
    {
        $datas = Periode::find($id);

        //Check if datas found or not.
        if (is_null($datas)) {
            $message = 'Data not found.';
            $status = false;
            return response()->json([
                'success' => $status,
                'message' => $message,
                'data' => $datas,
            ]);
        }
        $message = 'Data retrieved successfully.';
        $status = true;

        //Call function for response data
        return response()->json([
            'success' => $status,
            'message' => $message,
            'data' => $datas,
        ]);
    }
    
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

    public function students(Request $request)
    {
        try {
            $members = Student::select('*')
            ->with('grade')
            ->FilterGrade()    
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

    public function studentshow($id)
    {
        $datas = Student::find($id);

        //Check if datas found or not.
        if (is_null($datas)) {
            $message = 'Data not found.';
            $status = false;
            return response()->json([
                'success' => $status,
                'message' => $message,
                'data' => $datas,
            ]);
            // $response = $this->response($status, $datas, $message);
            // return $response;
        }
        $message = 'Data retrieved successfully.';
        $status = true;

        //Call function for response data
        // $response = $this->response($status, $datas, $message);
        // return $response;
        return response()->json([
            'success' => $status,
            'message' => $message,
            'data' => $datas,
        ]);
    }

    public function teachers()
    {
        try {
            $members = Teacher::select('*')
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

    public function teachershow($id)
    {
        $datas = Teacher::find($id);

        //Check if datas found or not.
        if (is_null($datas)) {
            $message = 'Data not found.';
            $status = false;
            return response()->json([
                'success' => $status,
                'message' => $message,
                'data' => $datas,
            ]);
        }
        $message = 'Data retrieved successfully.';
        $status = true;

        //Call function for response data
        return response()->json([
            'success' => $status,
            'message' => $message,
            'data' => $datas,
        ]);
    }

}
