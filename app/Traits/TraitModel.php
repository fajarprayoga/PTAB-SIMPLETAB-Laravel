<?php

namespace App\Traits;

use App\Action;
use App\Category;
use App\Customer;
use App\Dapertement;
use App\CtmGambarmeter;
use App\CtmGambarmetersms;
use App\CtmMapKunjungan;
use App\Staff;
use App\CtmStatusonoff;
use App\Subdapertement;
use App\CtmPemakaianAir;
use App\CtmStatussmPelanggan;
use App\CtmPembayaran;
use App\Ticket;
use DB;
use Illuminate\Database\QueryException;

trait TraitModel
{
    public function getCtmMeterPrev($nomorrekening, $month, $year)
    {
        $month = (int) $month;
        $month_prev = (int) date('m', strtotime($year . '-' . $month . ' -1 month', time()));
        $year_prev = date('Y', strtotime($year . '-' . $month . ' -1 month', time()));
        $return_obj = array();
        $return_obj['pencatatanmeter'] = 0;
        $return_obj['pemakaianair'] = 0;

        $tblpemakaianair_fetch = CtmPemakaianAir::select('pemakaianair' . $month_prev . ' AS pemakaianair', 'pencatatanmeter' . $month_prev . ' AS pencatatanmeter')
            ->where('tahunrekening', $year_prev)
            ->where('nomorrekening', $nomorrekening)
            ->get();

        $return_out = "";

        foreach ($tblpemakaianair_fetch as $key => $value) {
            $return_obj['pencatatanmeter'] = $value->pencatatanmeter;
            $return_obj['pemakaianair'] = $value->pemakaianair;
        }
        return $return_obj;
    }

    public function insupdCtmGambarmeter($var)
    {
        $arrCol = array("nomorpengirim", "bulanrekening", "tahunrekening", "tanggal", "filegambar", "operator", "infowaktu", "filegambar1", "_synced");
        $arrVal = array($var['nomorpengirim'], $var['bulanrekening'], $var['tahunrekening'], $var['datecatatf1'], $var['filegambar'], $var['operator'], $var['datecatatf2'], $var['filegambar1'], "0");

        $arrQry = array();
        foreach ($arrCol as $key => $value) {
            $arrQry[$value] = $arrVal[$key];
        }
        $arrUnique = array();
        $arrUnique['bulanrekening'] = $var['bulanrekening'];
        $arrUnique['tahunrekening'] = $var['tahunrekening'];
        $arrUnique['filegambar'] = $var['filegambar'];

        if ($gambarmeter = CtmGambarmeter::updateOrCreate($arrUnique, $arrQry)) {
            $idgambar = $gambarmeter->idgambar;
            return $idgambar;
        } else {
            return 0;
        }

    }

    public function insupdCtmGambarmetersms($var)
    {
        $arrCol = array("nomorpengirim", "bulanrekening", "tahunrekening", "tanggal", "nomorrekening", "pencatatanmeter", "idgambar", "_synced");
        $arrVal = array($var['nomorpengirim'], $var['bulanrekening'], $var['tahunrekening'], $var['datecatatf1'], $var['nomorrekening'], $var['pencatatanmeter'], $var['idgambar'], "0");

        $arrQry = array();
        foreach ($arrCol as $key => $value) {
            $arrQry[$value] = $arrVal[$key];
        }
        $arrUnique = array();
        $arrUnique['bulanrekening'] = $var['bulanrekening'];
        $arrUnique['tahunrekening'] = $var['tahunrekening'];
        $arrUnique['nomorrekening'] = $var['nomorrekening'];

        if ($gambarmetersms = CtmGambarmetersms::updateOrCreate($arrUnique, $arrQry)) {
            return true;
        } else {
            return false;
        }

    }

    public function insupdCtmMapKunjungan($var)
    {
        $arrCol = array("bulan", "tahun", "nomorrekening", "lat", "lng", "time", "accuracy", "statuskunjungan", "_synced");
        $arrVal = array($var['bulanrekening'], $var['tahunrekening'], $var['nomorrekening'], $var['lat'], $var['lng'], $var['datecatatf3'], $var['accuracy'], "1", "0");

        $arrQry = array();
        foreach ($arrCol as $key => $value) {
            $arrQry[$value] = $arrVal[$key];
        }
        $arrUnique = array();
        $arrUnique['bulan'] = $var['bulanrekening'];
        $arrUnique['tahun'] = $var['tahunrekening'];
        $arrUnique['nomorrekening'] = $var['nomorrekening'];

        if ($map_kunjungan = CtmMapKunjungan::updateOrCreate($arrUnique, $arrQry)) {
            return true;
        } else {
            return false;
        }

    }

