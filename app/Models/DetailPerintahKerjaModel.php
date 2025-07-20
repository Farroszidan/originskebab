<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailPerintahKerjaModel extends Model
{
    protected $table = 'detail_perintah_kerja';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'perintah_kerja_id',
        'kode_bahan',
        'nama_bahan',
        'jenis_bsj',
        'jumlah',
        'kategori',
        'satuan',
        'harga_satuan',
        'subtotal',
    ];
    protected $useTimestamps = false;
}
