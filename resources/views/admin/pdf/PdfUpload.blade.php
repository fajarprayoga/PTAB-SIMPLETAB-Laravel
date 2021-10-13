@extends('layouts.admin')
@section('content')

<!-- <div class="container">
    <div class="panel panel-primary">
      <div class="panel-body">
        @if ($message = Session::get('success'))
        <div class="alert alert-success alert-block">
            <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $message }}</strong>
        </div>
        <a href="{{Session::get('pdf')}}">PDF</a>
        @endif
        @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Terdapat kesalahan dalam mengupload File.
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
            <form action="{{ route('admin.file.upload.post') }}" method="POST" enctype="multipart/form-data">
            @csrf
                <div class="col-md-6">
                    <input type="file" name="file" class="form-control">
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-success">Upload</button>
                </div>
            </div>
            </form>
        </div>
    </div>
</div> -->

<div class="card">
    <div class="card-header">
       Create Laporan Keuangan Audited
    </div>
    <div class="card-body">
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <a href="{{Session::get('pdf')}}">
                    <div class="nav-icon fas fa-file-pdf" style="font-size:30px"></div>
                    <div>Lihat File</div>
                </a>
                <strong>{{ $message }}</strong>
            </div>
           
            @endif
            @if (count($errors) > 0)
            <div class="alert alert-danger">
                <strong>Whoops!</strong> Terdapat kesalahan dalam mengupload File.
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('admin.file.upload.post') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="PDF">Laporan PDF</label>
                <input type="file" name="file" class="form-control">
            </div>
            <div>
                <button type="submit" class="btn btn-success">Upload</button>
            </div>
        </form>
    </div>
</div>

@endsection
