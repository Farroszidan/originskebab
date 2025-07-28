<?php

namespace App\Models;

use CodeIgniter\Model;

class KartuPersediaanModel extends Model
{
    protected $table = 'kartu_persediaan';
    protected $primaryKey = 'id';
    protected $allowedFields = ['bahan_id', 'tanggal', 'jenis', 'jumlah', 'harga_satuan', 'keterangan', 'created_at'];
    protected $useTimestamps = true;
}
