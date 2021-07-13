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

        $code = acc_code_generate($last_code, 8, 3);
        
        $data = $request->all();
        // isset($request->email) ? $data['email'] = $request->email : null;

        $rules=array(
            // 'email' => 'email|unique:customers,email',
            // 'code' => 'unique:customers,code',
            'name' => 'required',
            'phone' => 'required|unique:customers,phone',
            'type' => 'required',
            'gender' => 'required',
            'address' => 'required'
        );

        // $rules['email'] = isset($request->email) ? 'required|email|unique:customers,email' : null;
        // $rules['code'] = isset($request->code) ? 'required|unique:customers,code' : null;

        if(isset($request->email)){
            $rules['email'] ='required|email|unique:customers,email';
        }

        if(isset($request->code)){
            $rules['code'] = 'required|unique:customers,code';
        }
        
        $data['code'] = isset($request->code) ? $request->code : $code;
        $data['password'] =  bcrypt($request->password);

        $validator=\Validator::make($data,$rules);
        if($validator->fails())
        {
            $messages=$validator->messages();
            $errors=$messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $request->all()
            ]);
        }

        $customer = CustomerApi::create($data);

        return response()->json([
            'message' => 'Data Customer Add Success',
            'data' => $customer
        ]);

        // return $rules;

    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, CustomerApi $customer)
    {
        $rules=array(
            'email' => 'required|email|unique:customers,email,'.$customer->id,
            'code' => 'required|unique:customers,code,'.$customer->id,
            'name' => 'required',
            'phone' => 'required|unique:customers,phone,'.$customer->id,
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

        
        $customer->update($request->all());

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
