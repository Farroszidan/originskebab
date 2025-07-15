<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailJualModel extends Model
{
    protected $table = 'detail_jual';
    protected $primaryKey = 'id_detail';
    protected $allowedFields = ['id_jual', 'kode_menu', 'kategori', 'nama_menu', 'ukuran', 'harga', 'qty', 'total_harga', 'add_ons', 'extra'];

    public function getKeluarBahan($outlet_id, $kode_bahan, $tanggal)
    {
        $db = \Config\Database::connect();

        $whereJenis = '';
        $column = '';

        switch ($kode_bahan) {
            case 'BSJ01': // Kulit Kebab
                $column = 'kulit_kebab';
                break;
            case 'BSJ02': // Olahan Daging Ayam
                $column = 'daging';
                $whereJenis = "AND m.nama_menu LIKE '%Chicken%'";
                break;
            case 'BSJ03': // Olahan Daging Sapi
                $column = 'daging';
                $whereJenis = "AND m.nama_menu LIKE '%Beef%'";
                break;
            case 'BSJ04': // Sayur
                $column = 'sayur';
                break;
            default:
                return 0;
        }

        $sql = "
        SELECT SUM(dj.qty * m.{$column}) AS total
        FROM jual j
        JOIN detail_jual dj ON dj.id_jual = j.id
        JOIN menu m ON m.kode_menu = dj.kode_menu
        WHERE j.outlet_id = :outlet_id:
        AND DATE(j.tgl_jual) = :tanggal:
        $whereJenis
    ";

        $query = $db->query($sql, [
            'outlet_id' => $outlet_id,
            'tanggal' => $tanggal,
        ]);

        $row = $query->getRow();
        return (int) ($row->total ?? 0);
    }
}
