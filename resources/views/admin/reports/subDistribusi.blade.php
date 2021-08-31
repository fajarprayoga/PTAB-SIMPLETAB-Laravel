@extends('layouts.admin')
@section('content')
    <form action="{{ route('admin.report.subdistribusiproses') }}" method="POST" enctype="multipart/form-data" >
        @csrf
        <div class="mb-3">
            <select class="form-select" aria-label="Default select example" name="month" required >
                <option selected>Pilih Bulan </option>
                <option value="01">Januari</option>
                <option value="02">Februari</option>
                <option value="03">Maret</option>
                <option value="04">April</option>
                <option value="05">Mei</option>
                <option value="06">Juni</option>
                <option value="07">Jui</option>
                <option value="08">Agutus</option>
                <option value="09">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
            </select>
        </div>
        <div class="mb-3">
            <select class="form-select" aria-label="Default select example" name="year" required>
                <option selected>Pilih Tahun </option>
                <option value="2020">2020</option>
                <option value="2021">2021</option>
            </select>
        </div>
        <input type="submit" class="btn btn-primary" value="Proses" >
      </form>
@endsection