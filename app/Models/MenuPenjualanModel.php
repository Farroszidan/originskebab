<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuPenjualanModel extends Model
{
    protected $table = 'menu'; // atau 'menu_penjualan' jika kamu pakai nama lain
    protected $primaryKey = 'id';

    protected $useTimestamps = true; // jika kamu menggunakan created_at dan updated_at

    protected $allowedFields = [
        'kode_menu',
        'kategori',
        'nama_menu',
        'harga',
        'komposisi'
    ];

    protected $returnType = 'array';
}
