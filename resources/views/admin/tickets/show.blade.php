@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.ticket.title_singular') }}
    </div>

    <div class="card-body">
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.code') }}</h5>
            <p>{{$ticket->code}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.title') }}</h5>
            <p>{{$ticket->title}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.description') }}</h5>
            <p>{{$ticket->description}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.status') }}</h5>
            <p>{{$ticket->status}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.category') }}</h5>
            <p>{{$ticket->category->name}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3" >
            <h5 style="font-weight:bold">{{ trans('global.ticket.fields.customer') }}</h5>
            <p>{{$ticket->customer->name}}</p>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3 pb-3 row" >
            <div class="col-md-6">
                <h5 style="font-weight:bold">{{ trans('global.ticket.fields.image') }}</h5>
             
                {{-- @foreach (json_decode($ticket->image->image) as $image)
                    <img  height="200px" width="300px"  src={{"https://simpletabadmin.ptab-vps.com/$image"}} alt="">
                @endforeach --}}

                @foreach ($ticket->ticket_image as $image)
                    @foreach (json_decode($image->image) as $item)
                        <img  height="200px" width="300px"  src={{"https://simpletabadmin.ptab-vps.com/$item"}} alt="">
                    @endforeach
                @endforeach
            </div>
            <div class="col-md-6">
                <h5 style="font-weight:bold">{{ trans('global.ticket.fields.video') }}</h5>
                <video width="300px" height="200px" controls>
                    <source src={{"https://simpletabadmin.ptab-vps.com/$ticket->video"}} type="video/mp4">
                    {{-- <source src="mov_bbb.ogg" type="video/ogg"> --}}
                </video>
            </div>
        </div>
        <!-- <div style="border-bottom: 1px solid" class="mt-3 pb-3" >
           
        </div> -->
       
        <br>
        @can('action_print_service')
        
            <a class="btn btn-lg btn-primary fa fa-print" target="_blank" href="{{ route('admin.tickets.printservice',$ticket->id) }}">
                {{ trans('global.action.print_service') }}
            </a>
        
        @endcan
        @can('action_print_spk')
        
            <a class="btn btn-lg btn-info fa fa-print " target="_blank" href="{{ route('admin.tickets.printspk',$ticket->id) }}">
                {{ trans('global.action.print_SPK') }}
            </a>
        
        @endcan
        @can('action_print_report')
        @if ($ticket->status == "close")
            <a class="btn btn-lg btn-success fa fa-print" target="_blank" href="{{ route('admin.tickets.printreport',$ticket->id) }}">
                {{ trans('global.action.print_Report') }}
            </a>
        @endif
        @endcan
    </div>
</div>

@endsection