<?php

namespace App\Http\Controllers\api\v1\dapertement;

use App\ActionApi;
use App\Http\Controllers\Controller;
use App\TicketApi;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Berkayk\OneSignal\OneSignalClient;
use OneSignal;
use App\User;

class SubDapertementsApiController extends Controller
{
    public function actionListSubDapertement($action_id)
    {
        try {
            $action = ActionApi::findOrFail($action_id)->first();
            return response()->json([
                'message' => 'Status di ubah ',
                'data' => $action,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Data Error ',
                'data' => $ex,
            ]);
        }
    }
    public function Edit(Request $request)
    {
        try {
            // ambil data dari request simpan di dataForm

            $dataForm = json_decode($request->form);
            // rules
            // $rules=array(
            //     'action_id' => 'required',
            //     'staff_id' => 'required',
            //     'status' => 'required'
            // );

            // $validator=\Validator::make($dataForm,$rules);
            // if($validator->fails())
            // {
            //     $messages=$validator->messages();
            //     $errors=$messages->all();
            //     return response()->json([
            //         'message' => $errors,
            //         'data' => $request->all()
            //     ]);
            // }

            // data action
            $action = ActionApi::where('id', $dataForm->action_id)->with('ticket')->with('staff')->first();

            // image yang lama disimpan
            $actionImage = json_decode($action->image);
            $img_path = "/images/action";
            $basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());

            // cek status dan upload gambar
            for ($i = 1; $i <= 2; $i++) {
                if ($request->file('image' . $i)) {
                    $resourceImage = $request->file('image' . $i);
                    $nameImage = strtolower($action->id);
                    $file_extImage = $request->file('image' . $i)->extension();
                    $nameImage = str_replace(" ", "-", $nameImage);

                    $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->subdapertement_id . $i . "." . $file_extImage;

                    $resourceImage->move($basepath . $img_path, $img_name);

                    $dataImageName[] = $img_name;
                } else {
                    $responseImage = 'Image tidak di dukung';
                    break;
                }
            }

            // $dataForm['image'] =  str_replace("\/", "/", json_encode($dataImageName));

            if ($resourceImage) {
                if ($action) {
                    $cek = $action->staff()->updateExistingPivot($dataForm->staff_id, ['status' => $dataForm->status]);
                }

                if ($cek) {
                    $action = ActionApi::where('id', $dataForm->action_id)->with('ticket')->with('staff')->first();
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

                    $dataNewAction = array(
                        'status' => $statusAction,
                        'image' => str_replace("\/", "/", json_encode($dataImageName)),
                        'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $dateNow,
                        'memo' => $dataForm->memo,
                    );

                    $action->update($dataNewAction);
                    //update ticket status
                    $ticket = TicketApi::find($action->ticket_id);
                    $ticket->status = $statusAction;
                    $ticket->save();

                    $admin = User::where('dapertement_id', 1)->first();
        $id_onesignal = $admin->_id_onesignal;
        $message = 'Status Diupdate : '.$action->ticket->description;
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
                        $message = 'Status Diupdate : ' . $action->ticket->description;
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
                    return response()->json([
                        'message' => 'Status Gagal Di Ubah',
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'Image Tidak Di Simpan',
                ]);
            }

        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal tambah staff ',
                'data' => $ex,
            ]);
        }
    }
}
