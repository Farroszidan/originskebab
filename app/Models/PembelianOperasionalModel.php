<?php

namespace App\Models;

use CodeIgniter\Model;

class PembelianOperasionalModel extends Model
{
    protected $table            = 'pembelian_operasional';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['tanggal', 'outlet_id', 'total', 'bukti'];
    protected $useTimestamps    = true; // untuk created_at & updated_at

    /**
     * Ambil semua detail barang untuk 1 pembelian
     */
    public function getDetailPembelian($pembelianId)
    {
        return $this->db->table('detail_pembelian_operasional')
            ->select('nama_barang, jumlah, total')
            ->where('pembelian_id', $pembelianId)
            ->get()
            ->getResultArray();
    }
}
