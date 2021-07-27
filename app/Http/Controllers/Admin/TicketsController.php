<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Category;
use App\Customer;
use App\Ticket;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\QueryException;
use App\Traits\TraitModel;

class TicketsController extends Controller
{
    use TraitModel;

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('ticket_access'), 403);

        if ($request->ajax()) {
            // set query
            if(request()->input('status')!=""){
                $status = request()->input('status'); 
                $qry = Ticket::with('customer')
                ->with('category')
                ->where('status', $status)
                ->get();
            }else{
                $qry = Ticket::with('customer')
                ->with('category')
                ->get();
            }  

            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'ticket_show';
                $editGate = 'ticket_edit';
                $actionGate = 'action_access';
                $deleteGate = 'ticket_delete';
                $crudRoutePart = 'tickets';
                $print = true;

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'actionGate',
                    'deleteGate',
                    'print',
                    'crudRoutePart',
                    'row'
                ));
            });
            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('title', function ($row) {
                return $row->title ? $row->title : "";
            });
            $table->editColumn('description', function ($row) {
                return $row->description ? $row->description : "";
            });
            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });
                
            $table->editColumn('category', function ($row) {
                return $row->category ? $row->category->name : "";
            });

            $table->editColumn('customer', function ($row) {
                return $row->customer ? $row->customer->name  : "";
            });


            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //default view        

        return view('admin.tickets.index');
    }

    public function create()
    {
        $last_code = $this->get_last_code('ticket');

        $code = acc_code_generate($last_code, 8, 3);

        abort_unless(\Gate::allows('ticket_create'), 403);

        $categories = Category::all();

        //$customers = Customer::all();

        return view('admin.tickets.create', compact('categories', 'code'));
    }

    public function store(StoreTicketRequest $request)
    {

        abort_unless(\Gate::allows('ticket_create'), 403);

        $img_path = "/images/complaint";
        $video_path = "/videos/complaint";
        

        $basepath=str_replace("laravel-simpletab","public_html/simpletabadmin/",\base_path());
        

        // upload image 
        $resourceImage = $request->file('image');
        $nameImage = strtolower($request->code);
        $file_extImage = $request->file('image')->extension();
        $nameImage = str_replace(" ", "-", $nameImage);
        $img_name = $img_path . "/" . $nameImage . "-" . $request->customer_id . "." . $file_extImage;

        $resourceImage->move($basepath . $img_path, $img_name);

        // video
        $video_path = "/videos/complaint";
        $resource = $request->file('video');
        // $filename = $resource->getClientOriginalName();
        // $file_extVideo = $request->file('video')->extension();
        $video_name = $video_path."/".strtolower($request->code).'-'.$request->customer_id.'.mp4';

        $resource->move($basepath.$video_path,$video_name);



        // data 
        $data = array(
            'code' => $request->code,
            'title' => $request->title,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'image' =>  $img_name,
            'video' => $video_name,
            'customer_id' => $request->customer_id,
          );

       
        $ticket = Ticket::create($data);
        
        return redirect()->route('admin.tickets.index');
    
    }

    public function show(Ticket $ticket)
    {
        // dd($ticket->customer);
        return view('admin.tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        abort_unless(\Gate::allows('ticket_edit'), 403);

        $categories = Category::all();

        //$customers = Customer::all();

        return view('admin.tickets.edit', compact('ticket', 'categories'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        // dd($request->all());
        abort_unless(\Gate::allows('ticket_edit'), 403);

        $ticket->update($request->all());

        return redirect()->route('admin.tickets.index');
    }

    public function destroy(Ticket $ticket)
    {
        abort_unless(\Gate::allows('ticket_delete'), 403);

        // dd($ticket);
        try{
            $ticket->delete();
            return back();
        }
        catch(QueryException $e) {
            return back()->withErrors(['Mohon hapus dahulu data yang terkait']);
        }

    }

    public function massDestroy()
    {
        # code...
    }

    public function print($id)
    {
        $ticket = Ticket::findOrFail($id);
        // $newtime = strtotime($data->created_at);
        // $data->time = date('M d, Y',$newtime);

        return view('admin.tickets.print', compact('ticket'));

        // dd($ticket);
    }

    public function printAction($id)
    {
        $ticket = Ticket::findOrFail($id);
        // $newtime = strtotime($data->created_at);
        // $data->time = date('M d, Y',$newtime);

        return view('admin.tickets.printAction', compact('ticket'));

        // dd($ticket);
    }
}
