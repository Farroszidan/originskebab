<?php

namespace App\Models;

use CodeIgniter\Model;

class PermintaanModel extends Model
{
    protected $table      = 'permintaan';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'tanggal',
        'catatan',
        'tujuan',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
}
