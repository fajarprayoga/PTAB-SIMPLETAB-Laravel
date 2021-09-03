@extends('layouts.admin')
@section('content')
@can('ticket_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.tickets.create') }}">
                {{ trans('global.add') }} {{ trans('global.ticket.title_singular') }}
            </a>
        </div>
    </div>
    
@endcan

@if($errors->any())
<!-- <h4>{{$errors->first()}}</h4> -->
    <?php 
        echo "<script> alert('{$errors->first()}')</script>";
    ?>
@endif
<div class="card">

    <div class="card-header">
        {{ trans('global.ticket.title_singular') }} {{ trans('global.list') }}
    </div>
    <div class="card-body">
    <div class="form-group">
        <div class="col-md-6">
             <form action="" id="filtersForm">
                <div class="input-group">
                    <select id="status" name="status" class="form-control">
                        <option value="">== Semua Status ==</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="close">Close</option>
                    </select>
                    <select id="departement" name="departement" class="form-control">
                        <option value="">== Semua Departement ==</option>
                        @foreach ($departementlist as $depart )
                            <option value="{{$depart->id}}" >{{$depart->name}}</option>
                        @endforeach
                    </select>
                    <span class="input-group-btn">
                    &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                    </span>
                </div>                
             </form>
        </div> 
    </div>
    
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-ticket">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        <th>
                            {{ trans('global.ticket.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.ticket.fields.date') }}
                        </th>
                        <th>
                            {{ trans('global.ticket.fields.departement') }}
                        </th>
                        <th>
                            {{ trans('global.ticket.fields.title') }}
                        </th>
                        <th>
                            {{ trans('global.ticket.fields.description') }}
                        </th>
                        <th>
                            {{ trans('global.ticket.fields.status') }}
                        </th>
                        <th>
                            {{ trans('global.ticket.fields.category') }}
                        </th>
                        <th>
                            {{ trans('global.ticket.fields.customer') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                
            </table>
        </div>
    </div>
</div>
@section('scripts')
@parent
<script>
    $(function () {
        let searchParams = new URLSearchParams(window.location.search)

        let status = searchParams.get('status')
        if (status) {
            $("#status").val(status);
        }else{
            $("#status").val('');
        }

        let departement = searchParams.get('departement')
        if (departement) {
            $("#departement").val(departement);
        }else{
            $("#departement").val('');
        }

        // console.log('type : ', type);

  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.tickets.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
    @can('ticket_delete')
    dtButtons.push(deleteButton)
    @endcan

  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })

  let dtOverrideGlobals = {
    buttons: dtButtons,
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.tickets.index') }}",
      data: {
        'status': $("#status").val(),
        'departement': $("#departement").val(),
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no' },
        { data: 'code', name: 'code' },
        { data: 'created_at', name: 'created_at' },
        { data: 'dapertement', name: 'dapertement' },
        { data: 'title', name: 'title' },
        { data: 'description', name: 'description' },
        { data: 'status', render: function (dataField) { return dataField === 'pending' ?'<button type="button" class="btn btn-warning btn-sm" disabled>'+dataField+'</button>':dataField === 'active' ?'<button type="button" class="btn btn-primary btn-sm" disabled>'+dataField+'</button>':'<button type="button" class="btn btn-success btn-sm" disabled>'+dataField+'</button>'; } },
        { data: 'category', name: 'category' },
        { data: 'customer', name: 'customer' },
        { data: 'actions', name: '{{ trans('global.actions') }}' }

    ],
    // order: [[ 2, 'asc' ]],
    pageLength: 100,
  };

  $('.datatable-ticket').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection
@endsection