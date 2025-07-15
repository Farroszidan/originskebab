<?php

namespace App\Models;

use CodeIgniter\Model;

class LogPersediaanMasukModel extends Model
{
    protected $table = 'log_persediaan_masuk';
    protected $primaryKey = 'id';
    protected $allowedFields = ['outlet_id', 'kode_bahan', 'jumlah', 'tanggal', 'created_at'];
    protected $useTimestamps = false;
}
