<?php

namespace App\Models;

use CodeIgniter\Model;

class BTKLModel extends Model
{
    protected $table = 'btkl';
    protected $allowedFields = [
        'user_id',
        'outlet_id',
        'jumlah_shift',
        'gaji_per_shift',
        'total_gaji',
        'periode_mulai',
        'periode_selesai'
    ];
}
