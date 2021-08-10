<?php

namespace App\Http\Controllers\api\v1\admin;

use App\ActionApi;
use App\Http\Controllers\Controller;
use App\StaffApi;
use App\TicketApi;
use App\Traits\TraitModel;
use App\User;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Berkayk\OneSignal\OneSignalClient;
use OneSignal;

class ActionsApiController extends Controller
{
    use TraitModel;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    function list($ticket_id) {
        try {
            $actions = ActionApi::with('staff')->with('dapertement')->with('ticket')->where('ticket_id', $ticket_id)->orderBy('start', 'desc')->get();
            return response()->json([
                'message' => 'Data Ticket',
                'data' => $actions,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'data' => $ex,
            ]);
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dateNow = date('Y-m-d H:i:s');

        $data = $request->all();

        $rules = array(
            'description' => 'required',
            'ticket_id' => 'required',
            'dapertement_id' => 'required',
        );

        $validator = \Validator::make($data, $rules);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $errors = $messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $request->all(),
            ]);
        }

        $data['status'] = 'pending';
        $data['start'] = $dateNow;

        $action = ActionApi::create($data);

        //send notif to humas
        $admin = User::where('dapertement_id', 1)->first();
        $id_onesignal = $admin->_id_onesignal;
        $message = 'Tindakan Baru Dibuat : '.$request->description;
        if (!empty($id_onesignal)) {
            OneSignal::sendNotificationToUser(
                $message,
                $id_onesignal,
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null
            );}

        //send notif to departement terkait
        $admin_arr = User::where('dapertement_id', $request->dapertement_id)->get();
        foreach ($admin_arr as $key => $admin) {
            $id_onesignal = $admin->_id_onesignal;
            $message = 'Tindakan Baru Dibuat : ' . $request->description;
            if (!empty($id_onesignal)) {
                OneSignal::sendNotificationToUser(
                    $message,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );}}

        return response()->json([
            'message' => 'Data Dapertement Add Success',
            'data' => $action,
        ]);
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
    public function update(Request $request, ActionApi $action)
    {
        $rules = array(
            'description' => 'required',
            'dapertement_id' => 'required',
        );

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $errors = $messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $request->all(),
            ]);
        }

        //send notif to humas
        $admin = User::where('dapertement_id', 1)->first();
        $id_onesignal = $admin->_id_onesignal;
        $message = 'Tindakan Baru Diupdate : '.$request->description;
        if (!empty($id_onesignal)) {
            OneSignal::sendNotificationToUser(
                $message,
                $id_onesignal,
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null
            );}
        
        //send notif to departement terkait
        $admin_arr = User::where('dapertement_id', $request->dapertement_id)->get();
        foreach ($admin_arr as $key => $admin) {
            $id_onesignal = $admin->_id_onesignal;
            $message = 'Tindakan Baru Diupdate : ' . $request->description;
            if (!empty($id_onesignal)) {
                OneSignal::sendNotificationToUser(
                    $message,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );}}

        $action->update($request->all());
        return response()->json([
            'message' => 'Data Dapertement Edit Success',
            'data' => $action,
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ActionApi $action)
    {
        try {

            $action->delete();
            return response()->json([
                'message' => 'Staff berhasil di hapus',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'data masih ada dalam daftar keluhan',
                'data' => $e,
            ]);
        }
    }

    public function actionStaffs($action_id)
    {

        try {
            $action = ActionApi::where('id', $action_id)->with('staff')->with('dapertement')->first();
            return response()->json([
                'message' => 'sucssess',
                'data' => $action,
            ]);

        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'sucssess',
                'data' => $ex,
            ]);
        }

        // $staffs = $action->staff;

    }

    public function actionStaffLists($action_id)
    {
        try {
            $action = ActionApi::findOrFail($action_id);

            $action_staffs = ActionApi::where('id', $action_id)->with('staff')->first();

            $staffs = StaffApi::where('dapertement_id', $action->dapertement_id)->get();

            // $staffs = Staff::where('dapertement_id', $action->dapertement_id)->with('action')->get();

            $action_staff_lists = DB::table('staffs')
                ->join('action_staff', 'action_staff.staff_id', '=', 'staffs.id')
                ->get();

            $data = [
                'action' => $action,
                'action_staffs' => $action_staffs,
                'staffs' => $staffs,
                'action_staff_lists' => $action_staff_lists,
            ];

            return response()->json([
                'message' => 'success',
                'data' => $data,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal ambil data',
                'data' => $ex,
            ]);
        }
    }

    public function actionStaffStore(Request $request)
    {

        try {
            $rules = array(
                'action_id' => 'required',
                'staff_id' => 'required',
            );

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->messages();
                $errors = $messages->all();
                return response()->json([
                    'message' => $errors,
                    'data' => $request->all(),
                ]);
            }

            $action = ActionApi::with('ticket')->find($request->action_id);

            if ($action) {
                $cek = $action->staff()->attach($request->staff_id, ['status' => 'pending']);

                if ($cek) {
                    $action = Action::where('id', $request->action_id)->with('staff')->first();

                    // dd($action->staff[0]->pivot->status);
                    $cekAllStatus = false;
                    $statusAction = 'close';
                    for ($status = 0; $status < count($action->staff); $status++) {
                        // dd($action->staff[$status]->pivot->status);
                        if ($action->staff[$status]->pivot->status == 'pending') {
                            $statusAction = 'pending';
                            break;
                        } else if ($action->staff[$status]->pivot->status == 'active') {

                            $statusAction = 'active';
                        }
                    }

                    $dateNow = date('Y-m-d H:i:s');

                    $action->update([
                        'status' => $statusAction,
                        'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $dateNow,
                    ]);
                }

                //send notif to humas
        $admin = User::where('dapertement_id', 1)->first();
        $id_onesignal = $admin->_id_onesignal;
        $message = 'Petugas Baru Dipilih : '.$action->ticket->description;
        if (!empty($id_onesignal)) {
            OneSignal::sendNotificationToUser(
                $message,
                $id_onesignal,
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null
            );}
                
                //send notif to departement terkait
                $admin_arr = User::where('dapertement_id', $action->dapertement_id)->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Petugas Baru Dipilih : ' . $action->ticket->description;
                    if (!empty($id_onesignal)) {
                        OneSignal::sendNotificationToUser(
                            $message,
                            $id_onesignal,
                            $url = null,
                            $data = null,
                            $buttons = null,
                            $schedule = null
                        );}}

                return response()->json([
                    'message' => 'staff Berhasil di tambahkan ',
                    'data' => $action,
                ]);
            }

        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal tambah staff ',
                'data' => $ex,
            ]);
        }

    }

    public function actionStaffUpdate(Request $request)
    {
        try {

            $rules = array(
                'action_id' => 'required',
                'staff_id' => 'required',
                'status' => 'required',
            );

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->messages();
                $errors = $messages->all();
                return response()->json([
                    'message' => $errors,
                    'data' => $request->all(),
                ]);
            }

            $action = ActionApi::where('id', $request->action_id)->with('ticket')->with('staff')->first();
            $idStaff = $request->staff_id;
            if ($action) {
                $cek = $action->staff()->updateExistingPivot($request->staff_id, ['status' => $request->status]);
                //    $cek =  $action->staff()->sync([$idStaff => [ 'status' => $request->status] ], false);
            }

            if ($cek) {
                $action = ActionApi::where('id', $request->action_id)->with('ticket')->with('staff')->first();

                //     // dd($action->staff[0]->pivot->status);
                $cekAllStatus = false;
                $statusAction = 'close';
                for ($status = 0; $status < count($action->staff); $status++) {
                    // dd($action->staff[$status]->pivot->status);
                    if ($action->staff[$status]->pivot->status == 'pending') {
                        $statusAction = 'pending';
                        break;
                    } else if ($action->staff[$status]->pivot->status == 'active') {

                        $statusAction = 'active';
                    }
                }

                $dateNow = date('Y-m-d H:i:s');

                $action->update([
                    'status' => $statusAction,
                    'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $dateNow,
                ]);

                // update ticket
                $statusTicket = 'close';
                if ($action) {
                    $actionStatusAll = ActionApi::where('ticket_id', $action->ticket_id)->with('ticket')->get();

                    for ($i = 0; $i < count($actionStatusAll); $i++) {
                        if ($actionStatusAll[$i]->status == 'pending') {
                            $statusTicket = 'pending';
                            break;
                        } else if ($actionStatusAll[$i]->status == 'active') {
                            $statusTicket = 'active';
                        }
                    }

                    $ticket = TicketApi::findOrFail($action->ticket_id);

                    $ticket->update([
                        'status' => $statusTicket,
                    ]);
                    // $actionStatusAll->update([
                    //     'status' => $statusTicket,
                    // ]);

                    // dd($statusTicket);
                }

                $admin = User::where('dapertement_id', 1)->first();
        $id_onesignal = $admin->_id_onesignal;
        $message = 'Petugas Diupdate : '.$action->ticket->description;
        if (!empty($id_onesignal)) {
            OneSignal::sendNotificationToUser(
                $message,
                $id_onesignal,
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null
            );}
                
                //send notif to departement terkait
                $admin_arr = User::where('dapertement_id', $action->dapertement_id)->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Petugas Diupdate : ' . $action->ticket->description;
                    if (!empty($id_onesignal)) {
                        OneSignal::sendNotificationToUser(
                            $message,
                            $id_onesignal,
                            $url = null,
                            $data = null,
                            $buttons = null,
                            $schedule = null
                        );}}

                return response()->json([
                    'message' => 'Status di ubah ',
                    'data' => $action,
                ]);
            } else {
                return $request->all();
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal tambah staff ',
                'data' => $ex,
            ]);
        }
    }

    public function actionStaffDestroy($action_id, $staff_id)
    {
        // abort_unless(\Gate::allows('action_staff_delete'), 403);

        try {
            $action = ActionApi::findOrFail($action_id);

            if ($action) {
                $cek = $action->staff()->detach($staff_id);

                if ($cek) {
                    $action = ActionApi::where('id', $action_id)->with('staff')->first();

                    // dd($action->staff[0]->pivot->status);
                    $cekAllStatus = false;
                    $statusAction = 'close';
                    for ($status = 0; $status < count($action->staff); $status++) {
                        // dd($action->staff[$status]->pivot->status);
                        if ($action->staff[$status]->pivot->status == 'pending') {
                            $statusAction = 'pending';
                            break;
                        } else if ($action->staff[$status]->pivot->status == 'active') {

                            $statusAction = 'active';
                        }
                    }

                    $dateNow = date('Y-m-d H:i:s');

                    $action->update([
                        'status' => $statusAction,
                        'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $dateNow,
                    ]);

                    return response()->json([
                        'message' => 'Berhasil di hapus ',
                        'data' => $action,
                    ]);
                }
            }
        } catch (QueryException $th) {
            return response()->json([
                'message' => 'gagal tambah staff ',
                'data' => $ex,
            ]);
        }
    }
}
