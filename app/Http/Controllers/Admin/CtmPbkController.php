<?php

namespace App\Http\Controllers\Admin;

use App\CtmPbk;
use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use Illuminate\Http\Request;

class CtmPbkController extends Controller
{
    use TraitModel;

    public function index()
    {
        abort_unless(\Gate::allows('pbk_access'), 403);
        $pbks = CtmPbk::all();

        return view('admin.pbks.index', compact('pbks'));
    }

    public function editStatus($id)
    {
        abort_unless(\Gate::allows('pbk_edit'), 403);
        $pbk = CtmPbk::where('Number', $id)->first();
        return view('admin.pbks.edit', compact('pbk'));
    }

    public function updateStatus(Request $request)
    {
        abort_unless(\Gate::allows('pbk_edit'), 403);
        //update status
        CtmPbk::where('Number', $request->Number)
            ->update([
                'Status' => $request->Status,
            ]);
        return redirect()->route('admin.pbks.index');
    }
}
