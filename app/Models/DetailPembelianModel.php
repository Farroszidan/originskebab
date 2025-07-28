<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailPembelianModel extends Model
{
    protected $table = 'detail_pembelian';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'pembelian_id',
        'bahan_id',
        'nama_bahan',
        'kategori',
        'jumlah',
        'satuan',
        'harga_satuan',
        'subtotal',
        'pemasok_id',
        'tipe_pembayaran',
        'bukti_transaksi'
    ];
}
