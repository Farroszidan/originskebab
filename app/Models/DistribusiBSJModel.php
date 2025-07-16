<?php

namespace App\Models;

use CodeIgniter\Model;

class DistribusiBSJModel extends Model
{
    protected $table = 'distribusi_bsj';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'perintah_kerja_id',
        'outlet_id',
        'jenis_bsj',
        'jumlah',
    ];
    protected $useTimestamps = false;
}
