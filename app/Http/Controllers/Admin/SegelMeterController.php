<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Customer;
use App\CtmPembayaran;
use Yajra\DataTables\Facades\DataTables;

class SegelMeterController extends Controller
{
    public function index(Request $request)

    {
        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $month_next = date('n', strtotime('+1 month'));

        if ($request->ajax()) {
            //set query

            if($date_now >= $date_comp){
                $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.tahunrekening', date('Y'))
                ->where('tblpembayaran.bulanrekening','<' ,$month_next)
                ->groupBy('tblpembayaran.nomorrekening');
    
                if(request()->input('status')  != ''){
                    $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpembayaran.tahunrekening', date('Y'))
                    ->where('tblpembayaran.bulanrekening','<' ,$month_next)
                    ->having('statusnunggak', request()->input('status') )
                    ->groupBy('tblpembayaran.nomorrekening');
                }
            }else{
                $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.tahunrekening', date('Y'))
                ->where('tblpembayaran.bulanrekening','<' , $month_next)
                ->groupBy('tblpembayaran.nomorrekening');
    
                if(request()->input('status')  != ''){
                    $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpembayaran.tahunrekening', date('Y'))
                    ->where('tblpembayaran.bulanrekening','<' ,$month_next)
                    ->having('statusnunggak', request()->input('status') )
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
                return $row->statusnunggak ==1  ? '<span class="badge bg-danger">Belum Lunas</span>' : '<span class="badge bg-primary">Sudah Lunas</span>';
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
        $ctm_lock =0;
        if ($date_now > $date_comp) {
            $ctm_lock_old=0;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpembayaran.tahunrekening', date('Y'))
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->where('tblpembayaran.bulanrekening', '<', $month_next)     
                ->get();
        } else {
            $ctm_lock_old=1;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpembayaran.tahunrekening', date('Y'))
                ->where('tblpembayaran.bulanrekening', '<', $month_next)                    
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        }

        
        foreach ($ctm as $key => $item) {
            $m3 = $item->bulanini - $item->bulanlalu;
            $sisa = $item->wajibdibayar - $item->sudahdibayar;
            $tagihan =$tagihan + $sisa;


            if($month_now==$item->bulanrekening && $ctm_lock_old==1){
                $ctm_lock = 1;
            }

            if($sisa>0 && $ctm_lock==0){
                $tunggakan =$tunggakan + 1;
            }

            $dataPembayaran[$key] = [
                // 'no' => $key +1,
                'norekening' => $item->nomorrekening,
                'periode'=> $item->tahunrekening . '-' . $item->bulanrekening,
                'tanggal' => $item->tglbayarterakhir,
                'm3' => $m3,
                'wajibdibayar' => $item->wajibdibayar,
                'sudahbayar' => $item->sudahdibayar,
                'denda' => $item->denda,
                'sisa' => $sisa,
            ];
        }

        if($tunggakan>0 && $tunggakan<2){
            $denda = 10000;
            $total = $tagihan +$denda;
            $denda = $denda;
        }
        if($tunggakan>1 && $tunggakan<4){
            $denda = 50000;
            $total = $tagihan + $denda;
            $denda = $denda;
        }
        if($tunggakan>3){
            $denda = 'SSB (Sanksi Denda Setara Sambungan Baru)';
            $total = $tagihan;
        }

        $recap = [
            'tagihan' => $tagihan,
            'denda'=> $denda,
            'total'=> $total,
            'tunggakan'=> $tunggakan
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
        $ctm_lock =0;
        if ($date_now > $date_comp) {
            $ctm_lock_old=0;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpembayaran.tahunrekening', date('Y'))
                ->where('tblpembayaran.statuslunas', '=' , 0)
                ->where('tblpembayaran.bulanrekening', '<', $month_next)  
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        } else {
            $ctm_lock_old=1;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpembayaran.tahunrekening', date('Y'))
                ->where('tblpembayaran.bulanrekening', '<', $month_next)   
                ->where('tblpembayaran.statuslunas','=' , 0)                 
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        }

        
        foreach ($ctm as $key => $item) {
            $m3 = $item->bulanini - $item->bulanlalu;
            $sisa = $item->wajibdibayar - $item->sudahdibayar;
            $tagihan =$tagihan + $sisa;


            if($month_now==$item->bulanrekening && $ctm_lock_old==1){
                $ctm_lock = 1;
            }

            if($sisa>0 && $ctm_lock==0){
                $tunggakan =$tunggakan + 1;
            }

            $dataPembayaran[$key] = [
                // 'no' => $key +1,
                'norekening' => $item->nomorrekening,
                'periode'=> $item->tahunrekening . '-' . $item->bulanrekening,
                'tanggal' => $item->tglbayarterakhir,
                'm3' => $m3,
                'wajibdibayar' => $item->wajibdibayar,
                'sudahbayar' => $item->sudahdibayar,
                'denda' => $item->denda,
                'sisa' => $sisa,
            ];
        }

        if($tunggakan>0 && $tunggakan<2){
            $denda = 10000;
            $total = $tagihan +$denda;
            $denda = $denda;
        }
        if($tunggakan>1 && $tunggakan<4){
            $denda = 50000;
            $total = $tagihan + $denda;
            $denda = $denda;
        }
        if($tunggakan>3){
            $denda = 'SSB (Sanksi Denda Setara Sambungan Baru)';
            $total = $tagihan;
        }
        

        $recap = [
            'tagihan' => $tagihan,
            'denda'=> $denda,
            'total'=> $total,
            'tunggakan'=> $tunggakan
        ];

        return view('admin.segelmeter.spp', compact('customer', 'dataPembayaran', 'recap'));
    }
}
