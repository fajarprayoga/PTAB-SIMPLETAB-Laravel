@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.action_staff.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{route('admin.actions.actionStaffUpdate')}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name='action_id' value='{{$action->id}}'>
            <input type="hidden" name='staff_id' value='{{$action_staffs_list->staff_id}}'>
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.action_staff.fields.code') }}*</label>
                <!-- <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($staff) ? $staff->code : '') }}"> -->
                <input type="text" disabled id="code" name="code" class="form-control" value="{{$action_staffs_list->code}}" >
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.action_staff.fields.name') }}*</label>
                <!-- <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($staff) ? $staff->name : '') }}"> -->
                <input type="text" disabled id="name" name="name" class="form-control" value="{{$action_staffs_list->name}}" >
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.action_staff.fields.description') }}*</label>
                <input type="text" disabled id="description" name="description" class="form-control" value="{{$action->description}}" >
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                <label for="status">{{ trans('global.action_staff.fields.status') }}*</label>
                <select id="status" name="status" class="form-control" value="{{ old('status', isset($action_staffs_list) ? $action_staffs_list->status : '') }}">
                    <option value="">--Pilih status--</option>
                    <option value="close" {{$action_staffs_list->status == 'close' ? 'selected' :''}} >Close</option>
                    <option value="pending" {{$action_staffs_list->status == 'pending' ? 'selected' :''}} >Pending</option>
                    <option value="active" {{$action_staffs_list->status == 'active' ? 'selected' :''}} >Active</option>
                </select>
                @if($errors->has('status'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status') }}
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