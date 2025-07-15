<?php

namespace App\Models;

use CodeIgniter\Model;

class BiayaOverheadModel extends Model
{
    protected $table = 'biaya_overhead';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama', 'biaya'];
    protected $useTimestamps = true;
}
