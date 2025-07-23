<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotifikasiModel;

class Notifikasi extends BaseController
{
    protected $notifikasiModel;

    public function __construct()
    {
        $this->notifikasiModel = new NotifikasiModel();
    }

    // Tampilkan semua notifikasi
    public function pesan_masuk()
    {
        $notifikasi = $this->notifikasiModel
            ->where('user_id', user_id())
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('notifikasi/pesan_masuk', [
            'tittle' => 'SIOK | Pesan Masuk',
            'notifikasi' => $notifikasi
        ]);
    }

    // Tandai sebagai sudah dibaca dan redirect ke detail
    public function baca($id)
    {
        $notif = $this->notifikasiModel->find($id);

        if (!$notif || $notif['user_id'] != user_id()) {
            return redirect()->to('/notifikasi/pesan_masuk')->with('error', 'Notifikasi tidak ditemukan.');
        }

        // Tandai sebagai dibaca
        if (!$notif['dibaca']) {
            $this->notifikasiModel->update($id, ['dibaca' => 1]);
        }

        $tipe = $notif['tipe'];
        $relasiId = $notif['relasi_id'];

        if (empty($relasiId)) {
            return redirect()->to('/notifikasi/pesan_masuk')->with('info', 'Notifikasi tidak memiliki data detail.');
        }

        return redirect()->to("/transaksi/detail/{$tipe}/{$relasiId}");
    }

    // Ambil jumlah notifikasi unread untuk AJAX
    public function ajax()
    {
        $jumlah = $this->notifikasiModel
            ->where('user_id', user_id())
            ->where('dibaca', 0)
            ->countAllResults();

        return $this->response->setJSON(['jumlah' => $jumlah]);
    }

    // Tandai semua pesan sebagai dibaca
    public function tandai_semua()
    {
        $this->notifikasiModel
            ->where('user_id', user_id())
            ->where('dibaca', 0)
            ->set(['dibaca' => 1])
            ->update();

        return redirect()->to(base_url('notifikasi/pesan_masuk'));
    }

    public function detail($id)
    {
        $notif = $this->notifikasiModel->find($id);

        if (!$notif || $notif['user_id'] != user_id()) {
            return redirect()->to('/notifikasi/pesan_masuk')->with('error', 'Notifikasi tidak ditemukan');
        }

        if ($notif['dibaca'] == 0) {
            $this->notifikasiModel->update($id, ['dibaca' => 1]);
        }

        $daftarProduksi = [];
        $rangkumanBahan = [];
        $data = [];

        if ($notif['tipe'] === 'perintah_kerja' && !empty($notif['relasi_id'])) {
            $perintahKerjaModel = new \App\Models\PerintahKerjaModel();
            $detailModel = new \App\Models\DetailPerintahKerjaModel();
            $perintahKerja = $perintahKerjaModel->find($notif['relasi_id']);

            if ($perintahKerja) {
                // Ambil semua produksi dengan admin_id yang sama
                $daftarProduksi = $perintahKerjaModel->where('admin_id', $perintahKerja['admin_id'])->findAll();
                // Ambil rangkuman bahan dari salah satu id produksi (relasi_id)
                $rangkumanBahan = $detailModel->where('perintah_kerja_id', $notif['relasi_id'])->findAll();
                $data = [
                    'tanggal' => $perintahKerja['tanggal'] ?? '',
                    'catatan' => $perintahKerja['catatan'] ?? '',
                    'daftar_produksi' => $daftarProduksi,
                    'rangkuman_bahan' => $rangkumanBahan,
                    'jenis' => 'perintah_kerja',
                ];
            }
        } elseif ($notif['tipe'] === 'pengiriman' && !empty($notif['relasi_id'])) {
            $pengirimanModel = new \App\Models\PengirimanModel();
            $pengirimanDetailModel = new \App\Models\PengirimanDetailModel();

            $pengiriman = $pengirimanModel->find($notif['relasi_id']);
            if (!$pengiriman) {
                return redirect()->to('/notifikasi/pesan_masuk')->with('error', 'Data pengiriman tidak ditemukan');
            }

            $detail = $pengirimanDetailModel
                ->where('pengiriman_id', $notif['relasi_id'])
                ->findAll();

            $jumlahTotal = array_sum(array_column($detail, 'jumlah'));

            $data = [
                'tanggal'            => $pengiriman['tanggal'] ?? '',
                'catatan'            => $pengiriman['catatan'] ?? '',
                'jumlah'             => $jumlahTotal,
                'jenis'              => 'pengiriman',
                'outlet_id'          => $pengiriman['outlet_id'] ?? null,
                'detail_pengiriman'  => $detail, // array detail bahan
            ];
        }

        return view('notifikasi/detail', [
            'tittle'         => 'SIOK | Detail Notifikasi',
            'notifikasi'     => $notif,
            'data'           => $data,
            'jenis'          => $data['jenis'] ?? null,
        ]);
    }
}
