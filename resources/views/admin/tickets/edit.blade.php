@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.ticket.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route('admin.tickets.update', [$ticket->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.ticket.fields.code') }}*</label>
                <input  type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($ticket) ? $ticket->code : '') }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">{{ trans('global.ticket.fields.title') }}*</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title', isset($ticket) ? $ticket->title : '') }}">
                @if($errors->has('title'))
                    <em class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.ticket.fields.description') }}*</label>
                <textarea type="text" id="description" name="description" class="form-control" value=""> {{ old('description', isset($ticket) ? $ticket->description : '') }}</textarea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('customer') ? 'has-error' : '' }}">
                <label for="customer">{{ trans('global.ticket.fields.customer') }}*</label>
                <input type="text" id="customer" name="customer_id" class="form-control" value="{{ old('customer', isset($ticket) ? $ticket->customer_id : '') }}">
                @if($errors->has('customer'))
                    <em class="invalid-feedback">
                        {{ $errors->first('customer') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                <label for="status">{{ trans('global.ticket.fields.status') }}*</label>
                <select id="status" name="status" class="form-control" value="{{ old('status', isset($ticket) ? $ticket->status : '') }}">
                    <option value="">--Pilih status--</option>
                    <option value="pending" {{$ticket->status == 'pending' ? 'selected' : ''}} >Pending</option>
                    <option value="active" {{$ticket->status == 'active' ? 'selected' : ''}} >Active</option>
                    <option value="close" {{$ticket->status == 'close' ? 'selected' : ''}} >Close</option>
                </select>
                @if($errors->has('status'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                <label for="category">{{ trans('global.ticket.fields.category') }}*</label>
                <select id="category" name="category_id" class="form-control" value="{{ old('category', isset($ticket) ? $ticket->category : '') }}">
                    <option value="">--Pilih category--</option>
                    @foreach ($categories as $key=>$category )
                        <option value="{{$category->id}}" {{$category->id == $ticket->category->id ? 'selected' : ''}} >{{$category->name}}</option>
                    @endforeach
                </select>
                @if($errors->has('category'))
                    <em class="invalid-feedback">
                        {{ $errors->first('category') }}
                    </em>
                @endif
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection