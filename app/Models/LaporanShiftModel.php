<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanShiftModel extends Model
{
    protected $table = 'laporan_shift';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tanggal', 'outlet_id', 'shift_id', 'user_id', 'total_penjualan', 'total_pengeluaran', 'keterangan_pengeluaran', 'rincian_penjualan'];
}
