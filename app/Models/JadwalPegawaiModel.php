<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalPegawaiModel extends Model
{
    protected $table            = 'jadwal_pegawai';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'user_id',
        'outlet_id',
        'shift_id',
        'tanggal',
        'status_absen',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;

    // Ambil jadwal lengkap dengan join user dan shift
    public function getJadwalLengkap($outlet_id = null)
    {
        $builder = $this->db->table($this->table);
        $builder->select('jadwal_pegawai.*, users.fullname as nama_pegawai, shift_kerja.jam_mulai, shift_kerja.jam_selesai');
        $builder->join('users', 'users.id = jadwal_pegawai.user_id');
        $builder->join('shift_kerja', 'shift_kerja.id = jadwal_pegawai.shift_id');
        if ($outlet_id !== null) {
            $builder->where('jadwal_pegawai.outlet_id', $outlet_id);
        }
        $builder->orderBy('tanggal', 'ASC');
        return $builder->get()->getResultArray();
    }
}
