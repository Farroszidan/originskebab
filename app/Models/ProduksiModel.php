<?php

namespace App\Models;

use CodeIgniter\Model;

class ProduksiModel extends Model
{
    protected $table            = 'produksi';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['bsj_id', 'tanggal', 'no_produksi', 'jumlah', 'total_biaya', 'status'];
    protected $useTimestamps    = true;
}
