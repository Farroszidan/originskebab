<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailPerintahKerjaModel extends Model
{
    protected $table = 'detail_perintah_kerja';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'perintah_kerja_id',
        'jenis_bsj',
        'jumlah',
    ];
    protected $useTimestamps = false;
}
