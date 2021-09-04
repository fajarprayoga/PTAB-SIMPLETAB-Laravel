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
        $departementlist = Dapertement::all();
        return view ('admin.reports.subHumas',compact('departementlist'));
        // return view ('admin.reports.reportSubHumas', compact('tickets'));
    }

    public function reportSubHumasProses(Request $request)
    {
    //    return $request;
            if($request->dapertement_id!='' && $request->status!=''){
                $tickets = Ticket::whereBetween('created_at', [$request->from, $request->to])->where('dapertement_id',$request->dapertement_id)->where('status',$request->status)->with(['action', 'customer', 'category','dapertement'])->get();
                return view ('admin.reports.reportSubHumas', compact('tickets','request'));
            }elseif($request->dapertement_id!=''){
                $tickets = Ticket::whereBetween('created_at', [$request->from, $request->to])->where('dapertement_id',$request->dapertement_id)->where('status',$request->status='close')->with(['action', 'customer', 'category','dapertement'])->get();
                return view ('admin.reports.reportSubHumas', compact('tickets','request'));
            }elseif($request->status!=''){
                $tickets = Ticket::whereBetween('created_at', [$request->from, $request->to])->where('status',$request->status)->with(['action', 'customer', 'category','dapertement'])->get();
                return view ('admin.reports.reportSubHumas', compact('tickets','request'));
            }else{
                $tickets = Ticket::whereBetween('created_at', [$request->from, $request->to])->where('status',$request->status='close')->with(['action', 'customer', 'category','dapertement'])->get();
                return view ('admin.reports.reportSubHumas', compact('tickets','request'));
            }
            
         }

    public function reportSubDistribusi(){
        $departementlist = Dapertement::all();
        // return view ('admin.reports.reportSubDistribusi');
        return view ('admin.reports.subDistribusi',compact('departementlist'));
    }

    public function reportSubDistribusiProses(Request $request)
    {
        // return $request;
            if($request->dapertement_id!='' && $request->status!=''){
            $tickets = Ticket::whereBetween('created_at', [$request->from, $request->to])->where('dapertement_id',$request->dapertement_id)->where('status',$request->status)->with(['action', 'customer', 'category','dapertement'])->get();
                return view ('admin.reports.reportSubDistribusi', compact('tickets','request'));
            }elseif($request->dapertement_id!=''){
            $tickets = Ticket::whereBetween('created_at', [$request->from, $request->to])->where('dapertement_id',$request->dapertement_id)->where('status',$request->status='close')->with(['action', 'customer', 'category','dapertement'])->get();
                return view ('admin.reports.reportSubDistribusi', compact('tickets','request'));
            }elseif($request->status!=''){
            $tickets = Ticket::whereBetween('created_at', [$request->from, $request->to])->where('status',$request->status)->with(['action', 'customer', 'category','dapertement'])->get();
                    return view ('admin.reports.reportSubDistribusi', compact('tickets','request'));
            }else{
            $tickets = Ticket::whereBetween('created_at', [$request->from, $request->to])->where('status',$request->status='close')->with(['action', 'customer', 'category','dapertement'])->get();
            return view ('admin.reports.reportSubDistribusi', compact('tickets','request'));
            }

    }

}