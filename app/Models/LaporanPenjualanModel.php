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
        'total_penjualan',
        'total_pengeluaran',
        'rincian_penjualan',
        'rincian_pengeluaran',
        'created_at',
        'updated_at'
    ];
}
