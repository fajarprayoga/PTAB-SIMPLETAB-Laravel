<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PERUSAHAAN UMUM DAERAH AIR MINUM</title>
    <link href="{{ asset('css/printsubhumas.css') }}" rel="stylesheet" />
</head>
<body class="A4"  onload="onload()" >

    {{-- {{ dd($tickets) }} --}}
    <section class="sheet padding-10mm">
        <h3>REKAPITULASI PERMINTAAN SERVICE</h3>
        <h3>BULAN : {{ count($tickets) > 0 ?  date('F Y', strtotime($tickets[0]->created_at)) : 'Tidak ada data kosong' }} </h3>
        <table class="table">
        <tr>
            <th rowspan="3">No</th>
            <th rowspan="3">HARI</th>
            <th rowspan="3">TANGGAL</th>
            <th rowspan="3">AREA</th>
            <th rowspan="3">No.SBG</th>
            <th rowspan="3">NAMA</th>
            <th rowspan="3">ALAMAT</th>
            <th colspan="3">KELUHAN MASUK</th>
            <th colspan="2">RENCANA PENANGANAN SERVICE</th>
            <th rowspan="3">T/P/R/L</th>
            <th rowspan="3">&nbsp;</th>
            <th colspan="2">TINDAKAN PENYELESESAIAN</th>
        </tr>
        <tr>
            <th colspan="3" class="text-center">Jam</th>
            <th rowspan="2" class="text-center">KODE</th>
            <th rowspan="2" class="text-center">KELUHAN</th>
            <th rowspan="2" class="text-center">TANGGAL</th>
            <th rowspan="2" class="text-center">KECEPATAN (HARI)</th>
        </tr>
        <tr>
            <th>AWAL</th>
            <th>AKHIR</th>
            <th>WAKTU</th>
        </tr>
        {{-- isi data --}}
        <?php $no=1 ?>
        @foreach ($tickets as $ticket)
    
            <tr>
                <td class="text-center">{{$no++}}</td>
                <td>{{date('l', strtotime($ticket->created_at))}}</td>
                <td>{{$ticket->created_at->format('d/m/Y')}}</td>
                <td>{{$ticket->area }}</td>
                <td>{{$ticket->customer->id}}</td>
                <td>{{ $ticket->customer->name }}</td>
                <td>{{$ticket->customer->address}}</td>
                <td>@if ($ticket->created_at != null) {{$ticket->created_at->format('H:i:s')}} @endif</td>
                <td>-</td>
                <td>-</td>
                <td>{{$ticket->category->code}}</td>
                <td>{{$ticket->category->name}}</td>
                <td>T</td>
                <td>0</td>
                <td>{{$ticket->updated_at->format('d/m/Y')}}</td>
                   <?php 
                   
                    $datetime1 = strtotime($ticket->created_at);
                    $datetime2 = strtotime($ticket->updated_at);
                 
                    $days = (int)(($datetime2 - $datetime1)/86400); ?>
                <td>{{$days}}</td>
            </tr>
        @endforeach
        {{-- batas isi data  --}}
    </table>
    </section>
<script>
    onload = function (){
        window.print();
    }
</script>
</body>
</html>