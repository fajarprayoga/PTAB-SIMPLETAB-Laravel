@extends('layouts.admin')
@section('content')
<!-- @can('ctmrequests_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.ctmrequests.create') }}">
                {{ trans('global.add') }} {{ trans('global.ctmrequest.title_singular') }}
            </a>
        </div>
    </div>
@endcan -->
<div class="card">
    <div class="card-header">
        {{ trans('global.ctmrequest.fields.name') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.ctmrequest.fields.norek') }}
                        </th>
                        <th>
                            {{ trans('global.ctmrequest.fields.name') }}
                        </th>                        
                        <th>
                            {{ trans('global.ctmrequest.fields.address') }}
                        </th>
                        <th>
                            {{ trans('global.ctmrequest.fields.month') }}
                        </th>
                        <th>
                            {{ trans('global.ctmrequest.fields.year') }}
                        </th>
                        <th>
                            {{ trans('global.ctmrequest.fields.wmmeteran') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($ctmrequests as $key => $ctmrequest)
                        <tr data-entry-id="{{ $ctmrequest->id }}">
                            <td>

                            </td>
                            <td>
                            {{ $ctmrequest->norek ?? '' }}
                            </td>
                            <td>
                            {{ $ctmrequest->customer->name ?? '' }}
                            </td>                            
                            <td>
                            {{ $ctmrequest->customer->address ?? '' }}
                            </td>
                            <td>
                            {{ $ctmrequest->month ?? '' }}
                            </td>
                            <td>
                            {{ $ctmrequest->year ?? '' }}
                            </td>
                            <td>
                            {{ $ctmrequest->wmmeteran ?? '' }}
                            </td>
                            <td>
                                @if($ctmrequest->status =='pending')
                                @can('ctmrequests_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.ctmrequests.edit', $ctmrequest->id) }}">
                                        Setujui
                                    </a>
                                @endcan
                                @endif                                
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
    @section('scripts')

        @parent
        <!-- <script>
            $(function () {
        let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
        let deleteButton = {
            text: deleteButtonTrans,
            url: "{{ route('admin.subdapertements.massDestroy') }}",
            className: 'btn-danger',
            action: function (e, dt, node, config) {
            var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
                return $(entry).data('entry-id')
            });

            if (ids.length === 0) {
                alert('{{ trans('global.datatables.zero_selected') }}')

                return null;
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


            @can('ctmrequests_delete')
                dtButtons.push(deleteButton)
            @endcan

            $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
        })

        </script> -->
    @endsection 
@endsection