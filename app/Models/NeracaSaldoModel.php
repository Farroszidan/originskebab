<?php

namespace App\Models;

use CodeIgniter\Model;

class NeracaSaldoModel extends Model
{
    protected $table = 'neraca_saldo';
    protected $primaryKey = 'id';
    protected $allowedFields = ['kode_akun', 'nama_akun', 'debet', 'kredit'];

    public function getAll()
    {
        return $this->findAll();
    }
}
