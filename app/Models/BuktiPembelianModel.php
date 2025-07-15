<?php

namespace App\Models;

use CodeIgniter\Model;

class BuktiPembelianModel extends Model
{
    protected $table      = 'bukti_pembelian';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'tanggal',
        'user_id',
        'nama_barang',
        'jumlah',
        'harga',
        'gambar',
        'keterangan',
        'total',
        'detail',
        'created_at',
        'updated_at',
        'nama',
        'outlet_id',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
