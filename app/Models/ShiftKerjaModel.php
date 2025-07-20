<?php

namespace App\Models;

use CodeIgniter\Model;

class ShiftKerjaModel extends Model
{
    protected $table = 'shift_kerja';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama_shift', 'jam_mulai', 'jam_selesai'];

    public function getShiftAktifSekarang()
    {
        $now = date('H:i:s');

        return $this->where(function ($builder) use ($now) {
            // 1. Shift normal (tidak melewati tengah malam)
            $builder->where("jam_mulai <=", $now)
                ->where("jam_selesai >=", $now);
        })->orWhere(function ($builder) use ($now) {
            // 2. Shift malam (melewati tengah malam)
            $builder->where("jam_mulai >", "jam_selesai")
                ->where(function ($b) use ($now) {
                    $b->where("jam_mulai <=", $now)
                        ->orWhere("jam_selesai >=", $now);
                });
        })->first();
    }
}
