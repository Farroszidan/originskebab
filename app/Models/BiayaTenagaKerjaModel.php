<?php

namespace App\Models;

use CodeIgniter\Model;

class BiayaTenagaKerjaModel extends Model
{
    protected $table = 'biaya_tenaga_kerja';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama', 'biaya'];
    protected $useTimestamps = true;
}
