<?php

namespace App\Models;

use CodeIgniter\Model;

class PembelianModel extends Model
{
    protected $table = 'pembelian';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'tanggal',
        'perintah_kerja_id',
        'total_harga',
        'pemasok_id',
        'tipe_pembayaran',
        'bukti_transaksi',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
}
