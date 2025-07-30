<?php

namespace App\Models;

use CodeIgniter\Model;

class KekuranganBahanModel extends Model
{
    protected $table = 'persediaan_outlet';

    /**
     * Hitung kekurangan bahan per outlet berdasar perintah kerja id
     * @param int $perintahKerjaId
     * @return array
     */
    public function getKekuranganByPerintahKerjaId(int $perintahKerjaId)
    {
        $db = \Config\Database::connect();

        // 1. Ambil total kebutuhan bahan dari detail perintah kerja
        // Misal tabel detail_perintah_kerja dengan kolom: perintah_kerja_id, bahan_id, jumlah, satuan
        $queryKebutuhan = $db->table('detail_perintah_kerja')
            ->select('bahan_id, SUM(jumlah) as total_jumlah')
            ->where('perintah_kerja_id', $perintahKerjaId)
            ->groupBy('bahan_id')
            ->get()
            ->getResultArray();

        // Mapping bahan_id => total_jumlah kebutuhan
        $kebutuhanMap = [];
        foreach ($queryKebutuhan as $row) {
            $kebutuhanMap[$row['bahan_id']] = $row['total_jumlah'];
        }

        // 2. Ambil semua outlet yang aktif (atau sesuai kebutuhan)
        $outlets = $db->table('outlet')
            ->select('id, nama_outlet')
            ->get()
            ->getResultArray();

        $result = [];

        foreach ($outlets as $outlet) {
            // 3. Ambil stok bahan di outlet ini
            $stokData = $db->table('persediaan_outlet')
                ->select('bahan_id, jumlah, satuan')
                ->where('outlet_id', $outlet['id'])
                ->get()
                ->getResultArray();

            // Map stok bahan_id => jumlah
            $stokMap = [];
            foreach ($stokData as $stok) {
                $stokMap[$stok['bahan_id']] = $stok['jumlah'];
            }

            $items = [];

            // 4. Hitung kekurangan per bahan
            foreach ($kebutuhanMap as $bahanId => $totalKebutuhan) {
                $stok = $stokMap[$bahanId] ?? 0;
                $kekurangan = $totalKebutuhan - $stok;
                if ($kekurangan > 0) {
                    // Ambil nama dan satuan bahan dari tabel bahan
                    $bahanInfo = $db->table('bahan')->select('nama, satuan')->where('id', $bahanId)->get()->getRowArray();

                    $items[] = [
                        'id' => $bahanId,
                        'nama' => $bahanInfo['nama'] ?? 'Unknown',
                        'jumlah' => $kekurangan,
                        'satuan' => $bahanInfo['satuan'] ?? '',
                    ];
                }
            }

            if (count($items) > 0) {
                $result[] = [
                    'outlet_id' => $outlet['id'],
                    'outlet_nama' => $outlet['nama_outlet'],
                    'items' => $items,
                ];
            }
        }

        return $result;
    }
}
