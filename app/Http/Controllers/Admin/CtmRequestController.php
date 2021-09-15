<?php

namespace App\Http\Controllers\Admin;

use App\CtmRequest;
use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use Illuminate\Http\Request;

class CtmRequestController extends Controller
{
    use TraitModel;

    public function index()
    {
        abort_unless(\Gate::allows('ctmrequests_access'), 403);
        $ctmrequests = CtmRequest::with('customer')
            ->get();

        return view('admin.ctmrequests.index', compact('ctmrequests'));
    }

    public function create()
    {
        $last_code = $this->get_last_code('category');

        $code = acc_code_generate($last_code, 8, 3);

        abort_unless(\Gate::allows('ctmrequests_create'), 403);
        return view('admin.ctmrequests.create', compact('code'));
    }

    public function store(Request $request)
    {
        abort_unless(\Gate::allows('ctmrequests_create'), 403);
        $category = CtmRequest::create($request->all());

        return redirect()->route('admin.ctmrequests.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('ctmrequests_edit'), 403);
        $ctmrequest = CtmRequest::with('customer')->findOrFail($id);
        $img_path_old = "/gambar-test";
        $img_path = "/gambar";
        $img = 'https://ptab-vps.com/pdam'.$ctmrequest->img;
        $img = str_replace($img_path,$img_path_old,$img);
        $ctmrequest->monthyear = $ctmrequest->month."/".$ctmrequest->year;
        return view('admin.ctmrequests.edit', compact('ctmrequest','img'));
    }

    public function update(Request $request, CtmRequest $ctmrequest)
    {
        abort_unless(\Gate::allows('ctmrequests_edit'), 403);
        //update status
        $ctmrequest->status = 'approve';
        $ctmrequest->save();
        //get var
        $var['norek'] = $ctmrequest->norek;
        $var['wmmeteran'] = $ctmrequest->wmmeteran;
        $var['namastatus'] = $ctmrequest->namastatus;
        $var['opp'] = $ctmrequest->opp;
        $var['lat'] = $ctmrequest->lat;
        $var['lng'] = $ctmrequest->lng;
        $var['accuracy'] = $ctmrequest->accuracy;
        $var['operator'] = $ctmrequest->operator;
        $var['nomorpengirim'] = $ctmrequest->nomorpengirim;
        $var['statusonoff'] = $ctmrequest->statusonoff;
        $var['description'] = $ctmrequest->description;
        $var['filegambar'] = $ctmrequest->img;
        $var['filegambar1'] = $ctmrequest->img1;
        $var['datecatatf1'] = $ctmrequest->datecatatf1;
        $var['datecatatf2'] = $ctmrequest->datecatatf2;
        $var['datecatatf3'] = $ctmrequest->datecatatf3;
        $var['year'] = $ctmrequest->year;
        $var['month'] = $ctmrequest->month;

        //get prev
        $year = $var['year'];
        $month = $var['month'];
        $ctm_prev = $this->getCtmPrev($var['norek'], $month, $year);
        $var['pencatatanmeterprev'] = $ctm_prev['pencatatanmeter'];
        $var['statussmprev'] = $ctm_prev['statussm'];

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
        $img_path_old = "/gambar-test";
        $img_path = "/gambar-pindahan";
        $basepath = str_replace("laravel-simpletab", "public_html/pdam/", \base_path());
        $path_old = $basepath . $img_path_old . "/" . $year_catat . $month_catat . "/";
        $path = $basepath . $img_path . "/" . $year_catat . $month_catat . "/"; 
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $new_image_name = $var['norek'] . "_" . $var['tahunrekening'] . "_" . $month_catat . ".jpg";

        copy($path_old . $new_image_name, $path . $new_image_name);
        
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
        return redirect()->route('admin.ctmrequests.index');
    }

    public function destroy(CtmRequest $category)
    {
        abort_unless(\Gate::allows('ctmrequests_delete'), 403);

        $category->delete();

        return back();
    }

    public function massDestory(Request $request)
    {
        CtmRequest::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
