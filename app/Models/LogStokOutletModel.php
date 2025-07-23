<?php

namespace App\Models;

use CodeIgniter\Model;

class LogStokOutletModel extends Model
{
    protected $table = 'logstokoutlet';
    protected $primaryKey = 'id';
    protected $allowedFields = ['outlet_id', 'bahan_id', 'nama_bahan', 'tanggal', 'tipe', 'jumlah', 'keterangan', 'created_at'];
    protected $useTimestamps = false;

    public function getHPPData($outlet_id, $tanggal_awal, $tanggal_akhir)
    {
        return $this->select('bahan_id, nama_bahan')
            ->selectSum("CASE WHEN tipe = 'masuk' AND tanggal < '$tanggal_awal' THEN jumlah ELSE 0 END", 'stok_awal')
            ->selectSum("CASE WHEN tipe = 'masuk' AND tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' THEN jumlah ELSE 0 END", 'stok_masuk')
            ->selectSum("CASE WHEN tipe = 'masuk' THEN jumlah ELSE 0 END", 'total_masuk')
            ->selectSum("CASE WHEN tipe = 'keluar' THEN jumlah ELSE 0 END", 'total_keluar')
            ->where('outlet_id', $outlet_id)
            ->groupBy('bahan_id, nama_bahan')
            ->findAll();
    }
}
