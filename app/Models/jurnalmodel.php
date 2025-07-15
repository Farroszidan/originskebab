<?php

namespace App\Models;

use CodeIgniter\Model;

class JurnalModel extends Model
{
    protected $table = 'jurnal_umum';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tanggal', 'akun_id', 'debit', 'kredit', 'keterangan'];

    public function getSaldoAkunBulanIni()
    {
        $builder = $this->db->table('akun');
        $builder->select('akun.id, akun.kode_akun, akun.nama_akun, akun.jenis_akun, akun.tipe, 
                      SUM(jurnal_umum.debit) as total_debit, 
                      SUM(jurnal_umum.kredit) as total_kredit');
        $builder->join('jurnal_umum', 'jurnal_umum.akun_id = akun.id', 'left');
        $builder->where('MONTH(jurnal_umum.tanggal)', date('m'));
        $builder->where('YEAR(jurnal_umum.tanggal)', date('Y'));
        $builder->groupBy('akun.id');

        return $builder->get()->getResultArray();
    }
}
