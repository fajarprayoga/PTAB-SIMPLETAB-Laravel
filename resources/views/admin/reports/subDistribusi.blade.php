@extends('layouts.admin')
@section('content')
    <form action="{{ route('admin.report.subdistribusiproses') }}"  target="_blank" method="POST" enctype="multipart/form-data" >
        @csrf
        <div class="row">
            <div class="col-lg-5">
                <div class="form-group">
                <label>Dari Tanggal</label>
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                        <input placeholder="masukkan tanggal Awal" type="date" class="form-control datepicker" name="from" value = "{{date('Y-m-d')}}">
                    </div>
                </div>
                <div class="form-group">
                <label>Sampai Tanggal</label>
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                        <input placeholder="masukkan tanggal Akhir" type="date" class="form-control datepicker" name="to" value = "{{date('Y-m-d')}}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Pilih Departement</label>
                    <select id="departement" name="dapertement_id" class="form-control">
                        <option value="">== Semua Departement ==</option>
                        @foreach ($departementlist as $depart )
                            <option value="{{$depart->id}}" >{{$depart->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Pilih Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">== Semua Status ==</option>    
                        <option value="close">Close</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>                        
                    </select>
                </div>

            </div>
        </div>
        <Button  type="submit"  class="btn btn-primary" value="Proses" >Proses</Button>
      </form>
      
@endsection
<script type="text/javascript">
 $(function(){
  $(".datepicker").datepicker({
      format: 'yyyy-mm-dd',
      autoclose: true,
      todayHighlight: true,
  });
 });
</script>