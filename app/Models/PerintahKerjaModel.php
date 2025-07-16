<?php

namespace App\Models;

use CodeIgniter\Model;

class PerintahKerjaModel extends Model
{
    protected $table = 'perintah_kerja';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tanggal',
        'admin_id',
        'total_biaya',
        'status',
        'keterangan',
        'created_at',
    ];
    protected $useTimestamps = false;
}
