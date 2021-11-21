<?php

namespace App\Http\Controllers\Admin;

use App\CtmPelanggan;
use App\CtmPembayaran;
use App\Customer;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Lock;
use App\Traits\TraitModel;
use App\AreaStaff;

class SegelMeterController extends Controller
{
    
    use TraitModel;

    public function deligate()
    {
        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $last_4_month = date("Y-n-d", strtotime ( '-4 month' , strtotime ( date('Y-m-01') ) )) ;
        
        if ($date_now > $date_comp) {
            $qry = CtmPelanggan::selectRaw('tblpelanggan.nomorrekening,tblpelanggan.idareal,tblwilayah.group_unit,tblpembayaran.bulanrekening, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')    
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')                
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->having('jumlahtunggakan', 2)
                ->groupBy('tblpembayaran.nomorrekening')
                ->get();
        } else {
            $qry = CtmPelanggan::selectRaw('tblpelanggan.nomorrekening,tblpelanggan.idareal,tblwilayah.group_unit,tblpembayaran.bulanrekening, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')                
                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')    
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')                
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->having('jumlahtunggakan', 2)
                ->groupBy('tblpembayaran.nomorrekening')
                ->get();
        }
        foreach ($qry as $index => $qry_row) {
            //get sub departement
            if($qry_row->group_unit==2){
                $subdapertement_id=13;
            }else if($qry_row->group_unit==3){
                $subdapertement_id=20;
            }else if($qry_row->group_unit==4){
                $subdapertement_id=18;
            }else if($qry_row->group_unit==5){
                $subdapertement_id=16;
            }else{
                $subdapertement_id=10;
            }
            //get staff
            $staff_id=0;
            $area_staff = AreaStaff::where('area_id', $qry_row->idareal)->first();
            if($area_staff !=null){
                $staff_id = $area_staff->staff_id;
            }
            //get scb
            $arr['subdapertement_id'] = $subdapertement_id;
            $arr['month'] = date("m");
            $arr['year'] = date("Y");
            $last_scb = $this->get_last_code('scb-lock', $arr);
            $scb = acc_code_generate($last_scb, 18, 12, 'Y');    
            if (Lock::where('customer_id', $qry_row->nomorrekening)->first() === null || Lock::where('customer_id', $qry_row->nomorrekening)->where('status', '!=', 'close')->first() === null) {
            // echo $scb."-".$qry_row->nomorrekening."</br>";
            $lock = Lock::create(['code'=>$scb,'customer_id'=>$qry_row->nomorrekening,'subdapertement_id'=>$subdapertement_id,'description'=>'']);
            if($staff_id>0){
                $lock->staff()->attach($staff_id);
                }
            }                 
        }    
        return back()->withErrors(['Teruskan Serentak Telah Selesai Diproses.']);    
    }

