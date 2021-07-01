<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiTicketRequest;
use Illuminate\Http\Request;
use App\Ticket;
use Illuminate\Database\QueryException;
use App\Traits\TraitModel;
use Illuminate\Support\Facades\Validator;

class TicketsApiController extends Controller
{
  use TraitModel;

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
              'title' => $dataForm->title,
              'category_id' => $dataForm->category_id,
              'description' => $dataForm->description,
              'image' =>  $img_name,
              'video' => $video_name,
              'customer_id' => $dataForm->customer_id
            );


              try {
        
                $ticket = Ticket::create($data);

                return response()->json([
                  'message' => "Keluhan diterima"
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
}
