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
        return view ('admin.reports.reportSubHumas');
    }
public function reportSubDistribusi(){
        return view ('admin.reports.reportSubDistribusi');
    }
}