<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PERUSAHAAN UMUM DAERAH AIR MINUM</title>
    <link href="{{ asset('css/printsubdistribusi.css') }}" rel="stylesheet" />
</head>
<body class="A4" onload="onload()">
    <section class="sheet padding-10mm">
        <h3>PERUSAHAAN UMUM DAERAH AIR MINUM KABUPATEN DATI II TABANAN WILAYAH PELAYANAN KOTA</h3>
        <!-- <h3>BULAN : {{ count($tickets) > 0 ?  date('F Y', strtotime($tickets[0]->created_at)) : 'Tidak ada data kosong' }} </h3> -->
        <h3>PERIODE : Dari {{$request->from}} Sampai {{$request->to}}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>NAMA</th>
                    <th>ALAMAT</th>
                    <th>AREA</th>
                    <th>TGL MASUK</th>
                    <th>KELUHAN</th>
                    <th>NO SPK</th>
                    <th>TGL DIKERJAKAN</th>
                    <th>PEKERJA</th>
                    <th>KET / TINDAKAN</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1 ?>
                @foreach ($tickets as $ticket)
                    <tr>
                        <td class="text-center">{{$no++}}</td>
                        <td>{{ $ticket->customer->name }}</td>
                        <td>{{$ticket->customer->address}}</td>
                        <td>{{$ticket->area}}</td>
                        <td>@if ($ticket->created_at != null) {{$ticket->created_at->format('d/m/Y')}} @endif</td>
                        <td>{{$ticket->description}}</td>
                        <td>{{$ticket->spk}}</td>
                        <td>@if ($ticket->created_at != null) {{$ticket->created_at->format('d/m/Y')}} @endif</td>
                        <td>Internal</td>
                        <td> @foreach ($ticket->action as $ticketaction)*{{$ticketaction->description}}"<br>@endforeach
                        </td>
                    </tr>
                @endforeach
               
            </tbody>
        </table>
    </section>
<script>
onload = function (){
    window.print();
}
</script>
</body>
</html>