    public function insupdCtmPemakaianair($var)
    {
        $pemakaianair = CtmPemakaianair::where('tahunrekening', '=', $var['tahunrekening'])
            ->where('nomorrekening', '=', $var['nomorrekening'])
            ->first();
        if ($pemakaianair === null) {
            $result = DB::connection('mysql2')->table('tblpemakaianair')->insert([
                'pencatatanmeter' . $var['bulanrekening'] => $var['pencatatanmeter'],
                'pemakaianair' . $var['bulanrekening'] => $var['pemakaianair'],
                'nomorrekening' => $var['nomorrekening'],
                'tahunrekening' => $var['tahunrekening'],
                'tglupdate' => $var['datecatatf1'],
                'operator' => $var['operator'],
                '_synced' => 0,
            ]);
        } else {
            $result = DB::connection('mysql2')->table('tblpemakaianair')
                ->where('tahunrekening', '=', $var['tahunrekening'])
                ->where('nomorrekening', '=', $var['nomorrekening'])
                ->update([
                    'pencatatanmeter' . $var['bulanrekening'] => $var['pencatatanmeter'],
                    'pemakaianair' . $var['bulanrekening'] => $var['pemakaianair'],
                    'tglupdate' => $var['datecatatf1'],
                    'operator' => $var['operator'],
                    '_synced' => 0,
                ]);
        }
        return true;
    }

    public function insupdCtmPsm($var)
    {
        // $arrCol = array("nomorrekening", "bulan", "tahun", "alasan", "tanggalsm", "operator");
        // $arrVal = array("'" . $var['nomorrekening'] . "'", "'" . $var['bulanrekening'] . "'", "'" . $var['tahunrekening'] . "'", "'PSM baru'", "'" . $var['datecatatf3'] . "'", "'" . $var['operator'] . "'");

        // $arrQry = array();
        // foreach ($arrCol as $key => $value) {
        //     $arrQry[$value] = $arrVal[$key];
        // }
        // $arrUnique = array();
        // $arrUnique['bulan'] = $var['bulanrekening'];
        // $arrUnique['tahun'] = $var['tahunrekening'];
        // $arrUnique['nomorrekening'] = $var['nomorrekening'];

        // if ($pemakaianair = Psm::updateOrCreate($arrUnique, $arrQry)) {
        //     return true;
        // } else {
        //     return false;
        // }
        return false;
    }

    public function insupdCtmStatussmpelanggan($var)
    {
        $tblstatussmpelanggan = CtmStatussmPelanggan::where('bulan', '=', $var['bulanrekening'])
            ->where('tahun', '=', $var['tahunrekening'])
            ->where('nomorrekening', '=', $var['nomorrekening'])
            ->first();
        if ($tblstatussmpelanggan === null) {
            $result = DB::connection('mysql2')->table('tblstatussmpelanggan')->insert([
                'bulan' => $var['bulanrekening'],
                'tahun' => $var['tahunrekening'],
                'nomorrekening' => $var['nomorrekening'],
                'statussm' => $var['namastatus'],
                'operator' => $var['operator'],
                '_synced' => 0,
            ]);
        } else {
            $result = DB::connection('mysql2')->table('tblstatussmpelanggan')
                ->where('bulan', '=', $var['bulanrekening'])
                ->where('tahun', '=', $var['tahunrekening'])
                ->where('nomorrekening', '=', $var['nomorrekening'])
                ->update([
                    'statussm' => $var['namastatus'],
                    'operator' => $var['operator'],
                    '_synced' => 0,
                ]);
        }
        return true;
    }

    public function insupdCtmStatusonoff($var)
    {
        $arrCol = array("nomorrekening", "bulan", "tahun", "status", "_synced");
        $arrVal = array($var['nomorrekening'], $var['bulanrekening'], $var['tahunrekening'], $var['statusonoff'], "0");

        $arrQry = array();
        foreach ($arrCol as $key => $value) {
            $arrQry[$value] = $arrVal[$key];
        }
        $arrUnique = array();
        $arrUnique['bulan'] = $var['bulanrekening'];
        $arrUnique['tahun'] = $var['tahunrekening'];
        $arrUnique['nomorrekening'] = $var['nomorrekening'];

        if ($map_kunjungan = CtmStatusonoff::updateOrCreate($arrUnique, $arrQry)) {
            return true;
        } else {
            return false;
        }

    }

