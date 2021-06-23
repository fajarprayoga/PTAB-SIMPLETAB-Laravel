<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Dapertement;
use App\Http\Requests\StoreActionRequest;
use App\Ticket;
use App\Staff;
use App\Action;
use Yajra\DataTables\Facades\DataTables;
use DB;
use App\Traits\TraitModel;

class ActionsController extends Controller
{
    use TraitModel;

    public function index()
    {
        abort_unless(\Gate::allows('action_access'), 403);

        return view('admin.actions.index');
    }

    public function create($ticket_id)
    {
        abort_unless(\Gate::allows('action_create'), 403);

        $dapertements = Dapertement::all();

        $staffs = Staff::all();

        return view('admin.actions.create', compact('dapertements', 'ticket_id', 'staffs'));
    }

    public function store(StoreActionRequest $request)
    {
        abort_unless(\Gate::allows('action_create'), 403);

        $dateNow = date('Y-m-d H:i:s');

        $data = array(
            'description' => $request->description,
            'status' => 'pending',
            'dapertement_id' => $request->dapertement_id,
            'ticket_id' => $request->ticket_id,
            'start' => $dateNow,
        );

        $action = Action::create($data);

        return redirect()->route('admin.actions.list', $request->ticket_id);
    }

    public function show($id)
    {
        abort_unless(\Gate::allows('action_show'), 403);
    }

    public function edit(Action $action)
    {
        abort_unless(\Gate::allows('action_edit'), 403);

        $dapertements = Dapertement::all();

        $tickets = Ticket::all();

        $staffs = Staff::all();
        
        return view('admin.actions.edit', compact('dapertements', 'tickets', 'staffs', 'action'));

    }

    public function update(Request $request, Action $action)
    {
        abort_unless(\Gate::allows('action_edit'), 403);
        
        $action->update($request->all());

        return redirect()->route('admin.actions.list', $action->ticket_id);

    }

    public function destroy(Request $request,Action $action)
    {
        abort_unless(\Gate::allows('action_delete'), 403);

        $data = [];
        foreach ($action->staff as $key => $staff) {
            $data[$key] = $staff->id;
        }

         $cek = $action->staff()->detach($data);

        $action->delete();

        return redirect()->route('admin.actions.list', $action->ticket_id);
    }

    // get staff
    public function staff(Request $request)
    {
        abort_unless(\Gate::allows('staff_access'), 403);
        $staffs = Staff::where('dapertement_id', $request->dapertement_id)->get();

        return json_encode($staffs);
    }

    // list tindakan
    public function list($ticket_id)
    {
        abort_unless(\Gate::allows('action_access'), 403);

        $actions = Action::with('staff')->with('dapertement')->with('ticket')->where('ticket_id', $ticket_id)->orderBy('start', 'desc')->get();

        return view('admin.actions.list', compact('actions', 'ticket_id'));
        // dd($actions);
    }


// list pegawai
    public function actionStaff($action_id)
    {
        abort_unless(\Gate::allows('action_staff_access'), 403);

        $action = Action::findOrFail($action_id);

        // $staffs = $action->staff;

        return view('admin.actions.actionStaff', compact('action'));
    }

    // nambah staff untuk tindakan 
    public function actionStaffCreate($action_id)
    {

        abort_unless(\Gate::allows('action_staff_create'), 403);

        $action = Action::findOrFail($action_id);

        $action_staffs = Action::where('id', $action_id)->with('staff')->first();

        $staffs = Staff::where('dapertement_id', $action->dapertement_id)->get();
        
        // $staffs = Staff::where('dapertement_id', $action->dapertement_id)->with('action')->get();

        $action_staffs_list = DB::table('staffs')
        ->join('action_staff', 'action_staff.staff_id', '=', 'staffs.id')
        ->get();

        return view('admin.actions.actionStaffCreate', compact('action_id', 'staffs', 'action', 'action_staffs', 'action_staffs_list'));

        // dd($action_staffs_list);
    }

