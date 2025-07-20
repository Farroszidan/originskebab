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

        // Tandai dibaca jika belum
        if ($notif['dibaca'] == 0) {
            $this->notifikasiModel->update($id, ['dibaca' => 1]);
        }

        $bsjList = [];
        $perintahKerja = null;
        if ($notif['tipe'] === 'perintah_kerja' && !empty($notif['relasi_id'])) {
            $perintahKerjaModel = new \App\Models\PerintahKerjaModel();
            $perintahKerja = $perintahKerjaModel->find($notif['relasi_id']);
            if ($perintahKerja && !empty($perintahKerja['bsj'])) {
                if (is_string($perintahKerja['bsj'])) {
                    $bsjList = json_decode($perintahKerja['bsj'], true);
                } elseif (is_array($perintahKerja['bsj'])) {
                    $bsjList = $perintahKerja['bsj'];
                }
            }
        }

        return view('notifikasi/detail', [
            'tittle' => 'SIOK | Detail Notifikasi',
            'notifikasi' => $notif,
            'bsj' => $bsjList,
            'perintahKerja' => $perintahKerja,
        ]);
    }
}
