<?php

namespace App\Models;

use CodeIgniter\Model;

class LabaRugiModel extends Model
{
    protected $table = 'jurnal_umum';
    protected $allowedFields = ['kode_akun', 'nama_akun', 'debet', 'kredit', 'jenis_akun'];

    public function getLabaRugi()
    {
        return $this->orderBy('tanggal', 'ASC')->findAll();
    }
}
