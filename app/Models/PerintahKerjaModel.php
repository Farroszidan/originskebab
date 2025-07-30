<?php

namespace App\Models;

use CodeIgniter\Model;

class PerintahKerjaModel extends Model
{
    protected $table = 'perintah_kerja';
    protected $primaryKey = 'id';

    // ✅ Aktifkan fitur timestamps
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    // ✅ Pastikan semua field selain created_at di sini
    protected $allowedFields = [
        'admin_id',
        'tanggal',
        'tipe',
        'nama',
        'jumlah',
        'satuan',
        'outlet_id'
    ];

    public function getAll()
    {
        return $this->orderBy('tanggal', 'DESC')->findAll();
    }

    public function getBSJByAdminId($adminId)
    {
        return $this->where('admin_id', $adminId)
            ->where('tipe', 'BSJ')
            ->findAll();
    }
}