    public function getCtmJenispelanggan($nomorrekening)
    {
        $return_obj = array();
        $tblpelanggan_fetch = DB::connection('mysql2')->table('tblpelanggan')
            ->join('tbljenispelanggan', 'tblpelanggan.idgol', '=', 'tbljenispelanggan.id')
            ->where('nomorrekening', $nomorrekening)
            ->select('tbljenispelanggan.*', 'tblpelanggan.idgol', 'tblpelanggan.idareal', 'tblpelanggan.idbiro')
            ->get();
        foreach ($tblpelanggan_fetch as $tblpelanggan_array) {
            $return_obj['idgol'] = $tblpelanggan_array->idgol;
            $return_obj['idareal'] = $tblpelanggan_array->idareal;
            $return_obj['tarif01'] = $tblpelanggan_array->tarif01;
            $return_obj['tarif02'] = $tblpelanggan_array->tarif02;
            $return_obj['tarif03'] = $tblpelanggan_array->tarif03;
            $return_obj['tarif04'] = $tblpelanggan_array->tarif04;
            $return_obj['tarif05'] = $tblpelanggan_array->tarif05;
            $return_obj['tarif06'] = $tblpelanggan_array->tarif06;
            $return_obj['danameter'] = $tblpelanggan_array->danameter;
            $return_obj['adm'] = $tblpelanggan_array->danaadministrasi;
            $return_obj['beban'] = $tblpelanggan_array->beban;
            $return_obj['denda'] = 0;
            $return_obj['batas1'] = $tblpelanggan_array->batastarif1;
            $return_obj['batas2'] = $tblpelanggan_array->batastarif2;
            $return_obj['batas3'] = $tblpelanggan_array->batastarif3;
            $return_obj['batas4'] = $tblpelanggan_array->batastarif4;
            $return_obj['batas5'] = $tblpelanggan_array->batastarif5;
            $return_obj['batas6'] = $tblpelanggan_array->batastarif6;
            $return_obj['idbiro'] = $tblpelanggan_array->idbiro;
        }

        return $return_obj;
    }

