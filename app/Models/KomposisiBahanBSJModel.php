<?php

namespace App\Models;

use CodeIgniter\Model;

class KomposisiBahanBSJModel extends Model
{
    protected $table            = 'komposisi_bahan_bsj';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['id_bsj', 'id_bahan', 'jumlah'];
    protected $useTimestamps    = false;

    // Optional: method untuk ambil komposisi lengkap dengan nama bahan
    public function getKomposisiLengkap()
    {
        return $this->select('komposisi_bahan_bsj.*, bahan.nama AS nama_bahan, bahan.kategori, bahan.harga_satuan')
            ->join('bahan', 'bahan.id = komposisi_bahan_bsj.id_bahan')
            ->findAll();
    }

    public function getKomposisiByBSJ($id_bsj)
    {
        return $this->select('komposisi_bahan_bsj.*, bahan.nama AS nama_bahan, bahan.kategori, bahan.harga_satuan')
            ->join('bahan', 'bahan.id = komposisi_bahan_bsj.id_bahan')
            ->where('komposisi_bahan_bsj.id_bsj', $id_bsj)
            ->findAll();
    }
}
