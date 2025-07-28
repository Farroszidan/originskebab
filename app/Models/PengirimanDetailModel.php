<?php

namespace App\Models;

use CodeIgniter\Model;

class PengirimanDetailModel extends Model
{
    protected $table = 'pengiriman_detail';
    protected $primaryKey = 'id';
    protected $allowedFields = ['pengiriman_id', 'nama_barang', 'jumlah', 'satuan', 'tipe', 'created_at', 'updated_at'];
    public $timestamps = true;
}
