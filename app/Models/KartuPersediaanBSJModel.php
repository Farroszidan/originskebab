<?php

namespace App\Models;

use CodeIgniter\Model;

class KartuPersediaanBSJModel extends Model
{
    protected $table = 'kartu_persediaan_bsj';
    protected $primaryKey = 'id';
    protected $allowedFields = ['bsj_id', 'tanggal', 'jenis', 'jumlah', 'harga_satuan', 'keterangan', 'created_at'];
    protected $useTimestamps = true;
}
