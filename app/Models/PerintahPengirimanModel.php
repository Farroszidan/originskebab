<?php

namespace App\Models;

use CodeIgniter\Model;

class PerintahPengirimanModel extends Model
{
    protected $table = 'perintah_pengiriman';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tanggal',
        'user_id',
        'keterangan',
        'created_at'
    ];
    public $timestamps = false;
}
