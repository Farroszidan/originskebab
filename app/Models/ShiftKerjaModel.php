<?php

namespace App\Models;

use CodeIgniter\Model;

class ShiftKerjaModel extends Model
{
    protected $table = 'shift_kerja';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama_shift', 'jam_mulai', 'jam_selesai'];
}
