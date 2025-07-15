<?php

namespace App\Models;

use CodeIgniter\Model;

class JualModel extends Model
{
    protected $table = 'jual';
    protected $primaryKey = 'id_jual';
    protected $allowedFields = ['no_faktur', 'tgl_jual', 'jam_jual', 'nama_kasir', 'grand_total', 'dibayar', 'kembalian', 'outlet_id', 'metode_pembayaran', 'jenis_cashless'];

    public function getDaftarTransaksi($outletId = null, $startDate = null, $endDate = null)
    {
        $builder = $this->db->table('jual');
        $builder->select('jual.*, outlet.nama_outlet');
        $builder->join('outlet', 'outlet.id = jual.outlet_id', 'left');

        if ($outletId) {
            $builder->where('jual.outlet_id', $outletId);
        }

        if ($startDate) {
            $builder->where('tgl_jual >=', $startDate);
        }

        if ($endDate) {
            $builder->where('tgl_jual <=', $endDate);
        }

        return $builder->orderBy('jual.tgl_jual', 'DESC')->get()->getResultArray();
    }
}
