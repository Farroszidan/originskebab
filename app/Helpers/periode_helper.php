<?php

function tentukan_periode($filter, $tanggal, $bulan, $tahun, $rentangMulai, $rentangSelesai, $triwulan = null, $semester = null)
{
    switch ($filter) {
        case 'tanggal':
            $start = $tanggal;
            $end = $tanggal;
            $label = 'Tanggal ' . date('d M Y', strtotime($tanggal));
            break;

        case 'bulan':
            $start = "$tahun-$bulan-01";
            $end = date("Y-m-t", strtotime($start));
            $label = 'Bulan ' . date('F Y', strtotime($start));
            break;

        case 'triwulan':
            $quarterMonths = [
                1 => [1, 3],
                2 => [4, 6],
                3 => [7, 9],
                4 => [10, 12]
            ];
            [$startMonth, $endMonth] = $quarterMonths[$triwulan];
            $start = "$tahun-" . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . "-01";
            $end = date("Y-m-t", strtotime("$tahun-" . str_pad($endMonth, 2, '0', STR_PAD_LEFT) . "-01"));
            $label = "Triwulan $triwulan Tahun $tahun";
            break;

        case 'semester':
            if ($semester == 1) {
                $start = "$tahun-01-01";
                $end = "$tahun-06-30";
                $label = "Semester I Tahun $tahun";
            } else {
                $start = "$tahun-07-01";
                $end = "$tahun-12-31";
                $label = "Semester II Tahun $tahun";
            }
            break;

        case 'tahun':
            $start = "$tahun-01-01";
            $end = "$tahun-12-31";
            $label = "Tahun $tahun";
            break;

        case 'rentang':
        default:
            $start = $rentangMulai;
            $end = $rentangSelesai;
            $label = 'Periode ' . date('d M Y', strtotime($start)) . ' - ' . date('d M Y', strtotime($end));
            break;
    }

    return [$start, $end, $label];
}
