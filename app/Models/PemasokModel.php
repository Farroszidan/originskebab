<?php

namespace App\Models;

use CodeIgniter\Model;

class PemasokModel extends Model
{
    protected $table = 'pemasok';
    protected $primaryKey = 'id';
    protected $allowedFields = ['kode_sup', 'nama', 'alamat', 'telepon', 'kategori'];
    protected $useTimestamps = true;
}