    // store pegawai untuk tindakan 

    public function actionStaffStore(Request $request)
    {
        abort_unless(\Gate::allows('action_staff_create'), 403);
        
        $action = Action::findOrFail($request->action_id);

        if($action){
           $cek =  $action->staff()->attach($request->staff_id, [ 'status' => 'pending' ]);

            if($cek){
                $action = Action::where('id',$request->action_id)->with('staff')->first();
    
                // dd($action->staff[0]->pivot->status);
                $cekAllStatus = false;
                $statusAction = 'close';
                for ($status=0;  $status < count($action->staff) ; $status++) { 
                    // dd($action->staff[$status]->pivot->status);
                    if($action->staff[$status]->pivot->status =='pending' ){
                        $statusAction = 'pending';
                        break;
                    }else if($action->staff[$status]->pivot->status =='active' ){
                      
                        $statusAction = 'active';
                    }
                }
                
                $dateNow = date('Y-m-d H:i:s');
    
                $action->update([
                    'status' => $statusAction,
                    'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $dateNow
                ]);
            }
        }

        return redirect()->route('admin.actions.actionStaff', $request->action_id);
    }

    // update pegawai tindakan 

    public function actionStaffEdit($action_id, $staff_id)
    {
        abort_unless(\Gate::allows('action_staff_edit'), 403);

        $action = Action::findOrFail($action_id);

        $action_staffs_list = DB::table('staffs')
        ->join('action_staff', 'action_staff.staff_id', '=', 'staffs.id')
        ->where('action_id', $action_id)
        ->where('staff_id', $staff_id)
        ->first();
        return view('admin.actions.actionStaffEdit', compact('action_staffs_list', 'action'));
    }

    public function actionStaffUpdate(Request $request)
    {
        abort_unless(\Gate::allows('action_staff_edit'), 403);

        $action = Action::where('id',$request->action_id)->with('staff')->first();

        if($action){
            $cek = $action->staff()->updateExistingPivot($request->staff_id, [ 'status' => $request->status ]);
        }

        if($cek){
            $action = Action::where('id',$request->action_id)->with('staff')->first();

            // dd($action->staff[0]->pivot->status);
            $cekAllStatus = false;
            $statusAction = 'close';
            for ($status=0;  $status < count($action->staff) ; $status++) { 
                // dd($action->staff[$status]->pivot->status);
                if($action->staff[$status]->pivot->status =='pending' ){
                    $statusAction = 'pending';
                    break;
                }else if($action->staff[$status]->pivot->status =='active' ){
                  
                    $statusAction = 'active';
                }
            }
            
            $dateNow = date('Y-m-d H:i:s');

            $action->update([
                'status' => $statusAction,
                'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $dateNow
            ]);
        }

        return redirect()->route('admin.actions.actionStaff', $action->id);

    }

    // editt status tindakan pegawai
    public function actionStaffDestroy($action_id, $staff_id)
    {
        abort_unless(\Gate::allows('action_staff_delete'), 403);
    
        $action = Action::findOrFail($action_id);

        if($action){
            $cek = $action->staff()->detach($staff_id);

            if($cek){
                $action = Action::where('id',$action_id)->with('staff')->first();
    
                // dd($action->staff[0]->pivot->status);
                $cekAllStatus = false;
                $statusAction = 'close';
                for ($status=0;  $status < count($action->staff) ; $status++) { 
                    // dd($action->staff[$status]->pivot->status);
                    if($action->staff[$status]->pivot->status =='pending' ){
                        $statusAction = 'pending';
                        break;
                    }else if($action->staff[$status]->pivot->status =='active' ){
                      
                        $statusAction = 'active';
                    }
                }
                
                $dateNow = date('Y-m-d H:i:s');
    
                $action->update([
                    'status' => $statusAction,
                    'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $dateNow
                ]);
            }
            
        }
        

        return redirect()->route('admin.actions.actionStaff', $action_id);
    }
}
