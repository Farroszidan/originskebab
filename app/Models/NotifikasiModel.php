<?php

namespace App\Models;

use CodeIgniter\Model;

class NotifikasiModel extends Model
{
    protected $table      = 'notifikasi';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'tipe',
        'relasi_id',
        'user_id',     // penerima notifikasi
        'judul',
        'isi',         // <--- tambahkan ini
        'tujuan_role', // <--- tambahkan ini
        'outlet_id', // <--- tambahkan ini
        'dibaca',
        'created_at',
    ];
    protected $useTimestamps = false;

    public function getLink($n)
    {
        if ($n['tipe'] === 'permintaan' && $n['permintaan_id']) {
            return base_url('permintaan/detail/' . $n['permintaan_id']);
        } elseif ($n['tipe'] === 'pengajuan' && $n['pengajuan_id']) {
            return base_url('pengajuan/detail/' . $n['pengajuan_id']);
        } elseif ($n['tipe'] === 'pengiriman' && $n['pengiriman_id']) {
            return base_url('pengiriman/detail/' . $n['pengiriman_id']);
        } elseif ($n['tipe'] === 'bukti_transfer' && $n['bukti_transfer_id']) {
            return base_url('transfer/detail/' . $n['bukti_transfer_id']);
        } elseif ($n['tipe'] === 'bukti_pembelian' && $n['bukti_pembelian_id']) {  // tambah case ini
            return base_url('pembelian/detail/' . $n['bukti_pembelian_id']);
        }
        return '#';
    }
}
