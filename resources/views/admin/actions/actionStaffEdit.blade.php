@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} Status Tindakan
    </div>

    <div class="card-body">
        <form action="{{route('admin.actions.actionStaffUpdate')}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name='action_id' value='{{$action->id}}'>
            <input type="hidden" name='ticket_id' value='{{$action->ticket_id}}'>
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.action.fields.code') }}*</label>
                <input type="text" disabled id="code" name="code" class="form-control" value="{{$action->ticket->code}}" >
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action.fields.description') }}*</label>
                <input type="text" disabled id="description" name="description" class="form-control" value="{{$action->description}}" >
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div>           
            
            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                <label for="status">{{ trans('global.action_staff.fields.status') }}*</label>
                <select id="status" name="status" class="form-control" value="{{ old('status', isset($action) ? $action->status : '') }}">
                    <option value="">--Pilih status--</option>
                    <option value="pending" {{$action->status == 'pending' ? 'selected' :''}} >Pending</option>
                    <option value="active" {{$action->status == 'active' ? 'selected' :''}} >Active</option>
                    <option value="close" {{$action->status == 'close' ? 'selected' :''}} >Close</option>                    
                </select>
                @if($errors->has('status'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('memo') ? 'has-error' : '' }}">
                <label for="memo">{{ trans('global.action.fields.memo') }}*</label>
                <textArea id="memo" name="memo" class="form-control" >{{$action->memo}}</textArea>
                @if($errors->has('memo'))
                    <em class="invalid-feedback">
                        {{ $errors->first('memo') }}
                    </em>
                @endif                
            </div>
            <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                <label for="image">{{ trans('global.ticket.fields.image') }}*</label>
                {{-- <input type="file" id="image" name="image" class="form-control" value="{{ old('image', isset($ticket) ? $ticket->image : '') }}">
                @if($errors->has('image'))
                    <em class="invalid-feedback">
                        {{ $errors->first('image') }}
                    </em>
                @endif --}}
                <div class="input-group control-group increment" >
                    <input type="file" name="image[]" class="form-control">
                    <div class="input-group-btn"> 
                      <button class="btn btn-success" type="button"><i class="glyphicon glyphicon-plus"></i>Add</button>
                    </div>
                  </div>
                  <div class="clone hide">
                    <div class="control-group input-group" style="margin-top:10px">
                      <input type="file" name="image[]" class="form-control">
                      <div class="input-group-btn"> 
                        <button class="btn btn-danger" type="button"><i class="glyphicon glyphicon-remove"></i> Remove</button>
                      </div>
                    </div>
                  </div>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
    @parent
    <script>
            $(".btn-success").click(function(){ 
                var html = $(".clone").html();
                $(".increment").after(html);
            });
            $("body").on("click",".btn-danger",function(){ 
                $(this).parents(".control-group").remove();
            });
    </script>
@endsection