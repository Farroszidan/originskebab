<?php

namespace App\Models;

use CodeIgniter\Model;

class KasOutletModel extends Model
{
    protected $table = 'akun';
    protected $primaryKey = 'id';
    protected $allowedFields = ['kode_akun', 'nama_akun', 'jenis_akun', 'tipe', 'saldo_awal', 'kas_outlet_id'];

    public function getKasWithOutlet()
    {
        return $this->db->table('akun')
            ->select('akun.saldo_awal, kas_outlet.nama_outlet')
            ->join('kas_outlet', 'kas_outlet.id = akun.kas_outlet_id')
            ->get()
            ->getResultArray(); // <- WAJIB pakai ini kalau di view pakai $kas['...']
    }
}