    public function getCtmTagihan($tblpelanggan_arr)
    {
        $tblpelanggan_arr['batas6'] = 2147483647;
        $return_obj = array();
        $batas0 = 0;
        //$pajak=0.1 * ($tblpelanggan_arr['danameter']+$tblpelanggan_arr['adm']+$tblpelanggan_arr['beban']);
        $pajak = 0;
        $kubik_yang_dipakai = max(0, $tblpelanggan_arr['pemakaianair']);
        $pemakaian1 = $tblpelanggan_arr['tarif01'] * max(0, min($kubik_yang_dipakai - $batas0, $tblpelanggan_arr['batas1'] - $batas0));
        $pemakaian2 = $tblpelanggan_arr['tarif02'] * max(0, min($kubik_yang_dipakai - $tblpelanggan_arr['batas1'], $tblpelanggan_arr['batas2'] - $tblpelanggan_arr['batas1']));
        $pemakaian3 = $tblpelanggan_arr['tarif03'] * max(0, min($kubik_yang_dipakai - $tblpelanggan_arr['batas2'], $tblpelanggan_arr['batas3'] - $tblpelanggan_arr['batas2']));
        $pemakaian4 = $tblpelanggan_arr['tarif04'] * max(0, min($kubik_yang_dipakai - $tblpelanggan_arr['batas3'], $tblpelanggan_arr['batas4'] - $tblpelanggan_arr['batas3']));
        $pemakaian5 = $tblpelanggan_arr['tarif05'] * max(0, min($kubik_yang_dipakai - $tblpelanggan_arr['batas4'], $tblpelanggan_arr['batas5'] - $tblpelanggan_arr['batas4']));
        $pemakaian6 = $tblpelanggan_arr['tarif06'] * max(0, min($kubik_yang_dipakai - $tblpelanggan_arr['batas5'], $tblpelanggan_arr['batas6'] - $tblpelanggan_arr['batas5']));
        $rp_tagihan = $tblpelanggan_arr['danameter'] + $tblpelanggan_arr['adm'] + $tblpelanggan_arr['beban'] + $pajak + $pemakaian1 + $pemakaian2 + $pemakaian3 + $pemakaian4 + $pemakaian5 + $pemakaian6;
        $gol_khusus_arr = array("K3", "K4", "K6", "K7", "K8", "K9", "K10", "K11", "K12", "K14");
        if (in_array($tblpelanggan_arr['idgol'], $gol_khusus_arr)) {
            $rp_tagihan = max($rp_tagihan, ($tblpelanggan_arr['danameter'] + $tblpelanggan_arr['adm'] + $tblpelanggan_arr['beban'] + $pajak + ($tblpelanggan_arr['tarif01'] * $tblpelanggan_arr['batas1'])));
        }
        //set output obj
        $pemakaianair = $pemakaian1 + $pemakaian2 + $pemakaian3 + $pemakaian4 + $pemakaian5 + $pemakaian6;
        $wajibdibayar = $rp_tagihan;
        $return_obj['pajak'] = $pajak;
        $return_obj['pemakaianair'] = $pemakaianair;
        $return_obj['pemakaianair01'] = $pemakaian1;
        $return_obj['pemakaianair02'] = $pemakaian2;
        $return_obj['pemakaianair03'] = $pemakaian3;
        $return_obj['pemakaianair04'] = $pemakaian4;
        $return_obj['pemakaianair05'] = $pemakaian5;
        $return_obj['pemakaianair06'] = $pemakaian6;
        $return_obj['rp_tagihan'] = $rp_tagihan;

        if (strpos($tblpelanggan_arr['idgol'], 'K') !== false) {
            if ($pemakaianair < $wajibdibayar) {
                $return_obj['pemakaianair'] = $wajibdibayar;
            }
            if ($pemakaian1 < $wajibdibayar && $pemakaian2 == 0) {
                $return_obj['pemakaianair01'] = $wajibdibayar;
            }
        }

        return $return_obj;
    }

    public function insupdCtmPembayaran($var)
    {
        $tblpelanggan_arr = $this->getCtmJenispelanggan($var['nomorrekening']);
        //hitung rp-tagihan
        $tblpelanggan_arr['pemakaianair'] = $var['pemakaianair'];
        $pdam_tagihan_arr = $this->getCtmTagihan($tblpelanggan_arr);
        //insert
        $arrCol = array("nomorrekening", "bulanrekening", "tahunrekening", "idgol", "idareal", "tarif01", "tarif02", "tarif03", "tarif04", "tarif05", "tarif06", "danameter", "adm", "beban", "denda", "batas1", "batas2", "batas3", "batas4", "batas5", "pajak", "pemakaianair", "pemakaianair01", "pemakaianair02", "pemakaianair03", "pemakaianair04", "pemakaianair05", "pemakaianair06", "bulanini", "bulanlalu", "wajibdibayar", "idbiro", "tglbayarterakhir", "operator", "operator1", "_synced");
        $arrVal = array($var['nomorrekening'], $var['bulanbayar'], $var['tahunbayar'], $tblpelanggan_arr['idgol'], $tblpelanggan_arr['idareal'], $tblpelanggan_arr['tarif01'], $tblpelanggan_arr['tarif02'], $tblpelanggan_arr['tarif03'], $tblpelanggan_arr['tarif04'], $tblpelanggan_arr['tarif05'], $tblpelanggan_arr['tarif06'], $tblpelanggan_arr['danameter'], $tblpelanggan_arr['adm'], $tblpelanggan_arr['beban'], "'0'", $tblpelanggan_arr['batas1'], $tblpelanggan_arr['batas2'], $tblpelanggan_arr['batas3'], $tblpelanggan_arr['batas4'], $tblpelanggan_arr['batas5'], $pdam_tagihan_arr['pajak'], $pdam_tagihan_arr['pemakaianair'], $pdam_tagihan_arr['pemakaianair01'], $pdam_tagihan_arr['pemakaianair02'], $pdam_tagihan_arr['pemakaianair03'], $pdam_tagihan_arr['pemakaianair04'], $pdam_tagihan_arr['pemakaianair05'], $pdam_tagihan_arr['pemakaianair06'], $var['pencatatanmeter'], $var['meterawal'], $pdam_tagihan_arr['rp_tagihan'], $tblpelanggan_arr['idbiro'], $var['datecatatf3'], $var['operator'], $var['operator'], "0");
        
        $arrQry = array();
        foreach ($arrCol as $key => $value) {
            $arrQry[$value] = $arrVal[$key];
        }
        $arrUnique = array();
        $arrUnique['tahunrekening'] = $var['tahunbayar'];
        $arrUnique['bulanrekening'] = $var['bulanbayar'];
        $arrUnique['nomorrekening'] = $var['nomorrekening'];

        if ($map_kunjungan = CtmPembayaran::updateOrCreate($arrUnique, $arrQry)) {
            return true;
        } else {
            return false;
        }    
    }

