<?php

namespace App\Models;

use CodeIgniter\Model;

class PembelianModel extends Model
{
    protected $table = 'pembelian';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'no_nota',
        'pemasok_id',
        'tanggal',
        'bahan_id',
        'total',
        'bukti_transaksi',
        'jenis_pembelian',
        'status_barang'
    ];
    protected $useTimestamps = true;
}
