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
        return $this->select('komposisi_bahan_bsj.*, bahan.nama AS nama_bahan, bahan.kategori, bahan.harga_satuan, bahan.kode AS kode_bahan')
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
    public function getKomposisiWithNamaBahan($id_bsj)
    {
        return $this->db->table('komposisi_bahan_bsj kb')
            ->select('kb.id_bahan, kb.jumlah, b.nama_bahan, b.satuan')
            ->join('bahan b', 'b.id = kb.id_bahan')
            ->where('kb.id_bsj', $id_bsj)
            ->get()->getResultArray();
    }
}
