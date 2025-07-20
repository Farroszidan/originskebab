<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiBahanModel extends Model
{
    protected $table            = 'transaksi_bahan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields    = [
        'id_bahan',
        'tanggal',
        'jenis',
        'jumlah',
        'satuan',
        'keterangan'
    ];

    protected $useTimestamps = true;

    // Relasi ke tabel bahan (opsional jika kamu pakai join manual di controller)
    public function getWithBahan()
    {
        return $this->select('transaksi_bahan.*, bahan.nama, bahan.kode, bahan.satuan AS satuan_bahan')
            ->join('bahan', 'bahan.id = transaksi_bahan.id_bahan');
    }
}
