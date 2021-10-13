<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PdfUploadController extends Controller
{

    public function fileUpload()
    {
        return view('admin.pdf.PdfUpload');
    }

    public function fileUploadPost(Request $request)
    {
       
        $request->validate([
            'file' => 'required|mimes:pdf',
        ]);

        if ($request->file('file')) {
  
        $img_path = "/pdf";
        $basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $resourceImage = $request->file;
        $nameImage = time();
        $file_extImage = $request->file->extension();
        $nameImage = str_replace(" ", "-", $nameImage);
        $img_name = $img_path . "/" . $nameImage . "." . $file_extImage;

        $resourceImage->move($basepath . $img_path, $img_name);
        
        $success='Upload File Berhasil';
        $pdf='https://simpletabadmin.ptab-vps.com/pdf/'.$nameImage.".".$file_extImage;

        return back()
            ->with(compact('success','pdf'));//);
        }else{
            return back()
            ->with('failed');
        }
   
    }
   
}