    public function deligateBAK(Request $request)
    {
        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $last_4_month = date("Y-n-d", strtotime ( '-4 month' , strtotime ( date('Y-m-01') ) )) ;
        //56530,5632,10011

        // $qry = CtmPelanggan::selectRaw('tblpelanggan.nomorrekening,tblpelanggan.namapelanggan,tblpembayaran.tahunrekening,tblpembayaran.bulanrekening')
        //     ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
        //     ->where('tblpelanggan.nomorrekening', 56530)
        //     ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")') , '<' , date('2021-9-01'))
        //     ->get();

        if ($date_now > $date_comp) {
            $qry = CtmPelanggan::selectRaw('tblpelanggan.nomorrekening,tblpelanggan.namapelanggan,tblwilayah.group_unit,tblpembayaran.bulanrekening, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')    
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')                
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->having('jumlahtunggakan', 2)
                ->groupBy('tblpembayaran.nomorrekening')
                ->skip(0)->take(10)->get();
        } else {
            $qry = CtmPelanggan::selectRaw('tblpelanggan.nomorrekening,tblpelanggan.namapelanggan,tblwilayah.group_unit,tblpembayaran.bulanrekening, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')                
                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')    
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')                
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->having('jumlahtunggakan', 2)
                ->groupBy('tblpembayaran.nomorrekening')
                ->skip(0)->take(10)->get();
        }
        $data_insert=array();
        for($i=0;$i<count($qry);$i++){
            if($qry[$i]->group_unit==2){
                $subdapertement_id=13;
            }else if($qry[$i]->group_unit==3){
                $subdapertement_id=20;
            }else if($qry[$i]->group_unit==4){
                $subdapertement_id=18;
            }else if($qry[$i]->group_unit==5){
                $subdapertement_id=16;
            }else{
                $subdapertement_id=10;
            }
            $arr['subdapertement_id'] = $subdapertement_id;
            $arr['month'] = date("m");
            $arr['year'] = date("Y");
            $last_scb = $this->get_last_code('scb-lock', $arr);
            $scb = acc_code_generate($last_scb, 16, 12, 'Y');    
            $data_insert[$i] = ['code'=>$scb,'customer_id'=>$qry[$i]->nomorrekening,'subdapertement_id'=>$subdapertement_id,'description'=>''];            
        }
        return $this->deligateStore($data_insert);
        // $lock = Lock::insert($data_insert);
        // return count($qry);
        // foreach ($qry as $key => $qry_row) {
        //     echo $qry_row->nomorrekening."</br>";
        // }
        // foreach ($qry as $index => $qry_row) {            
        //     if($qry_row->group_unit==2){
        //         $subdapertement_id=13;
        //     }else if($qry_row->group_unit==3){
        //         $subdapertement_id=20;
        //     }else if($qry_row->group_unit==4){
        //         $subdapertement_id=18;
        //     }else if($qry_row->group_unit==5){
        //         $subdapertement_id=16;
        //     }else{
        //         $subdapertement_id=10;
        //     }
        //     // $arr['subdapertement_id'] = $subdapertement_id;
        //     // $arr['month'] = date("m");
        //     // $arr['year'] = date("Y");
        //     // $last_scb = $this->get_last_code('scb-lock', $arr);
        //     // $scb = acc_code_generate($last_scb, 16, 12, 'Y');    
        //     if (Lock::where('customer_id', $qry_row->nomorrekening)->first() === null) {
        //     // echo $scb."-".$qry_row->nomorrekening."</br>";
        //     $lock = Lock::create(['code'=>$index,'customer_id'=>$qry_row->nomorrekening,'subdapertement_id'=>$subdapertement_id,'description'=>'']);
        //     }        
        // }
    }

