<?php

namespace App\Http\Controllers\api\v1\admin;

use App\CustomerApi;
use App\Http\Controllers\Controller;
use App\TicketApi;
use App\Ticket_Image;
use App\Traits\TraitModel;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Berkayk\OneSignal\OneSignalClient;
use OneSignal;

class TicketsApiController extends Controller
{
    use TraitModel;

    public function tickets(Request $request)
    {
        $department = '';
        if (isset($request->userid) && $request->userid != '') {
            $admin = User::with('roles')->find($request->userid);
            $role = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (!in_array("ticket_all_access", $permission)) {
                $department = $admin->dapertement_id;
            }
        }
        try {

            $ticket = TicketApi::FilterStatus($request->status)
                ->FilterDepartment($department)
                ->orderBy('id', 'DESC')
                ->with('department')
                ->with('customer')
                ->with('category')
                ->with('ticket_image')
                ->paginate(10, ['*'], 'page', $request->page);

            return response()->json([
                'message' => 'success',
                'data' => $ticket,
                'page' => $request->page,
                'seacrh' => $request->search,
                'department' => $department,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data' => $ex,
            ]);
        }
    }

    public function ticketsBAK(Request $request)
    {
        try {

            if ($request->department != '') {
                $ticket = TicketApi::selectRaw("DISTINCT tickets.*")
                    ->join('actions', 'tickets.id', '=', 'actions.ticket_id')
                    ->FilterJoinStatus($request->status)
                    ->FilterJoinDepartment($request->department)
                    ->orderBy('id', 'DESC')
                    ->with('customer')
                    ->with('category')
                    ->with('ticket_image')
                    ->paginate(10, ['*'], 'page', $request->page);
            } else {
                if ($request->status != '') {
                    $ticket = TicketApi::where('status', $request->status)->orderBy('id', 'DESC')->with('customer')->with('category')->with('ticket_image')->paginate(10, ['*'], 'page', $request->page);
                } else {
                    $ticket = TicketApi::orderBy('id', 'DESC')->with('customer')->with('category')->with('ticket_image')->paginate(10, ['*'], 'page', $request->page);
                }
            }

            return response()->json([
                'message' => 'success',
                'data' => $ticket,
                'page' => $request->page,
                'seacrh' => $request->search,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data' => $ex,
            ]);
        }
    }

    public function index()
    {
        try {
            $ticket = TicketApi::orderBy('id', 'DESC')->with('department')->with('customer')->with('category')->with('ticket_image')->get();
            return response()->json([
                'message' => 'Data Ticket',
                'data' => $ticket,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
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
        $last_code = $this->get_last_code('ticket');

        $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/complaint";
        $basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $dataForm = json_decode($request->form);
        $responseImage = '';

        $customer_code = CustomerApi::WhereMaps('id', $dataForm->customer_id)->first();

        if (!$customer_code) {
            return response()->json([
                'message' => 'Code Pelanggan tidak ditemukan',
            ]);
        }

        $dataQtyImage = json_decode($request->qtyImage);
        for ($i = 1; $i <= $dataQtyImage; $i++) {
            if ($request->file('image' . $i)) {
                $resourceImage = $request->file('image' . $i);
                $nameImage = strtolower($code);
                $file_extImage = $request->file('image' . $i)->extension();
                $nameImage = str_replace(" ", "-", $nameImage);

                $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->customer_id . $i . "." . $file_extImage;

                $resourceImage->move($basepath . $img_path, $img_name);

                $dataImageName[] = $img_name;
            } else {
                $responseImage = 'Image tidak di dukung';
                break;
            }
        }

        if ($responseImage != '') {
            return response()->json([
                'message' => $responseImage,
            ]);
        }

        $video_name = '';
        if ($request->file('video')) {

            $video_path = "/videos/complaint";
            $resource = $request->file('video');
            // $filename = $resource->getClientOriginalName();
            // $file_extVideo = $request->file('video')->extension();
            $video_name = $video_path . "/" . strtolower($code) . '-' . $dataForm->customer_id . '.mp4';

            $resource->move($basepath . $video_path, $video_name);

        }

        $data = array(
            'code' => $code,
            'title' => $dataForm->title,
            'category_id' => $dataForm->category_id,
            'description' => $dataForm->description,
            'image' => '',
            'video' => $video_name,
            'customer_id' => $dataForm->customer_id,
            'lat' => $dataForm->lat,
            'lng' => $dataForm->lng,
        );

        try {
            $ticket = TicketApi::create($data);
            if ($ticket) {
                $upload_image = new Ticket_Image;
                $upload_image->image = str_replace("\/", "/", json_encode($dataImageName));
                $upload_image->ticket_id = $ticket->id;
                $upload_image->save();
            }
            return response()->json([
                'message' => 'Keluhan Diterima',
            ]);

        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
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
    public function update(Request $request, TicketApi $ticket)
    {

        $rules = array(
            // 'email' => 'email|unique:customers,email',
            // 'code' => 'unique:customers,code',
            'title' => 'required',
            'category_id' => 'required',
            'description' => 'required',
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

        $ticket->update($request->all());

        //send notif to departement terkait
        $admin_arr = User::where('dapertement_id', $request->dapertement_id)->get();
        foreach ($admin_arr as $key => $admin) {
            $id_onesignal = $admin->_id_onesignal;
            $message = 'Keluhan Baru Diterima : ' . $request->description;
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
            'message' => 'Data Ticket update Success',
            'data' => $ticket,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(TicketApi $ticket)
    {
        try {
            $ticket_image = Ticket_Image::where('ticket_id', $ticket->id)->delete();
            $ticket->delete();
            return response()->json([
                'message' => 'Data Berhasil Di Hapus',
                'data' => $ticket_image,
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Data Masih Terkait dengan data yang lain',
                'data' => $e,
            ]);
        }
    }
}
