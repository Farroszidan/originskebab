<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailPembelianOperasionalModel extends Model
{
    protected $table            = 'detail_pembelian_operasional';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['pembelian_id', 'nama_barang', 'jumlah', 'satuan', 'total'];
}
