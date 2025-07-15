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

    public function upsert(array $data)
    {
        // Validasi minimal: pastikan tanggal adalah format Y-m-d
        if (!isset($data['tanggal']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['tanggal'])) {
            throw new \InvalidArgumentException('Tanggal tidak valid');
        }

        $existing = $this->where([
            'outlet_id' => $data['outlet_id'],
            'kode_bahan' => $data['kode_bahan'],
            'tanggal' => $data['tanggal']
        ])->first();

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert($data);
        }
    }
}
