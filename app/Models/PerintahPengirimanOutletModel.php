<?php

namespace App\Models;

use CodeIgniter\Model;

class PerintahPengirimanOutletModel extends Model
{
    protected $table = 'perintah_pengiriman_outlet';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'perintah_pengiriman_id',
        'outlet_id',
        'keterangan'
    ];
    public $timestamps = false;
}
