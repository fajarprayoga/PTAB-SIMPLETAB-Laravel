<?php

namespace App\Http\Controllers\Admin;

use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyCustomerRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CustomersController extends Controller
{
    use TraitModel;

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('customer_access'), 403);

        if ($request->ajax()) {
            //set query
            $qry = Customer::FilterMaps($request);

            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'customer_show';
                $editGate = 'customer_edit';
                $deleteGate = 'customer_delete';
                $crudRoutePart = 'customers';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });
            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('email', function ($row) {
                return $row->email ? $row->email : "";
            });

            $table->editColumn('phone', function ($row) {
                return $row->phone ? $row->phone : "";
            });

            $table->editColumn('type', function ($row) {
                return $row->type ? ($row->type == 'public' ? 'Umum' : 'Pelanggan') : "";
            });

            $table->editColumn('address', function ($row) {
                return $row->address ? $row->address : "";
            });

            $table->editColumn('gender', function ($row) {
                return $row->gender ? ($row->gender == 'male' ? 'Laki-laki' : 'Perempuan') : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //default view
        return view('admin.customers.index');

    }

    public function create()
    {
        $last_code = $this->get_last_code('public');
        $code = acc_code_generate($last_code, 8, 3);

        abort_unless(\Gate::allows('customer_create'), 403);
        return view('admin.customers.create', compact('code'));
    }

    public function store(StoreCustomerRequest $request)
    {
        abort_unless(\Gate::allows('customer_create'), 403);

        $customer = new Customer;
        $customer->name = $request->name;
        $customer->code = $request->code;
        $customer->email = $request->email;
        $customer->password = bcrypt($request->password);
        $customer->phone = $request->phone;
        $customer->type = $request->type;
        $customer->gender = $request->gender;
        $customer->address = $request->address;
        $customer->_synced = 0;
        $customer->save();

        return redirect()->route('admin.customers.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('customer_edit'), 403);
        $customer = Customer::WhereMaps('id', $id)->first();
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request)
    {
        abort_unless(\Gate::allows('customer_edit'), 403);

        $customer = Customer::find($request->code);
        $customer->name = $request->name;
        $customer->code = $request->code;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->type = $request->type;
        $customer->gender = $request->gender;
        $customer->address = $request->address;
        $customer->_synced = 0;
        $customer->save();

        return redirect()->route('admin.customers.index');
    }

    public function destroy(Customer $customer)
    {
        abort_unless(\Gate::allows('customer_delete'), 403);

        $customer->delete();

        return back();
    }

    public function massDestroy(MassDestroyCustomerRequest $request)
    {
        # code...
    }
}
