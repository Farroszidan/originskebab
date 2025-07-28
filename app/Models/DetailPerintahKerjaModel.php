<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailPerintahKerjaModel extends Model
{
    protected $table            = 'detail_perintah_kerja';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['perintah_kerja_id', 'nama', 'kategori', 'jumlah', 'satuan', 'pembulatan'];

    public function getByPerintahId($id)
    {
        return $this->where('perintah_kerja_id', $id)->findAll();
    }
}
