<?php

namespace App\Http\Controllers\Admin;

use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Ticket;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use DB;

class ReportsController extends Controller
{
    use TraitModel;

    public function reportSubHumas()
    {
        $departementlist = Dapertement::all();
        return view('admin.reports.subHumas', compact('departementlist'));
        // return view ('admin.reports.reportSubHumas', compact('tickets'));
    }

    public function reportSubHumasProses(Request $request)
    {
        $tickets = Ticket::whereBetween(DB::raw('DATE(created_at)'), [$request->from, $request->to])->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])->get();
        return view('admin.reports.reportSubHumas', compact('tickets', 'request'));
    }

    public function reportSubDistribusi()
    {
        $departementlist = Dapertement::all();
        // return view ('admin.reports.reportSubDistribusi');
        return view('admin.reports.subDistribusi', compact('departementlist'));
    }

    public function reportSubDistribusiProses(Request $request)
    {
        $tickets = Ticket::whereBetween(DB::raw('DATE(created_at)'), [$request->from, $request->to])->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])->get();
        return view('admin.reports.reportSubDistribusi', compact('tickets', 'request'));
    }

}
