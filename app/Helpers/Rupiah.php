<?php
    
    function rupiah($harga){
    $RP= 'RP. ' . number_format ($harga,0,',','.');
    return $RP;
    }
?>