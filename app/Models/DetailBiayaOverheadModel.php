<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailBiayaOverheadModel extends Model
{
    protected $table = 'detail_biaya_overhead';
    protected $primaryKey = 'id';
    protected $allowedFields = ['biaya_overhead_id', 'nama_biaya', 'jumlah_biaya'];
    protected $useTimestamps = false;
}
