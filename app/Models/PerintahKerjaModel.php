<?php

namespace App\Models;

use CodeIgniter\Model;

class PerintahKerjaModel extends Model
{
    protected $table            = 'perintah_kerja';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['tanggal', 'tipe', 'nama', 'jumlah', 'satuan', 'created_at', 'admin_id'];

    public function getAll()
    {
        return $this->orderBy('tanggal', 'DESC')->findAll();
    }
}
