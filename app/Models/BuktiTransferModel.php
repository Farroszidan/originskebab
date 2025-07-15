<?php

namespace App\Models;

use CodeIgniter\Model;

class BuktiTransferModel extends Model
{
    protected $table      = 'bukti_transfer';
    protected $primaryKey = 'id';

    protected $allowedFields = ['tanggal', 'user_id', 'catatan', 'gambar'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
