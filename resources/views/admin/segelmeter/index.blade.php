@extends('layouts.admin')
@section('content')
@if($errors->any())
<!-- <h4>{{$errors->first()}}</h4> -->
    <?php 
        echo "<script> alert('{$errors->first()}')</script>";
    ?>
@endif
<div class="card">

    <div class="card-header">
        {{ trans('global.segelmeter.title') }} {{ trans('global.list') }}
    </div>
    <div class="card-body">
    <div class="form-group">
        <div class="mt-2">
                <a href="{{ route('admin.segelmeter.deligate') }}" class="btn btn-primary">Teruskan Serentak</a >
        </div>
        <div class="mt-2">
            &nbsp;
        </div>
        <div class="col-md-6">
             <form action="" id="filtersForm">
                <div class="input-group">
                    <select id="status_tunggakan" name="status_tunggakan" class="form-control">
                        <option value="">== Status Tunggaakan ==</option>
                        <option value="0" {{ !empty($_GET['status_tunggakan']) && $_GET['status_tunggakan']  ==0 ?'selected' : '' }} >Lunas</option>
                        <option value="1" {{ !empty($_GET['status_tunggakan']) && $_GET['status_tunggakan']  ==1 ?'selected' : '' }}>Perhatian</option>
                        <option value="2" {{ !empty($_GET['status_tunggakan']) && $_GET['status_tunggakan']  ==1 ?'selected' : '' }}>Tunggak</option>
                        <option value="3" {{ !empty($_GET['status_tunggakan']) && $_GET['status_tunggakan']  ==1 ?'selected' : '' }}>Segel</option>
                        <option value="4" {{ !empty($_GET['status_tunggakan']) && $_GET['status_tunggakan']  ==1 ?'selected' : '' }}>Cabut</option>
                    </select>
                    <span class="input-group-btn">
                    &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                    </span>
                </div>                
             </form>
             </div> 
        </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-staff">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        <th>
                            {{ trans('global.segelmeter.norekening') }}
                        </th>
                        <th>
                            {{ trans('global.segelmeter.name') }}
                        </th>
                        <th>
                            {{ trans('global.segelmeter.address') }}
                        </th>
                        <th>
                            {{ trans('global.segelmeter.tunggakan') }}
                        </th>
                        <th>
                            {{ trans('global.segelmeter.status') }}
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
            let dapertement_id = searchParams.get('dapertement_id')
            if (dapertement_id) {
                $("#dapertement_id").val(dapertement_id);
            }else{
                $("#dapertement_id").val('');
            }
            let status_tunggakan = searchParams.get('status_tunggakan')
            if (status_tunggakan) {
                $("#status_tunggakan").val(status_tunggakan);
            }else{
                $("#status_tunggakan").val('');
            }

            // console.log('type : ', type);

    let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
    let deleteButton = {
        text: deleteButtonTrans,
        url: "{{ route('admin.staffs.massDestroy') }}",
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
        @can('staff_delete')
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
      url: "{{ route('admin.segelmeter.index') }}",
      data: {
        // 'dapertement_id': $("#dapertement_id").val(),
            'status' : $('#status_tunggakan').val()
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder'},
        { data: 'DT_RowIndex', name: 'no', searchable: false },
        { data: 'nomorrekening', name: 'nomorrekening' },
        { data: 'namapelanggan', name: 'namapelanggan' },
        { data: 'alamat', name: 'alamat' },
        { data: 'jumlahtunggakan', name: 'jumlahtunggakan', searchable: false },
        { data: 'statusnunggak', name: 'statusnunggak', searchable: false },
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    order: [[ 2, 'asc' ]],
    pageLength: 100,
  };

  $('.datatable-staff').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection
@endsection