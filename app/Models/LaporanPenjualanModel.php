<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanPenjualanModel extends Model
{
    protected $table = 'laporan_penjualan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tanggal',
        'outlet_id',
        'shift_id',
        'total_penjualan',
        'total_pengeluaran',
        'keterangan_pengeluaran',
        'created_at'
    ];
}
