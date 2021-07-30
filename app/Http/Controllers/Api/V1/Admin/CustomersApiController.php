<?php

namespace App\Http\Controllers\api\v1\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CustomerApi;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Database\QueryException;
use App\Traits\TraitModel;

class CustomersApiController extends Controller
{
    
    use TraitModel;

    public function index()
    {
        $customer = CustomerApi::all();

        return response()->json([
            'message' => 'Sucess',
            'data' => $customer
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {


        $last_code = $this->get_last_code('customer');

        //$code = acc_code_generate($last_code, 8, 3);
        $code = $last_code + 1;
        
        $rules=array(
            'name' => 'required',
            'phone' => 'required|unique:mysql2.tblpelanggan,telp',
            'type' => 'required',
            'gender' => 'required',
            'address' => 'required'
        );

        $validator=\Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            $messages=$validator->messages();
            $errors=$messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $request->all()
            ]);
        }

        $customer = new CustomerApi;
        $customer->name = $request->name;
        if(!isset($request->code)){
            $customer->code = $code;
        }else{
            $request->validate([
                'code' => 'required|unique:mysql2.tblpelanggan,nomorrekening',
            ]);
            $customer->code = $request->code;
        }       
        if(!isset($request->email)){
            $customer->email = null;
        }else{
            $request->validate([
                'email' => 'required|email',
            ]);
            $customer->email = $request->email;
        }
        $customer->email_verified_at = null;
        $customer->remember_token = null;
        $customer->password = bcrypt($request->password);
        $customer->phone = $request->phone;
        $customer->type = $request->type;
        $customer->gender = $request->gender;
        $customer->address = $request->address;
        $customer->_synced = 0;

        try {
            $customer->save();
            return response()->json([
                'message' => 'Registrasi Berhasil',
                'data' => $customer
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                // 'message' => 'Registrasi Berhasil',
                // 'token' => $token,
                'data' => $request->email
            ]);
        }

    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request)
    {
        $customer = Customer::find($request->code);
        $rules=array(
            'email' => 'required|email',
            'code' => 'required|unique:mysql2.tblpelanggan,nomorrekening,'.$request->code,
            'name' => 'required',
            'phone' => 'required|unique:mysql2.tblpelanggan,telp,'.$request->code,
            'type' => 'required',
            'gender' => 'required',
            'address' => 'required'
        );
        $validator=\Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            $messages=$validator->messages();
            $errors=$messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $customer
            ]);
        }
        
        $customer->name = $request->name;
        $customer->code = $request->code;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->type = $request->type;
        $customer->gender = $request->gender;
        $customer->address = $request->address;
        $customer->_synced = 0;
        $customer->save();

        return response()->json([
            'message' => 'Data Customer Update Success',
            'data' => $customer
        ]);

    }

    public function destroy(CustomerApi $customer)
    {
        // abort_unless(\Gate::allows('staff_delete'), 403);

        try{
            
            $customer->delete();
            return response()->json([
                'message' => 'Customer berhasil di hapus',
            ]);
        }
        catch(QueryException $e) {
           return response()->json([
               'message' => 'data masih ada dalam daftar keluhan',
               'data' => $e
           ]);
        }

    }
}
