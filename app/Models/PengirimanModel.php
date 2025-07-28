<?php

namespace App\Models;

use CodeIgniter\Model;

class PengirimanModel extends Model
{
    protected $table      = 'pengiriman';
    protected $primaryKey = 'id';

    protected $allowedFields = ['tanggal', 'user_id', 'outlet_id', 'catatan', 'status'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
