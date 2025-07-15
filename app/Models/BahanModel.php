<?php

namespace App\Models;

use CodeIgniter\Model;

class BahanModel extends Model
{
    protected $table = 'bahan';
    protected $primaryKey = 'id';
    protected $allowedFields = ['kode', 'nama', 'kategori', 'jenis', 'stok', 'satuan', 'harga_satuan'];
    protected $useTimestamps = true;
}
