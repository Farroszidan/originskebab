<?php

namespace App\Models;

use CodeIgniter\Model;

class LogPersediaanHarianModel extends Model
{
    protected $table = 'log_persediaan_harian';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'outlet_id',
        'kode_bahan',
        'tanggal',
        'stok_awal',
        'masuk',
        'keluar',
        'stok_akhir'
    ];
    public $timestamps = false;

    public function getRekap($outlet_id, $tanggal)
    {
        return $this->where('outlet_id', $outlet_id)
            ->where('tanggal', $tanggal)
            ->findAll();
    }

    /**
     * Update or insert log harian untuk bahan dan outlet tertentu.
     * Akan otomatis menghitung stok_akhir = stok_awal + masuk - keluar.
     */
    public function upsert(array $data)
    {
        // Validasi tanggal
        if (!isset($data['tanggal']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['tanggal'])) {
            throw new \InvalidArgumentException('Tanggal tidak valid');
        }

        // Ambil data eksisting (jika ada) untuk outlet-bahan-tanggal
        $existing = $this->where([
            'outlet_id'   => $data['outlet_id'],
            'kode_bahan'  => $data['kode_bahan'],
            'tanggal'     => $data['tanggal']
        ])->first();

        // Jika tidak ada, cari stok akhir dari hari sebelumnya sebagai stok awal
        if (!$existing) {
            $yesterday = date('Y-m-d', strtotime($data['tanggal'] . ' -1 day'));

            $prev = $this->where([
                'outlet_id'  => $data['outlet_id'],
                'kode_bahan' => $data['kode_bahan'],
                'tanggal'    => $yesterday
            ])->first();

            $stokAwal = $prev ? $prev['stok_akhir'] : 0;

            $new = [
                'outlet_id'   => $data['outlet_id'],
                'kode_bahan'  => $data['kode_bahan'],
                'tanggal'     => $data['tanggal'],
                'stok_awal'   => $stokAwal,
                'masuk'       => $data['masuk'] ?? 0,
                'keluar'      => $data['keluar'] ?? 0,
            ];
            $new['stok_akhir'] = $new['stok_awal'] + $new['masuk'] - $new['keluar'];

            return $this->insert($new);
        } else {
            // Update masuk/keluar jika sudah ada record di tanggal tersebut
            $update = [
                'stok_awal'   => $existing['stok_awal'],
                'masuk'       => ($data['masuk'] ?? 0) + $existing['masuk'],
                'keluar'      => ($data['keluar'] ?? 0) + $existing['keluar'],
            ];
            $update['stok_akhir'] = $update['stok_awal'] + $update['masuk'] - $update['keluar'];

            return $this->update($existing['id'], $update);
        }
    }
}
