<?php

namespace App\Models;

use CodeIgniter\Model;

class BiayaOverheadModel extends Model
{
    protected $table = 'biaya_overhead';
    protected $primaryKey = 'id';
    // Tambahkan field jenis_bsj dan biaya agar bisa menyimpan info tambahan
    protected $allowedFields = ['nama', 'jenis_bsj', 'biaya'];
    protected $useTimestamps = true;
}
