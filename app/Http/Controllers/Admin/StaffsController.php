<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Dapertement;
use App\Staff;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\TraitModel;
use Illuminate\Database\QueryException;

class StaffsController extends Controller
{
    use TraitModel;

    public function index(Request $request)
    {
        if ($request->ajax()) {
            //set query
            if(request()->input('dapertement_id')!=""){
                $dapertement_id = request()->input('dapertement_id'); 
    
                $qry = Staff::where('dapertement_id', $dapertement_id);
            }else{
                $qry = Staff::get();
            }  

            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = '';
                $editGate = 'staff_edit';
                $deleteGate = 'staff_delete';
                $crudRoutePart = 'staffs';

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

            $table->editColumn('dapertement', function ($row) {
                return $row->dapertement ? $row->dapertement->name : "";
            });
            
            $table->editColumn('phone', function ($row) {
                return $row->phone ? $row->phone : "";
            });


            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }

        $dapertements = Dapertement::all();

        return view('admin.staffs.index', compact('dapertements'));
    }

    public function create()
    {
        $last_code = $this->get_last_code('staff');

        $code = acc_code_generate($last_code, 8, 3);

        abort_unless(\Gate::allows('staff_create'), 403);
        $dapertements = Dapertement::all();
        
        return view('admin.staffs.create', compact('dapertements', 'code'));
    }

    public function store(StoreStaffRequest $request)
    {
        $staff = Staff::create($request->all());

        return redirect()->route('admin.staffs.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('staff_edit'), 403);

        $staff = Staff::findOrFail($id);

        $dapertements = Dapertement::all();

        return view('admin.staffs.edit', compact('staff', 'dapertements'));
    }

    public function update(UpdateStaffRequest $request, Staff $staff)
    {
        abort_unless(\Gate::allows('staff_edit'), 403);

        $staff->update($request->all());

        return redirect()->route('admin.staffs.index');
    }

    public function destroy(Staff $staff)
    {
        abort_unless(\Gate::allows('staff_delete'), 403);

        try{
            
            $staff->delete();
        }
        catch(QueryException $e) {
            return back()->withErrors(['Pegawai masih terdaftar dalam data Tiket']);
        }

        return back();
    }

    public function massDestroy()
    {
        # code...
    }
}
