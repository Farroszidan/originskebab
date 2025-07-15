<?php

namespace App\Models;

use CodeIgniter\Model;

class PersediaanOutletModel extends Model
{
    protected $table = 'persediaan_outlet';
    protected $primaryKey = 'id';
    protected $allowedFields = ['outlet_id', 'kode_bahan', 'stok', 'tanggal', 'updated_at'];
    protected $useTimestamps = false;

    public function kurangiStok($outlet_id, $kode_bahan, $jumlah)
    {
        $stok = $this->where(['outlet_id' => $outlet_id, 'kode_bahan' => $kode_bahan])->first();

        if ($stok && $stok['stok'] >= $jumlah) {
            return $this->set('stok', 'stok - ' . $jumlah, false)
                ->where(['outlet_id' => $outlet_id, 'kode_bahan' => $kode_bahan])
                ->update();
        } else {
            return false;
        }
    }


    public function tambahStok($outlet_id, $kode_bahan, $jumlah, $tanggal = null)
    {
        $existing = $this->where([
            'outlet_id' => $outlet_id,
            'kode_bahan' => $kode_bahan
        ])->first();

        if ($existing) {
            return $this->where('id', $existing['id'])
                ->set('stok', 'stok + ' . (int)$jumlah, false)
                ->set('updated_at', date('Y-m-d H:i:s'))
                ->update();
        } else {
            return $this->insert([
                'outlet_id'  => $outlet_id,
                'kode_bahan' => $kode_bahan,
                'stok'       => (int)$jumlah,
                'tanggal'    => $tanggal,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function cekStok($outlet_id, $kode_bahan)
    {
        $stok = $this->where([
            'outlet_id'   => $outlet_id,
            'kode_bahan'  => $kode_bahan
        ])->first();

        return $stok ? (int)$stok['stok'] : 0;
    }
}
