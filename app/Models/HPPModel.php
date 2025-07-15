<?php

namespace App\Models;

use CodeIgniter\Model;

class HPPModel extends Model
{
    protected $table = 'hpp_bsj';
    protected $primaryKey = 'id';
    protected $allowedFields = ['produksi_id', 'kode_produksi', 'total_biaya', 'jumlah_produksi', 'hpp_per_unit', 'keterangan'];
    protected $useTimestamps = true;
}