    public function index(Request $request)
    {
        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $last_4_month = date("Y-n-d", strtotime ( '-4 month' , strtotime ( date('Y-m-01') ) )) ;

        if ($request->ajax()) {
            //set query

            if ($date_now > $date_comp) {
                $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpelanggan.status', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->having('jumlahtunggakan', 2)
                    ->groupBy('tblpembayaran.nomorrekening');

                if (request()->input('status') != '') {
                    $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                        ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                        ->where('tblpelanggan.status', 1)
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                        ->having('jumlahtunggakan', 2)
                        ->groupBy('tblpembayaran.nomorrekening');
                }
            } else {
                $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpelanggan.status', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->having('jumlahtunggakan', 2)
                    ->groupBy('tblpembayaran.nomorrekening');

                if (request()->input('status') != '') {
                    $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                        ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                        ->where('tblpelanggan.status', 1)
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                        ->having('jumlahtunggakan', 2)
                        ->groupBy('tblpembayaran.nomorrekening');
                }
            }

            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'segel_show';
                $editGate = '';
                $deleteGate = '';
                $crudRoutePart = 'segelmeter';
                $lockGate = $row->statusnunggak;
                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'lockGate',
                    'crudRoutePart',
                    'row'
                ));
            });
            $table->editColumn('nomorrekening', function ($row) {
                return $row->nomorrekening ? $row->nomorrekening : "";
            });

            $table->editColumn('namapelanggan', function ($row) {
                return $row->namapelanggan ? $row->namapelanggan : "";
            });

            $table->editColumn('alamat', function ($row) {
                return $row->alamat ? $row->alamat : "";
            });

            $table->editColumn('jumlahtunggakan', function ($row) {
                return $row->jumlahtunggakan ? $row->jumlahtunggakan : 0;
            });

            $table->editColumn('statusnunggak', function ($row) {
                if($row->jumlahtunggakan == 0){
                    return '<span class="badge bg-success">Lunas</span>';
                }else if($row->jumlahtunggakan == 1){
                    return '<span class="badge bg-warning">Awas</span>';
                }else{
                    return '<span class="badge bg-danger">Tunggak</span>';
                }
            });

            $table->rawColumns(['actions', 'placeholder', 'statusnunggak']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        return view('admin.segelmeter.index');

    }

    public function show($id)
    {
        $customer = Customer::where('nomorrekening', $id)
            ->first();
        $customer->year = date('Y');
        // dd($ctm);

        // ctm pay
        $date_now = date("Y-m-d");
        $date_comp = date("Y-m") . "-20";
        $month_next = date('n', strtotime('+1 month'));
        $month_now = ($month_next - 1);
        $tunggakan = 0;
        $tagihan = 0;
        $denda = 0;
        $total = 0;
        $ctm_lock = 0;
        $last_4_month = date("Y-n-d", strtotime ( '-4 month' , strtotime ( date('Y-m-01') ) )) ;
        if ($date_now > $date_comp) {
            $ctm_lock_old = 0;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        } else {
            $ctm_lock_old = 1;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        }

        foreach ($ctm as $key => $item) {
            $m3 = $item->bulanini - $item->bulanlalu;
            $sisa = $item->wajibdibayar - $item->sudahdibayar;
            $tagihan = $tagihan + $sisa;

            if ($month_now == $item->bulanrekening && $ctm_lock_old == 1) {
                $ctm_lock = 1;
            }

            if ($sisa > 0 && $ctm_lock == 0) {
                $tunggakan = $tunggakan + 1;
            }

            $dataPembayaran[$key] = [
                // 'no' => $key +1,
                'norekening' => $item->nomorrekening,
                'periode' => $item->tahunrekening . '-' . $item->bulanrekening,
                'tanggal' => $item->tglbayarterakhir,
                'm3' => $m3,
                'wajibdibayar' => $item->wajibdibayar,
                'sudahbayar' => $item->sudahdibayar,
                'denda' => $item->denda,
                'sisa' => $sisa,
            ];
        }

        if ($tunggakan > 0 && $tunggakan < 2) {
            $denda = 10000;
            $total = $tagihan + $denda;
            $denda = $denda;
        }
        if ($tunggakan > 1 && $tunggakan < 4) {
            $denda = 50000;
            $total = $tagihan + $denda;
            $denda = $denda;
        }
        if ($tunggakan > 3) {
            $denda = 'SSB (Sanksi Denda Setara Sambungan Baru)';
            $total = $tagihan;
        }

        $recap = [
            'tagihan' => $tagihan,
            'denda' => $denda,
            'total' => $total,
            'tunggakan' => $tunggakan,
        ];

        return view('admin.segelmeter.show', compact('customer', 'dataPembayaran', 'recap'));
    }

    public function sppPrint($id)
    {

        $customer = Customer::where('nomorrekening', $id)
            ->first();
        $customer->year = date('Y');
        // dd($ctm);

        // ctm pay
        $date_now = date("Y-m-d");
        $date_comp = date("Y-m") . "-20";
        $month_next = date('n', strtotime('+1 month'));
        $month_now = ($month_next - 1);
        $tunggakan = 0;
        $tagihan = 0;
        $denda = 0;
        $total = 0;
        $ctm_lock = 0;
        if ($date_now > $date_comp) {
            $ctm_lock_old = 0;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpembayaran.statuslunas', '=', 0)
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        } else {
            $ctm_lock_old = 1;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpembayaran.statuslunas', '=', 0)
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        }

        foreach ($ctm as $key => $item) {
            $m3 = $item->bulanini - $item->bulanlalu;
            $sisa = $item->wajibdibayar - $item->sudahdibayar;
            $tagihan = $tagihan + $sisa;

            if ($month_now == $item->bulanrekening && $ctm_lock_old == 1) {
                $ctm_lock = 1;
            }

            if ($sisa > 0 && $ctm_lock == 0) {
                $tunggakan = $tunggakan + 1;
            }

            $dataPembayaran[$key] = [
                // 'no' => $key +1,
                'norekening' => $item->nomorrekening,
                'periode' => $item->tahunrekening . '-' . $item->bulanrekening,
                'tanggal' => $item->tglbayarterakhir,
                'm3' => $m3,
                'wajibdibayar' => $item->wajibdibayar,
                'sudahbayar' => $item->sudahdibayar,
                'denda' => $item->denda,
                'sisa' => $sisa,
            ];
        }

        if ($tunggakan > 0 && $tunggakan < 2) {
            $denda = 10000;
            $total = $tagihan + $denda;
            $denda = $denda;
        }
        if ($tunggakan > 1 && $tunggakan < 4) {
            $denda = 50000;
            $total = $tagihan + $denda;
            $denda = $denda;
        }
        if ($tunggakan > 3) {
            $denda = 'SSB (Sanksi Denda Setara Sambungan Baru)';
            $total = $tagihan;
        }

        $recap = [
            'tagihan' => $tagihan,
            'denda' => $denda,
            'total' => $total,
            'tunggakan' => $tunggakan,
        ];

        return view('admin.segelmeter.spp', compact('customer', 'dataPembayaran', 'recap'));
    }
}
