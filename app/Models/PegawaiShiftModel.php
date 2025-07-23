<?php

namespace App\Models;

use CodeIgniter\Model;

class PegawaiShiftModel extends Model
{
    protected $table = 'pegawai_shift';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'shift_id', 'tanggal', 'jam_mulai', 'jam_selesai', 'foto_absensi'];

    public function getShiftByOutletAndDate($outletId, $tanggal)
    {
        return $this->db->table('pegawai_shift')
            ->select('pegawai_shift.*, users.outlet_id, users.username') // ambil info tambahan
            ->join('users', 'users.id = pegawai_shift.user_id')
            ->where('users.outlet_id', $outletId)
            ->where('pegawai_shift.tanggal', $tanggal)
            ->get()
            ->getResultArray();
    }
}
