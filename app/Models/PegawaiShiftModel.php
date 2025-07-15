<?php

namespace App\Models;

use CodeIgniter\Model;

class PegawaiShiftModel extends Model
{
    protected $table = 'pegawai_shift';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'shift_id', 'tanggal', 'jam_mulai', 'jam_selesai'];

    // public function getShiftsByOutlet($outletId, $startDate, $endDate)
    // {
    //     return $this->select('users.fullname, COUNT(pegawai_shift.id) as total_shift')
    //         ->join('users', 'users.id = pegawai_shift.user_id')
    //         ->join('shift_kerja', 'shift_kerja.id = pegawai_shift.shift_id')
    //         ->where('users.outlet_id', $outletId)
    //         ->where('users.divisi', 'penjualan')
    //         ->where('tanggal >=', $startDate)
    //         ->where('tanggal <=', $endDate)
    //         ->groupBy('users.id')
    //         ->findAll();
    // }
}
