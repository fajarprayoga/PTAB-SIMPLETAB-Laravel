@extends('layouts.admin')
@section('content')
@if($errors->any())
    <?php 
        echo "<script> alert('{$errors->first()}')</script>";
    ?>
@endif
<div class="card">
    <div class="card-header">
        {{ trans('global.lock.title') }} {{ trans('global.list') }}
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
                        <span class="input-group-btn">
                            &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                        </span>
                    </div>                
                </form>
            </div> 
        </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-lock">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        <th>
                            {{ trans('global.lock.code') }}
                        </th>
                        <th>
                            Register
                        </th>
                        <th>
                            {{ trans('global.lock.customer') }}
                        </th>
                        <th>
                            {{ trans('global.lock.description') }}
                        </th>
                        <th>
                            {{ trans('global.lock.status') }}
                        </th>
                        <th>
                            {{ trans('global.lock.subdapertement') }}
                        </th>
                        <th>
                            {{ trans('global.lock.start') }}
                        </th>
                        <th>
                            {{ trans('global.lock.end') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                  
                </tbody>
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

  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.users.massDestroy') }}",
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
    @can('user_delete')
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
            url: "{{ route('admin.lock.index') }}",
            data: {
                'status': $("#status").val(),
                }
            },
            columns: [
                { data: 'placeholder', name: 'placeholder' },
                { data: 'DT_RowIndex', name: 'no', searchable: false },
                { data: 'code', name: 'code' },
                { data: 'register', name: 'register', searchable: false },
                { data: 'customer', name: 'customer', searchable: false },
                { data: 'description', name: 'description' },
                { data: 'status', render: function (dataField) { return dataField === 'pending' ?'<button type="button" class="btn btn-warning btn-sm" disabled>Pending</button>':dataField === 'lock_resist' ?'<button type="button" class="btn btn-primary btn-sm" disabled>Hambatan Segel</button>':dataField === 'lock' ?'<button type="button" class="btn btn-primary btn-sm" disabled>Segel</button>':dataField === 'unplug_resist' ?'<button type="button" class="btn btn-primary btn-sm" disabled>Hambatan Cabut</button>':dataField === 'unplug' ?'<button type="button" class="btn btn-primary btn-sm" disabled>Cabut</button>':'<button type="button" class="btn btn-success btn-sm" disabled>Selesai</button>'; } },
                { data: 'subdapertement', name: 'subdapertement', searchable: false },
                { data: 'start', name: 'start', searchable: false },
                { data: 'end', name: 'end', searchable: false },
                { data: 'staff', name: '{{ trans('global.staff.title') }}' }
            ],
            // order: [[ 2, 'asc' ]],
            pageLength: 100,
        };

        $('.datatable-lock').DataTable(dtOverrideGlobals);
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
            $($.fn.dataTable.tables(true)).DataTable()
                .columns.adjust();
        });
    })

</script>
@endsection
@endsection