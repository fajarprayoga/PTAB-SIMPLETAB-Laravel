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
                <img  height="200px" width="300px"  src={{"https://simpletabadmin.ptab-vps.com/$ticket->image"}} alt="">
            </div>
            <div class="col-md-6">
                <h5 style="font-weight:bold">{{ trans('global.ticket.fields.video') }}</h5>
                <video width="300px" height="200px" controls>
                    <source src={{"https://simpletabadmin.ptab-vps.com/$ticket->video"}} type="video/mp4">
                    {{-- <source src="mov_bbb.ogg" type="video/ogg"> --}}
                </video>
            </div>
        </div>
        <div style="border-bottom: 1px solid" class="mt-3 pb-3" >
           
        </div>
    </div>
</div>

@endsection