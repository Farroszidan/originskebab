<?php

namespace App\Models;

use CodeIgniter\Model;

class RangkumanKekuranganPerOutletModel extends Model
{
    protected $table = 'rangkuman_kekurangan_per_outlet';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'outlet',
        'kode_bahan',
        'tipe',
        'nama_barang',
        'satuan',
        'kekurangan',
        'pembulatan',
        'tanggal',
        'batch_id'
    ];
}
