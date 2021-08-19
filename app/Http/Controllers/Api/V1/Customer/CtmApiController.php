<?php

namespace App\Http\Controllers\api\v1\customer;

use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use Illuminate\Http\Request;

class CtmApiController extends Controller
{
    use TraitModel;

    public function ctmPrev(Request $request)
    {
        //$data = $request->all();
        $data = json_decode($request->form);
        $var = [];
        foreach ($data as $key => $dat) {
            $var[$key] = $dat;
        }
        $var['datecatatf1']=date("Y-m-d");//2021-08-16
        $var['datecatatf2']=date("F d, Y, G:i:s a");//August 16, 2021, 15:23:37 pm
        $var['datecatatf3']=date("Y-m-d G:i:s");//2021-08-16 15:23:37

        //get month year rekening
        $datecatatf1_arr = explode("-", $var['datecatatf1']);
        $month_catat = $datecatatf1_arr[1];
        $year_catat = $datecatatf1_arr[0];
        $month_bayar = date('m', strtotime($datecatatf1_arr[0] . '-' . $datecatatf1_arr[1] . ' + 1 month'));
        $year_bayar = date('Y', strtotime($datecatatf1_arr[0] . '-' . $datecatatf1_arr[1] . ' + 1 month'));
        //additional var
        $var['nomorrekening'] = $var['norek'];
        $var['pencatatanmeter'] = $var['wmmeteran'];
        $var['bulanrekening'] = (int) $month_catat;
        $var['tahunrekening'] = $year_catat;
        $var['bulanbayar'] = (int) $month_bayar;
        $var['tahunbayar'] = $year_bayar;
        $var['namastatus'] = $var['namastatus'];
        $var['bulanini'] = $var['wmmeteran'];
        $var['bulanlalu'] = $var['pencatatanmeterprev'];
        $var['statusonoff'] = $var['statusonoff'];
        //img path
        $img_path = "/gambar-test";
        $basepath = str_replace("laravel-simpletab", "public_html/pdam/", \base_path());
        $path = $basepath . $img_path . "/" . $year_catat . $month_catat . "/"; //path nanti bisa dirubah disini mode 755
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $new_image_name = $var['norek'] . "_" . $var['tahunrekening'] . "_" . $month_catat . ".jpg"; //nama image dibuat sendiri
        //move_uploaded_file($_FILES['file']['tmp_name'], $path . $new_image_name);
        $img_name = $img_path . "/" . $new_image_name;
        $resourceImage = $request->file('image');
        $resourceImage->move($path, $img_name);
        $path_img = "/" . "gambar/" . $year_catat . $month_catat . "/";
        $path_img1 = "D:/MyAMP/www/" . "gambar/" . $year_catat . $month_catat . "/";
        $var['filegambar'] = $path_img . $new_image_name;
        $var['filegambar1'] = $path_img1 . $new_image_name;

        //get meterawal
        $getCtmMeterPrev = $this->getCtmMeterPrev($var['norek'], $var['bulanrekening'], $var['tahunrekening']);
        $meterawal = $var['pencatatanmeterprev'];

        if ((int) $var['namastatus'] == 111) {
            $meterawal = $getCtmMeterPrev['pencatatanmeter'];
        }

        //set pemakaianair
        $var['pemakaianair'] = max(0, ($var['pencatatanmeter'] - $meterawal));
        $var['meterawal'] = $meterawal;
        //insert data into gambarmeter
        $var['idgambar'] = $this->insupdCtmGambarmeter($var);
        $this->insupdCtmGambarmetersms($var);
        $this->insupdCtmMapKunjungan($var);
        $this->insupdCtmPemakaianair($var);
        $this->insupdCtmStatussmpelanggan($var);
        $this->insupdCtmStatusonoff($var);
        //insert into tblpembayaran
        $this->insupdCtmPembayaran($var);

        // $var['nomorrekening']=2;
        // $var['pemakaianair'] =63;
        // $tblpelanggan_arr = $this->getCtmJenispelanggan($var['nomorrekening']);
        // //hitung rp-tagihan
        // $tblpelanggan_arr['pemakaianair'] = $var['pemakaianair'];
        // $data = $this->getCtmTagihan($tblpelanggan_arr);

        // $var['nomorrekening']='60892';
        // $var['bulanrekening']='9';
        // $var['tahunrekening']='2021';
        // $var['statusonoff']='off';
        // $var['_synced']='0';

        // $data = $this->insupdCtmStatusonoff($var);

        // $var['bulanrekening']='9';
        // $var['tahunrekening']='2021';
        // $var['nomorrekening']='9997';
        // $var['namastatus']='114';
        // $var['operator']='EKA';
        // $var['_synced']='0';

        // $data = $this->insupdCtmStatussmpelanggan($var);

        // $var['bulanrekening']='8';
        // $var['pencatatanmeter']='6917';
        // $var['pemakaianair']='30';
        // $var['nomorrekening']='1';
        // $var['tahunrekening']='2022';
        // $var['datecatatf1']='2021-08-06';
        // $var['operator']='Sumardhana';
        // $var['_synced']='0';

        // $data = $this->insupdCtmPemakaianair($var);

        // $var['bulanrekening']='9';
        // $var['tahunrekening']='2021';
        // $var['nomorrekening']='38563';
        // $var['lat']='-8.5570138';
        // $var['lng']='115.10578';
        // $var['datecatatf3']='2021-08-19 10:54:19';
        // $var['accuracy']='2001';
        // $var['_synced']='0';

        // $data = $this->insupdCtmMapKunjungan($var);

        // $var['nomorpengirim']='+6282235454214';
        // $var['bulanrekening']='9';
        // $var['tahunrekening']='2021';
        // $var['datecatatf1']='2021-08-31';
        // $var['nomorrekening']='38563';
        // $var['pencatatanmeter']='2209';
        // $var['idgambar']='4107901';
        // $var['_synced']='0';

        // $data = $this->insupdCtmGambarmetersms($var);

        // $var['nomorpengirim']='+6282235454214';
        // $var['bulanrekening']='9';
        // $var['tahunrekening']='2021';
        // $var['datecatatf1']='2021-09-19';
        // $var['filegambar']='/gambar/202108/38563_2021_08.jpg';
        // $var['operator']='EKA';
        // $var['datecatatf2']='July 19, 2021, 10:54:19 am';
        // $var['filegambar1']='D:/MyAMP/www/gambar/202109/38563_2021_09.jpg';
        // $var['_synced']='0';

        // $data = $this->insupdCtmGambarmeter($var);

        $nomorrekening = '1';
        $month = '07';
        $year = '2021';
        // $data=$this->getCtmPrev($nomorrekening, $month, $year);
        // $data=$this->getCtmAvg($nomorrekening, $month, $year);
        // $data=$this->getCtmMeterPrev($nomorrekening, $month, $year);
        return response()->json([
            'message' => 'Berhasil',
            'data' => $data,
        ]);
    }

}
