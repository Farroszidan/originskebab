<?php

namespace App\Models;

use CodeIgniter\Model;

class PerintahPengirimanDetailModel extends Model
{
    protected $table = 'perintah_pengiriman_detail';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'perintah_pengiriman_outlet_id',
        'tipe',
        'barang_id',
        'nama_barang',
        'jumlah',
        'satuan'
    ];
    public $timestamps = false;
}
