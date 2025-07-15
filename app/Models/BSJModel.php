<?php

namespace App\Models;

use CodeIgniter\Model;

class BSJModel extends Model
{
    protected $table = 'bsj';
    protected $primaryKey = 'id';
    protected $allowedFields = ['kode', 'nama', 'satuan', 'stok', 'kategori'];
    protected $useTimestamps = true;
}
