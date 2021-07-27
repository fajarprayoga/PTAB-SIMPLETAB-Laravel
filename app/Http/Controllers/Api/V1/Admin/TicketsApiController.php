<?php

namespace App\Http\Controllers\api\v1\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\TicketApi;
use App\Customer;
use Illuminate\Database\QueryException;
use App\Traits\TraitModel;
use Illuminate\Support\Facades\Validator;
class TicketsApiController extends Controller
{
    use TraitModel;

    public function index()
    {
        try {
            $ticket = TicketApi::orderBy('id', 'DESC')->with('customer')->with('category')->get();
            return response()->json([
                'message' => 'Data Ticket',
                'data' => $ticket
            ]);
            } catch (QueryException $ex) {
                return response()->json([
                    'message' => 'Gagal Mengambil data'
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
        $basepath=str_replace("laravel-simpletab","public_html/simpletabadmin/",\base_path());
        $dataForm = json_decode($request->form);



        if($request->file('image') && $request->file('video')){


          // image
            $resourceImage = $request->file('image');
            $nameImage = strtolower($code);
            $file_extImage = $request->file('image')->extension();
            $nameImage = str_replace(" ", "-", $nameImage);


            $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->customer_id . "." . $file_extImage;

            $resourceImage->move($basepath . $img_path, $img_name);


            // video 

            $video_path = "/videos/complaint";
            $resource = $request->file('video');
            // $filename = $resource->getClientOriginalName();
            // $file_extVideo = $request->file('video')->extension();
            $video_name = $video_path."/".strtolower($code).'-'.$dataForm->customer_id.'.mp4';

            $resource->move($basepath.$video_path,$video_name);



            $data = array(
              'code' => $code,
              'title' => $dataForm->title,
              'category_id' => $dataForm->category_id,
              'description' => $dataForm->description,
              'image' =>  $img_name,
              'video' => $video_name,
              'customer_id' => $dataForm->customer_id,
              'lat' => $dataForm->lat,
              'lng' => $dataForm->lng
            );


              try {
        
                $ticket = TicketApi::create($data);

                return response()->json([
                  'message' => "Keluhan diterima",
                  'data' => $data
                ]);

              } catch (QueryException $ex) {
                return response()->json([
                  'message' => $ex
                ]);
              }
        }else{
          return response()->json([
            'message' => 'Image atau Videdo tidak didukung'
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

      
      $rules=array(
          // 'email' => 'email|unique:customers,email',
          // 'code' => 'unique:customers,code',
          'title' => 'required',
          'category_id' => 'required',
          'description' => 'required',
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

      $ticket->update($request->all());

      return response()->json([
        'message' => 'Data Ticket update Success',
        'data' => $ticket
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
      try{
        $ticket->delete();
        return response()->json([
          'message' => 'Data Berhasil Di Hapus'
        ]);
      }
      catch(QueryException $e) {
          return response()->json([
            'message' => 'Data Masih Terkait dengan data yang lain',
            'data' => $e
          ]);
      }
    }
}
