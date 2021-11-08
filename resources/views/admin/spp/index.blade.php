@extends('layouts.admin')
@section('content')
<div class="container">
    <div class="row">
        <!-- <div class="col-md-10 offset-md-1 "> -->
            <!-- <div class="row"> -->
                <div class="col-md-3 mt-5 mr-4 ml-4 text-center" style="background-color: #333;">
                    <a href="{{ route('admin.spp.sppprintall',['locks' => ['3','4']]) }}" class="nav-link ">
                        <div style="height:95px">
                            <div class="mt-3 mb-3">
                                <i class="nav-icon fas fa-print fa-3x" ></i>
                                <hr>
                                <p>Print SPP 1-31<p>
                            </div> 
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mt-5 mr-4 ml-4 text-center" style="background-color: #333;">
                    <a href="" class="nav-link ">
                        <div style="height:95px">
                            <div class="mt-3 mb-3">
                                <i class="nav-icon fas fa-print fa-3x" ></i>
                                <hr>
                                <p>Print SPP 31-60<p>
                            </div> 
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mt-5 mr-4 ml-4 text-center" style="background-color: #333;">
                    <a href="" class="nav-link ">
                        <div style="height:95px">
                            <div class="mt-3 mb-3">
                                <i class="nav-icon fas fa-print fa-3x" ></i>
                                <hr>
                                <p>Print SPP 61-90<p>
                            </div> 
                        </div>
                    </a>
                </div> 
            <!-- </div>  -->
        <!-- </div> -->
    </div>
</div>
@section('scripts')
@endsection
@endsection