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
class ActionsController extends Controller
{
    public function index()
    {
        abort_unless(\Gate::allows('action_access'), 403);

        return view('admin.actions.index');
    }

    public function create()
    {
        abort_unless(\Gate::allows('action_create'), 403);

        $dapertements = Dapertement::all();

        $tickets = Ticket::all();

        $staffs = Staff::all();

        return view('admin.actions.create', compact('dapertements', 'tickets', 'staffs'));
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

        $staffs = $request->staff;

        $action = Action::create($data);

        if($action){

            for($staff = 0; $staff < count($staffs); $staff ++){
                $action->staff()->attach($staffs[$staff], [ 'status' => 'pending' ]);
            }

        }

        return redirect()->route('admin.actions.index');
    }

    public function show($id)
    {
        abort_unless(\Gate::allows('action_show'), 403);
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('action_edit'), 403);
    }

    public function update(Request $request, $id)
    {
        abort_unless(\Gate::allows('action_edit'), 403);
    }

    public function staff(Request $request)
    {
        abort_unless(\Gate::allows('staff_access'), 403);
        $staffs = Staff::where('dapertement_id', $request->dapertement_id)->get();

        return json_encode($staffs);
    }

    public function list($ticket_id)
    {
        abort_unless(\Gate::allows('action_access'), 403);

        $actions = Action::with('staff')->with('dapertement')->with('ticket')->where('ticket_id', $ticket_id)->get();

        return view('admin.actions.list', compact('actions'));
        // dd($actions);
    }

    public function destroy($id)
    {
        abort_unless(\Gate::allows('action_delete'), 403);
    }
}
