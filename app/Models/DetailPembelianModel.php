<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailPembelianModel extends Model
{
    protected $table      = 'detail_pembelian';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'pembelian_id',
        'bahan_id',
        'jumlah',
        'harga_satuan',
        'subtotal'
    ];

    protected $useTimestamps = true;
}
