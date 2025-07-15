<?php

namespace App\Models;

use CodeIgniter\Model;

class PermintaanDetailModel extends Model
{
    protected $table      = 'permintaan_detail';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'permintaan_id',
        'tipe',
        'kode_bahan',
        'nama',
        'jumlah',
        'satuan',
    ];

    protected $useTimestamps = false; // Nonaktif karena tidak ada kolom created_at di tabel detail
}
