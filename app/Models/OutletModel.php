<?php

namespace App\Models;

use CodeIgniter\Model;

class OutletModel extends Model
{
    protected $table = 'outlet';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama_outlet', 'alamat'];
}
