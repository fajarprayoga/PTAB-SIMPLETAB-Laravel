<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Dapertement;
use App\Http\Requests\StoreActionRequest;
use App\Ticket;
use App\Staff;
use App\Action;
use Yajra\DataTables\Facades\DataTables;
use DB;
use App\Traits\TraitModel;
class ReportsController extends Controller
{
    use TraitModel;


    public function reportSubHumas(){
        return view ('admin.reports.subHumas');
        // return view ('admin.reports.reportSubHumas', compact('tickets'));
    }

    public function reportSubHumasProses(Request $request)
    {
        $tickets = Ticket::whereYear('created_at', '=', $request->year)->whereMonth('created_at', '=', $request->month)->with(['action', 'customer', 'category','dapertement'])->get();
        return view ('admin.reports.reportSubHumas', compact('tickets'));
    }

    public function reportSubDistribusi(){
        // return view ('admin.reports.reportSubDistribusi');
        return view ('admin.reports.subDistribusi');
    }

    public function reportSubDistribusiProses(Request $request)
    {
        $tickets = Ticket::whereYear('created_at', '=', $request->year)->whereMonth('created_at', '=', $request->month)->with(['action', 'customer', 'category','dapertement'])->get();
        // return view ('admin.reports.reportSubHumas', compact('tickets'));
        // $tickets = Ticket::whereYear('created_at', '=', $request->year)->whereMonth('created_at', '=', $request->month)->with('customer')->get();
        return view ('admin.reports.reportSubDistribusi', compact('tickets'));
    }

}