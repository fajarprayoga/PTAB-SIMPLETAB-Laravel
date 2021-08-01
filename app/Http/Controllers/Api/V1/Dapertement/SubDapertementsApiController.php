<?php

namespace App\Http\Controllers\api\v1\dapertement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ActionApi;
class SubDapertementsApiController extends Controller
{
    public function Edit(Request $request)
    {


        

        $action = ActionApi::findOrFail($request->action_id);

        $actionImage = json_decode($action->image);
        $img_path = "/images/action";
        $basepath=str_replace("laravel-simpletab","public_html/simpletabadmin/",\base_path());


        $dataForm = json_decode($request->form);

        $data = array(
            'code' => $code,
            'title' => $dataForm->title,
            'category_id' => $dataForm->category_id,
            'description' => $dataForm->description,
            'video' => $video_name,
            'customer_id' => $dataForm->customer_id,
            'lat' => $dataForm->lat,
            'lng' => $dataForm->lng
        );
        if($dataForm->status == 'active'){
            for ($i=1; $i <= 2 ; $i++) { 
                if($request->file('image'.$i)){
                  $resourceImage = $request->file('image'.$i);
                  $nameImage = strtolower($code);
                  $file_extImage = $request->file('image'.$i)->extension();
                  $nameImage = str_replace(" ", "-", $nameImage);
      
      
                  $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->customer_id . $i. "." . $file_extImage;
      
                  $resourceImage->move($basepath . $img_path, $img_name);
      
                  $dataImageName[] = $img_name;
                }else{
                  $responseImage ='Image tidak di dukung';
                  break;
                }
            }

            $data['image'] =  str_replace("\/", "/", json_encode($dataImageName));
        }else if($dataForm->status=='close'){
            for ($i=1; $i <= 2 ; $i++) { 
                $index = $i--;
                if($request->file('image'.$i)){
                    $resourceImage = $request->file('image'.$i);
                    $resourceImage->move($basepath . $img_path, $actionImage[$index]);
                }else{
                    $responseImage ='Image tidak di dukung';
                    break;
                }
            }
        }else{
            return response()->json([
                'message' => 'Status Aksi masih pending'
            ]);
        }

        if($responseImage != ''){
          return response()->json([
            'message' => $responseImage
          ]);
        }
    }
}
