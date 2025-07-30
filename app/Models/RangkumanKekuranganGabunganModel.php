<?php

namespace App\Models;

use CodeIgniter\Model;

class RangkumanKekuranganGabunganModel extends Model
{
    protected $table = 'rangkuman_kekurangan_gabungan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
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
