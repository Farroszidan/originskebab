<?php

namespace App\Models;

use CodeIgniter\Model;

class VarianMenuModel extends Model
{
    protected $table = 'varian_menu';
    protected $primaryKey = 'id';
    protected $allowedFields = ['kategori', 'nama_menu', 'kode_barang'];
}
