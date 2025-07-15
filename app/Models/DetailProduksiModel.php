<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailProduksiModel extends Model
{
    protected $table            = 'detail_produksi';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'produksi_id',
        'kategori',
        'bahan_id',
        'nama_biaya',
        'jumlah',
        'harga_satuan',
        'subtotal'
    ];
}