    public function getCtmAvg($nomorrekening, $month, $year)
    {
        $return_var = 0;
        $pemakain_3bln_total = 0;
        $div_bln = 0;
        $pencatatanmeter_last = 0;
        $return_obj = array();
        //select 3 month prev
        for ($i = 1; $i < 4; $i++) {
            $month_prev = (int) date('m', strtotime($year . '-' . $month . ' -' . $i . ' month', time()));
            $year_prev = (int) date('Y', strtotime($year . '-' . $month . ' -' . $i . ' month', time()));

            $pemakain_bln_row = CtmPemakaianAir::select('pemakaianair' . $month_prev . ' AS pemakaianair')
                ->where('tahunrekening', $year_prev)
                ->where('nomorrekening', $nomorrekening)
                ->get();
            if (count($pemakain_bln_row) == 0) {
                $pemakain_bln = 0;
            } else {
                $pemakain_bln = $pemakain_bln_row[0]->pemakaianair;
                $div_bln++;
            }

            if ($i == 1) {
                $pencatatanmeter_last_row = CtmPemakaianAir::select('pencatatanmeter' . $month_prev . ' AS pencatatanmeter')
                    ->where('tahunrekening', $year_prev)
                    ->where('nomorrekening', $nomorrekening)
                    ->get();
                if (count($pencatatanmeter_last_row) == 0) {
                    $pencatatanmeter_last = 0;
                } else {
                    $pencatatanmeter_last = $pencatatanmeter_last_row[0]->pencatatanmeter;
                }
            }
            $pemakain_3bln_total += $pemakain_bln;
        }
        if ($div_bln == 0) {
            $div_bln = 1;
        }
        $pencatatanmeter_avg = $pencatatanmeter_last + ceil($pemakain_3bln_total / $div_bln);
        $return_obj['pencatatanmeter_avg'] = $pencatatanmeter_avg;
        $return_obj['pemakaian_avg'] = ceil($pemakain_3bln_total / $div_bln);
        return $return_obj;
    }

    public function getCtmPrev($nomorrekening, $month, $year)
    {
        $return_obj = array();
        $pemakaianair_bln = 0;
        $pencatatanmeter_bln = 0;
        $month_prev = (int) date('m', strtotime($year . '-' . $month . ' -1 month', time()));
        $year_prev = (int) date('Y', strtotime($year . '-' . $month . ' -1 month', time()));
        $pemakaianair_bln_row = CtmPemakaianAir::select('pemakaianair' . $month_prev . ' AS pemakaianair', 'pencatatanmeter' . $month_prev . ' AS pencatatanmeter')
            ->where('tahunrekening', $year_prev)
            ->where('nomorrekening', $nomorrekening)
            ->get();
        if (count($pemakaianair_bln_row) == 0) {
            $pemakaianair_bln = false;
            $pencatatanmeter_bln = false;
        } else {
            $pemakaianair_bln = $pemakaianair_bln_row[0]->pemakaianair;
            $pencatatanmeter_bln = $pemakaianair_bln_row[0]->pencatatanmeter;
        }
        $statussm_bln_row = CtmStatussmPelanggan::select('statussm')
            ->where('tahun', $year_prev)
            ->where('bulan', $month_prev)
            ->where('nomorrekening', $nomorrekening)
            ->get();
        if (count($statussm_bln_row) == 0) {
            $statussm_bln = '-';
        } else {
            $statussm_bln = $statussm_bln_row[0]->statussm;
        }
        $return_obj['pemakaianair'] = $pemakaianair_bln;
        $return_obj['pencatatanmeter'] = $pencatatanmeter_bln;
        $return_obj['statussm'] = $statussm_bln;
        return $return_obj;
    }

