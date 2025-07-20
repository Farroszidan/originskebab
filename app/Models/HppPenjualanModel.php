<?php

namespace App\Models;

use CodeIgniter\Model;

class HppPenjualanModel extends Model
{
    protected $table = 'hpp_penjualan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'start_date',
        'end_date',
        'jumlah_hari',
        'total_biaya_hpp',
        'total_produksi',
        'total_btkl',
        'total_operasional',
        'total_semua_biaya',
        'hpp_per_hari',
        'hpp_per_porsi'
    ];
    protected $useTimestamps = true;
}
