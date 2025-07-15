<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PermintaanModel;
use App\Models\PengirimanModel;
use App\Models\PengirimanDetailModel;
use App\Models\PengajuanModel;
use App\Models\BuktiTransferModel;
use App\Models\NotifikasiModel;
use App\Models\PermintaanDetailModel;
use Myth\Auth\Models\GroupModel;
use App\Models\BuktiPembelianModel; // Pastikan model ini sudah ada

class Transaksi extends BaseController
{
    public function index()
    {
        $outletModel = new \App\Models\OutletModel();
        $outlets = $outletModel->findAll();

        $data = [
            'tittle'  => 'SIOK | Manajemen Operasional',
            'outlets' => $outlets
        ];

        return view('manajemen_transaksi/manajemenTO', $data);
    }

    public function store()
    {
        $jenisForm   = $this->request->getPost('jenis_form');
        $tujuanRole  = $this->request->getPost('tujuan_role');
        $userId      = user_id(); // dari Myth:Auth

        if (!$jenisForm || !$tujuanRole) {
            return redirect()->back()->with('error', 'Jenis form dan tujuan wajib diisi.');
        }

        $data = [];
        $model = null;
        $userTujuanId = null;
        $userTujuan = null;
        $usersTujuan = [];

        if ($jenisForm === 'permintaan' && $tujuanRole === 'penjualan') {
            $outletId = $this->request->getPost('outlet_id');
            if (!$outletId) {
                return redirect()->back()->with('error', 'Outlet harus dipilih untuk tujuan penjualan.');
            }

            $db = \Config\Database::connect();
            $user = $db->table('users')
                ->select('users.id, users.outlet_id')
                ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
                ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
                ->where('auth_groups.name', 'penjualan')
                ->where('users.outlet_id', $outletId)
                ->get()
                ->getRowArray();

            if (!$user) {
                return redirect()->back()->with('error', 'User penjualan untuk outlet ini tidak ditemukan.');
            }

            $userTujuanId = $user['id'];
            $userTujuan = $user;
            $usersTujuan[] = $user;
        }

        switch ($jenisForm) {
            case 'permintaan':
                $model = new PermintaanModel();
                $barang = $this->request->getPost('barang');
                $total = 0;
                if (is_array($barang)) {
                    foreach ($barang as $item) {
                        $jumlah = floatval($item['jumlah'] ?? 0);
                        $harga = floatval($item['harga'] ?? 0);
                        $total += $jumlah * $harga;
                    }
                }
                $data = [
                    'tanggal'        => $this->request->getPost('tanggal'),
                    'user_id'        => $userId,
                    'user_tujuan_id' => $userTujuanId,
                    'catatan'        => $this->request->getPost('catatan'),
                    'total'          => $total,
                    'created_at'     => date('Y-m-d H:i:s'),
                ];
                break;

            case 'pengajuan':
                $model = new PengajuanModel();
                $data = [
                    'tanggal'         => $this->request->getPost('tanggal'),
                    'user_id'         => $userId,
                    'jenis_pengajuan' => $this->request->getPost('jenis_pengajuan'),
                    'keterangan'      => $this->request->getPost('keterangan'),
                    'created_at'      => date('Y-m-d H:i:s'),
                ];
                break;

            case 'pengiriman':
                $model = new PengirimanModel();
                $jumlah = $this->request->getPost('jumlah');
                $data = [
                    'tanggal'    => $this->request->getPost('tanggal'),
                    'user_id'    => $userId,
                    'catatan'    => $this->request->getPost('catatan'),
                    'detail'     => json_encode($jumlah),
                    'lainnya'    => $this->request->getPost('lainnya'),
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                break;

            case 'bukti_transfer':
                $model = new BuktiTransferModel();
                $gambar = $this->request->getFile('gambar');
                if (!$gambar || !$gambar->isValid() || $gambar->hasMoved() || $gambar->getSize() === 0) {
                    return redirect()->back()->withInput()->with('error', 'Gambar bukti transfer tidak valid atau tidak ada.');
                }
                if (strpos($gambar->getMimeType(), 'image/') !== 0) {
                    return redirect()->back()->withInput()->with('error', 'File harus berupa gambar.');
                }
                $uploadPath = FCPATH . 'uploads/bukti_transfer';
                if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);
                $namaFile = $gambar->getRandomName();
                $gambar->move($uploadPath, $namaFile);
                $data = [
                    'tanggal'    => $this->request->getPost('tanggal'),
                    'user_id'    => $userId,
                    'gambar'     => $namaFile,
                    'catatan'    => $this->request->getPost('catatan'),
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                break;

            case 'bukti_pembelian':
                $model = new BuktiPembelianModel();
                $gambar = $this->request->getFile('gambar');
                if (!$gambar || !$gambar->isValid() || $gambar->hasMoved() || $gambar->getSize() === 0) {
                    return redirect()->back()->withInput()->with('error', 'Gambar bukti pembelian tidak valid atau tidak ada.');
                }
                $ext = strtolower($gambar->getClientExtension());
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                    return redirect()->back()->withInput()->with('error', 'Ekstensi file tidak diperbolehkan.');
                }
                $uploadPath = FCPATH . 'uploads/bukti_pembelian';
                if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);
                $namaFile = uniqid('bukti_', true) . '.' . $ext;
                $gambar->move($uploadPath, $namaFile);

                $barang = $this->request->getPost('barang');
                $total = 0;
                if (is_array($barang)) {
                    foreach ($barang as $item) {
                        $jumlah = floatval($item['jumlah'] ?? 0);
                        $harga = floatval($item['harga'] ?? 0);
                        $total += $jumlah * $harga;
                    }
                }

                $userModel = new \App\Models\UserModel();
                $userInfo = $userModel->find($userId);
                $namaUser = $userInfo->fullname ?? $userInfo->username ?? 'unknown';
                $outletId = $userInfo->outlet_id ?? null;

                $db = \Config\Database::connect();
                $usersTujuan = $db->table('users')
                    ->select('users.id, users.outlet_id')
                    ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
                    ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
                    ->where('auth_groups.name', $tujuanRole)
                    ->get()
                    ->getResultArray();

                $data = [
                    'tanggal'    => $this->request->getPost('tanggal'),
                    'user_id'    => $userId,
                    'nama'       => $namaUser,
                    'outlet_id'  => $outletId,
                    'gambar'     => $namaFile,
                    'keterangan' => $this->request->getPost('keterangan'),
                    'total'      => $total,
                    'detail'     => json_encode($barang),
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                break;

            default:
                return redirect()->back()->with('error', 'Jenis form tidak dikenal.');
        }

        if ($model && $data) {
            if ($model->insert($data)) {
                $insertId = $model->getInsertID();

                // Simpan detail permintaan jika ada
                if ($jenisForm === 'permintaan' && is_array($barang)) {
                    $permintaanDetailModel = new \App\Models\PermintaanDetailModel();
                    foreach ($barang as $item) {
                        $permintaanDetailModel->insert([
                            'permintaan_id' => $insertId,
                            'nama_barang'   => $item['nama'],
                            'jumlah'        => $item['jumlah'],
                            'harga_satuan'  => $item['harga'] ?? null,
                            'created_at'    => date('Y-m-d H:i:s'),
                        ]);
                    }
                }

                // ✅ Simpan detail pengiriman jika ada
                if ($jenisForm === 'pengiriman' && is_array($jumlah)) {
                    $pengirimanDetailModel = new \App\Models\PengirimanDetailModel();
                    foreach ($jumlah as $namaBahan => $qty) {
                        if (floatval($qty) > 0) {
                            $pengirimanDetailModel->insert([
                                'pengiriman_id' => $insertId,
                                'nama_bahan'    => $namaBahan,
                                'jumlah'        => $qty,
                                'satuan'        => 'pcs', // ubah jika perlu
                                'created_at'    => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                }

                // Kirim notifikasi
                $groupModel = new \Myth\Auth\Models\GroupModel();
                $builder = $groupModel->db->table('auth_groups_users')
                    ->select('auth_groups.name')
                    ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
                    ->where('auth_groups_users.user_id', $userId)
                    ->get();
                $role = $builder->getFirstRow('array');
                $pengirimRole = $role['name'] ?? 'unknown';
                $isi = "[{$jenisForm} dari {$pengirimRole}]";

                $notifModel = new \App\Models\NotifikasiModel();
                foreach ($usersTujuan as $user) {
                    $notifModel->insert([
                        'tipe'        => $jenisForm,
                        'relasi_id'   => $insertId,
                        'user_id'     => $user['id'],
                        'isi'         => $isi,
                        'tujuan_role' => $tujuanRole,
                        'outlet_id'   => ($tujuanRole === 'penjualan') ? ($user['outlet_id'] ?? null) : null,
                        'dibaca'      => 0,
                        'created_at'  => date('Y-m-d H:i:s'),
                    ]);
                }

                return redirect()->to('/manajemen-transaksi')->with('success', 'Form berhasil dikirim.');
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data ' . $jenisForm);
            }
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses data.');
    }



    public function detail($jenis, $id)
    {
        $notifikasiModel = new \App\Models\NotifikasiModel();
        $notifikasiModel->where('tipe', $jenis)
            ->where('relasi_id', $id)
            ->where('user_id', user_id())
            ->set(['dibaca' => 1])
            ->update();

        $model = null;
        $detail = [];

        switch ($jenis) {
            case 'permintaan':
                $model = new \App\Models\PermintaanModel();
                $detailModel = new \App\Models\PermintaanDetailModel();
                $detail = $detailModel->where('permintaan_id', $id)->findAll();
                break;

            case 'pengajuan':
                $model = new \App\Models\PengajuanModel();
                break;

            case 'pengiriman':
                $model = new \App\Models\PengirimanModel();
                break;

            case 'bukti_transfer':
                $model = new \App\Models\BuktiTransferModel();
                break;

            case 'bukti_pembelian':
                $model = new \App\Models\BuktiPembelianModel();
                break;

            default:
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Jenis tidak dikenali.");
        }

        $data = $model->find($id);

        if (!$data) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Data tidak ditemukan.");
        }

        return view('transaksi/detail', [
            'jenis' => $jenis,
            'data'  => $data,
            'detail' => $detail, // ← ini kita tambahkan
            'tittle' => 'SIOK | Detail ' . ucfirst(str_replace('_', ' ', $jenis))
        ]);
    }
}
