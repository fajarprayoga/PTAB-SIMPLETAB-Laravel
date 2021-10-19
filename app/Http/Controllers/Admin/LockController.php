<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreActionRequest;
use App\Dapertement;
use App\Lock;
use App\LockAction;
use App\Action;
use App\Staff;
use DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Database\QueryException;
class LockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        abort_unless(\Gate::allows('lock_access'), 403);
        $qry = Lock::with('customer')->with('subdapertement');
        if(request()->input('status') != ''){
            $qry = Lock::FilterStatus(request()->input('status'))
                ->with('customer')
                ->with('subdapertement')
                ->orderBy('created_at', 'DESC')
                ->get();
        }

     
      
        if ($request->ajax()) {
            $table = Datatables::of($qry);
            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('staff', '&nbsp;');

            $table->editColumn('staff', function ($row) {
                $viewGate = '';
                $editGate = '';
                $deleteGate = 'lock_delete';
                $staffGate = 'lock_access';
                $actionLockGate = 'lock_access';
                $crudRoutePart = 'lock';
                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'staffGate',
                    'actionLockGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('customer', function ($row) {
                return $row->customer ? $row->customer->name : "";
            });

            $table->editColumn('description', function ($row) {
                return $row->description ? $row->description : "";
            });
            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });
            $table->editColumn('subdapertement', function ($row) {
                return $row->subdapertement ? $row->subdapertement->name : "";
            });
            $table->editColumn('start', function ($row) {
                return $row->start ? $row->start : "";
            });
            $table->editColumn('end', function ($row) {
                return $row->end ? $row->end : "";
            });
       
            $table->rawColumns(['staff', 'placeholder']);
         

            $table->addIndexColumn();
            
            return $table->make(true);
        }
     
        return view('admin.lock.index');
        // return $subdepartementlist;
    

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort_unless(\Gate::allows('lock_create'), 403);
        $dapertements = Dapertement::all();
        return view('admin.lock.create', compact('dapertements'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('lock_create'), 403);
        $request->validate([
            'code' => 'required',
            'customer_id' => 'required',
            'subdapertement_id' => 'required',
            'description' => 'required',
        ]);

        
        try {

            $lock = Lock::create($request->all());
            return redirect()->route('admin.lock.index');
        } catch (\Throwable $th) {
            return back()->withErrors($th);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lock $lock)
    {
        abort_unless(\Gate::allows('lock_delete'), 403);
        try {
            $lock->delete();
            return back();

        } catch (QueryException $e) {
            
            return back()->withErrors(['Mohon hapus dahulu data yang terkait']);
        }

    }

    public function lockactionStaff($lockaction_id)
    {
        abort_unless(\Gate::allows('lock_staff_access'), 403);
        $action = Lock::findOrFail($lockaction_id);

        return view('admin.lock.actionStaff', compact('action'));
    }

    public function lockactionStaffCreate($lockaction_id)
    {
        
        abort_unless(\Gate::allows('lock_staff_create'), 403);

        $action = Lock::findOrFail($lockaction_id);

        $action_staffs = Lock::where('id', $lockaction_id)->with('staff')->first();

        $staffs = Staff::where('subdapertement_id', $action->subdapertement_id)->get();

        $action_staffs_list = DB::table('staffs')
            ->join('action_staff', function ($join) {
                $join->on('action_staff.staff_id', '=', 'staffs.id')
                    ->where('action_staff.status', '!=', 'close');
            })
            ->get();

        return view('admin.lock.actionStaffCreate', compact('lockaction_id', 'staffs', 'action', 'action_staffs', 'action_staffs_list'));

    }

    public function lockactionStaffStore(Request $request)
    {
        abort_unless(\Gate::allows('lock_staff_create'), 403);
        $action = Lock::findOrFail($request->lockaction_id);

        if ($action) {
            $cek = $action->staff()->attach($request->staff_id);

        }

        return redirect()->route('admin.lock.actionStaff', $request->lockaction_id);
    }

    public function lockactionStaffDestroy($lockaction_id, $staff_id)
    {
        abort_unless(\Gate::allows('lock_staff_delete'), 403);

        $action = Lock::findOrFail($lockaction_id);

        if ($action) {
            $cek = $action->staff()->detach($staff_id);

            if ($cek) {
                $action = Lock::where('id', $lockaction_id)->with('staff')->first();

                $cekAllStatus = false;
                $statusAction = 'close';
              
                $dateNow = date('Y-m-d H:i:s');

                $action->update([
                    'status' => $statusAction,
                    'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $dateNow,
                ]);
            }

        }

        return redirect()->route('admin.lock.actionStaff', $lockaction_id);
    }

    function list($lockaction_id) {
        abort_unless(\Gate::allows('action_access'), 403);
            $actions = LockAction::with('subdapertement')
                ->with('lock')
                ->where('lock_id', $lockaction_id)
                ->get();
        // return $actions;
        // return $actions
        return view('admin.lock.list', compact('lockaction_id', 'actions'));
    }

    public function actioncreate($lock_id)
    {
        abort_unless(\Gate::allows('action_create'), 403);
        $lock = Lock::findOrFail($lock_id);
        $dapertements = Dapertement::where('id', $lock->dapertement_id)->get();

        $staffs = Staff::all();


        return view('admin.lock.actionCreate', compact('dapertements', 'lock_id', 'staffs'));
    }

    public function lockstore(Request $request)
    {
        abort_unless(\Gate::allows('action_create'), 403);
        $dateNow = date('Y-m-d H:i:s');
       
        if ($request->file('image')) {
            $img_path = "/pdf";
            $basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
            foreach ($request->file('image') as $key => $image) {
            $resourceImage = $image;
            $nameImage = time() + $key;
            $file_extImage = $image->extension();
            $nameImage = str_replace(" ", "-", $nameImage);
            $img_name = $img_path . "/" . $nameImage . "." . $file_extImage;
    
            $resourceImage->move($basepath . $img_path, $img_name);
            $dataImageName[] = $nameImage . "." . $file_extImage;
            }
        }
        $data = array(
            'code' => $request->code,
            'type' => $request->type,
            'image' => str_replace("\/", "/", json_encode($dataImageName)),
            'memo' => $request->memo,
            'lock_id' => $request->lock_id,
        );
        
        $action = LockAction::create($data);
        // return $action;
        return redirect()->route('admin.lock.list', $request->lock_id);
    }
   
    public function lockactionDestroy(Request $request, LockAction $action)
    {
        abort_unless(\Gate::allows('action_delete'), 403);

        $action->delete();

        return redirect()->route('admin.lock.list', $action->lock_id);
    }

    public function LockView($lock_id)
    {
        abort_unless(\Gate::allows('action_staff_edit'), 403);

        $lock = LockAction::findOrFail($lock_id);
        return view('admin.lock.lockView', compact('lock'));
        
    }

}