    public function get_last_code($type)
    {
        if ($type == "action") {
            $action = Action::orderBy('id', 'desc')
                ->first();
            if ($action && (strlen($action->code) == 8)) {
                $code = $action->code;
            } else {
                $code = acc_codedef_generate('ACT', 8);
            }
        }

        if ($type == "customer") {
            $customer = Customer::OrderRawMaps('id', 'desc')
                ->first();
            if ($customer) {
                $code = $customer->code;
            } else {
                $code = 0;
            }
        }

        if ($type == "category") {
            $category = Category::orderBy('id', 'desc')
                ->first();
            if ($category && (strlen($category->code) == 8)) {
                $code = $category->code;
            } else {
                $code = acc_codedef_generate('CAT', 8);
            }
        }

        if ($type == "dapertement") {
            $dapertement = Dapertement::orderBy('id', 'desc')
                ->first();
            if ($dapertement && (strlen($dapertement->code) == 8)) {
                $code = $dapertement->code;
            } else {
                $code = acc_codedef_generate('DAP', 8);
            }
        }

        if ($type == "subdapertement") {
            $dapertement = Subdapertement::orderBy('id', 'desc')
                ->first();
            if ($dapertement && (strlen($dapertement->code) == 8)) {
                $code = $dapertement->code;
            } else {
                $code = acc_codedef_generate('SDP', 8);
            }
        }

        if ($type == "staff") {
            $staff = Staff::orderBy('id', 'desc')
                ->first();
            if ($staff && (strlen($staff->code) == 8)) {
                $code = $staff->code;
            } else {
                $code = acc_codedef_generate('STF', 8);
            }
        }

        if ($type == "ticket") {
            $ticket = Ticket::orderBy('id', 'desc')
                ->first();
            if ($ticket && (strlen($ticket->code) == 8)) {
                $code = $ticket->code;
            } else {
                $code = acc_codedef_generate('TIC', 8);
            }
        }

        return $code;
    }

    public function acc_get_last_code($accounts_group_id)
    {
        $account = Account::where('accounts_group_id', $accounts_group_id)
            ->orderBy('code', 'desc')
            ->first();
        if ($account) {
            $code = $account->code;
        } else {
            $accounts_group = AccountsGroup::select('code')->where('id', $accounts_group_id)->first();
            $accounts_group_code = $accounts_group->code;
            $code = acc_codedef_generate($accounts_group_code, 5);
        }

        return $code;
    }

    public function mbr_get_last_code()
    {
        $account = Customer::where('type', 'member')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('MBR', 8);
        }

        return $code;
    }

    public function cst_get_last_code()
    {
        $account = Customer::where('type', '!=', 'member')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('CST', 8);
        }

        return $code;
    }

    public function prd_get_last_code()
    {
        $account = Production::where('type', 'production')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('PRD', 8);
        }

        return $code;
    }

    public function ord_get_last_code()
    {
        $account = Production::where('type', 'sale')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('ORD', 8);
        }

        return $code;
    }

    public function oag_get_last_code()
    {
        $account = Production::where('type', 'agent_sale')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('OAG', 8);
        }

        return $code;
    }

    public function top_get_last_code()
    {
        $account = Production::where('type', 'topup')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('TOP', 8);
        }

        return $code;
    }

    public function get_ref_exc($id, $ref_arr, $lev_max, $id_exc)
    {
        $customer = Customer::find($id);
        $ref_id = $customer->ref_id;
        if ($ref_id > 0 && $lev_max <= 9) {
            $referal = Customer::find($ref_id);
            $ref_status = $referal->status;
            if (($ref_id != $id_exc) && ($ref_status == 'active')) {
                array_push($ref_arr, $ref_id);
            }
            $lev_max++;
            return $this->get_ref_exc($ref_id, $ref_arr, $lev_max, $id_exc);
        } else {
            return $ref_arr;
        }
    }

    public function get_ref($id, $ref_arr, $lev_max)
    {
        $customer = Customer::find($id);
        $ref_id = $customer->ref_id;
        if ($ref_id > 0 && $lev_max <= 9) {
            $referal = Customer::find($ref_id);
            $ref_status = $referal->status;
            if ($ref_status == 'active') {
                array_push($ref_arr, $ref_id);
            }
            $lev_max++;
            return $this->get_ref($ref_id, $ref_arr, $lev_max);
        } else {
            return $ref_arr;
        }
    }
}
