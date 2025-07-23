<?php

namespace App\Controllers;

use App\Models\VarianMenuModel;
use App\Models\MenuPenjualanModel;
use App\Models\JualModel;
use App\Models\DetailJualModel;
use App\Models\OutletModel;
use App\Models\PersediaanOutletModel;
use App\Models\PegawaiShiftModel;
use App\Models\ShiftKerjaModel;
use App\Models\BTKLModel;
use Myth\Auth\Models\UserModel;
use App\Models\BSJModel;
use App\Models\PembelianModel;
use App\Models\PemasokModel;
use App\Models\BahanModel;
use App\Models\DetailPembelianModel;
use App\Models\BiayaOverheadModel;
use App\Models\BiayaTenagaKerjaModel;
use App\Models\KomposisiBahanBSJModel;
use App\Models\KasOutletModel;
use CodeIgniter\I18n\Time;
use App\Models\PerintahKerjaModel;
use App\Models\DetailPerintahKerjaModel;

class Admin extends BaseController
{
    protected $db, $builder, $jualModel, $detailJualModel, $session, $auth, $bsjModel, $komposisiModel, $perintahModel, $detailModel, $bahanModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('users');
        $this->jualModel = new JualModel();
        $this->detailJualModel = new DetailJualModel();
        $this->session = session();
        $this->auth = service('authentication');
        $this->bsjModel = new BSJModel();
        $this->perintahModel = new PerintahKerjaModel();
        $this->detailModel = new DetailPerintahKerjaModel();
        $this->komposisiModel = new KomposisiBahanBSJModel();
        $this->bahanModel = new BahanModel();
    }
    public function index()
    {
        // Pastikan hanya admin yang bisa akses
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }
        $data['tittle'] = 'SIOK | Dashboard';
        return view('admin/dashboard', $data);
    }

    public function dashboard()
    {
        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end') ?? date('Y-m-t');

        $jualModel = new JualModel();
        $outletModel = new OutletModel();
        $kasModel = new KasOutletModel();

        $outlets = $outletModel->findAll();
        $penjualanPerOutlet = [];
        $totalSeluruhOutlet = 0;

        foreach ($outlets as $outlet) {
            $total = $jualModel
                ->where('outlet_id', $outlet['id'])
                ->where('tgl_jual >=', $start)
                ->where('tgl_jual <=', $end)
                ->selectSum('grand_total')
                ->first();

            $totalOutlet = $total['grand_total'] ?? 0;
            $penjualanPerOutlet[] = [
                'nama_outlet' => $outlet['nama_outlet'],
                'total'       => $totalOutlet
            ];

            $totalSeluruhOutlet += $totalOutlet;
        }

        $kas_outlet = $kasModel->getKasWithOutlet();

        $data = [
            'tittle'               => 'Dashboard Admin',
            'role'                 => 'admin',
            'start'                => $start,
            'end'                  => $end,
            'penjualanPerOutlet'   => $penjualanPerOutlet,
            'totalSeluruhOutlet'   => $totalSeluruhOutlet,
            'kas_outlet'           => $kas_outlet,
        ];

        return view('dashboard/index', $data);
    }


    public function userlist()
    {
        // Pastikan hanya admin yang bisa akses
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }
        $data['tittle'] = 'SIOK | User Manajemen';

        $this->builder->select('users.id as userid, username, email, name');
        $this->builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');
        $this->builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');
        $query = $this->builder->get();

        $data['users'] = $query->getResult();
        return view('admin/user_manajemen', $data);
    }

    public function detail_manajemen($id = 0)
    {
        // Pastikan hanya admin yang bisa akses
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }
        $data['tittle'] = 'SIOK | Detail User Manajemen';


        $this->builder->select('users.id as userid, username, email,fullname, user_image, name');
        $this->builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');
        $this->builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');
        $this->builder->where('users.id', $id);
        $query = $this->builder->get();

        $data['user'] = $query->getRow();

        if (empty($data['user'])) {
            return redirect()->to('admin/usermanajemen');
        }

        return view('admin/detail_manajemen', $data);
    }

    public function pemasok()
    {
        if (!in_groups('admin')) return redirect()->to('login');

        $model = new PemasokModel();
        $data = [
            'tittle' => 'Manajemen Pemasok',
            'pemasok' => $model->findAll(),
        ];
        return view('admin/pemasok/index', $data);
    }

    public function tambahPemasok()
    {
        $model = new PemasokModel();
        $model->save([
            'kode_sup' => $this->request->getPost('kode_sup'),
            'nama'     => $this->request->getPost('nama'),
            'kategori' => $this->request->getPost('kategori'),
            'alamat'   => $this->request->getPost('alamat'),
            'telepon'  => $this->request->getPost('telepon'),
        ]);
        return redirect()->to('admin/pemasok')->with('success', 'Data pemasok berhasil ditambahkan.');
    }

    public function updatePemasok($id)
    {
        $model = new PemasokModel();
        $model->update($id, [
            'kode_sup' => $this->request->getPost('kode_sup'),
            'nama'     => $this->request->getPost('nama'),
            'kategori' => $this->request->getPost('kategori'),
            'alamat'   => $this->request->getPost('alamat'),
            'telepon'  => $this->request->getPost('telepon'),
        ]);
        return redirect()->to('admin/pemasok')->with('success', 'Data pemasok berhasil diperbarui.');
    }

    public function hapusPemasok($id)
    {
        $model = new PemasokModel();
        $model->delete($id);
        return redirect()->to('admin/pemasok')->with('success', 'Data pemasok berhasil dihapus.');
    }

    public function biayaTNK()
    {
        if (!in_groups('admin')) return redirect()->to('login');

        $model = new BiayaTenagaKerjaModel();
        $data = [
            'tittle' => 'Manajemen Biaya Tenaga Kerja',
            'tenaker' => $model->findAll(),
        ];
        return view('admin/biaya/view_tenaker', $data);
    }

    public function simpanTNK()
    {
        $model = new BiayaTenagaKerjaModel();
        $model->save([
            'nama'     => $this->request->getPost('nama'),
            'biaya' => $this->request->getPost('biaya'),
        ]);
        return redirect()->to('admin/biaya/view_tenaker')->with('success', 'Data Biaya Tenaga Kerja berhasil ditambahkan.');
    }

    public function biayaBOP()
    {
        if (!in_groups('admin')) return redirect()->to('login');

        $model = new BiayaOverheadModel();
        $data = [
            'tittle' => 'Manajemen Biaya Overhead Pabrik',
            'bop' => $model->findAll(),
        ];
        return view('admin/biaya/view_bop', $data);
    }

    public function simpanBOP()
    {
        $model = new BiayaOverheadModel();
        $model->save([
            'nama'     => $this->request->getPost('nama'),
            'biaya' => $this->request->getPost('biaya'),
        ]);
        return redirect()->to('admin/biaya/view_bop')->with('success', 'Data BOP berhasil ditambahkan.');
    }

    public function hapusTNK($id)
    {
        $model = new BiayaTenagaKerjaModel();
        $model->delete($id);
        return redirect()->to('admin/biaya/view_tenaker')->with('success', 'Data Biaya Tenaga Kerja berhasil dihapus.');
    }

    public function hapusBOP($id)
    {
        $model = new BiayaOverheadModel();
        $model->delete($id);
        return redirect()->to('admin/biaya/view_bop')->with('success', 'Data BOP berhasil dihapus.');
    }

    //KOMPOSISI
    public function komposisiIndex()
    {
        $komposisiModel = new KomposisiBahanBSJModel();
        $bsjModel = new BSJModel();
        $bahanModel = new BahanModel();

        $data = [
            'tittle' => 'Manajemen Komposisi',
            'komposisi' => $komposisiModel
                ->select('komposisi_bahan_bsj.*, bahan.nama as nama_bahan, bahan.kategori')
                ->join('bahan', 'bahan.id = komposisi_bahan_bsj.id_bahan')
                ->findAll(),
            'bsj' => $bsjModel->findAll(),
            'bahan' => $bahanModel->findAll()
        ];

        return view('admin/komposisi/index', $data);
    }

    public function KomposisiTambah()
    {
        if (!in_groups('admin')) return redirect()->to('login');
        $bsjModel = new BSJModel();
        $bahanModel = new BahanModel();

        $data = [
            'tittle' => 'Input Komposisi ',
            'bsj' => $bsjModel->findAll(),
            'bahan' => $bahanModel->findAll()
        ];

        return view('admin/komposisi/tambah', $data);
    }

    public function komposisiSimpan()
    {
        $komposisiModel = new \App\Models\KomposisiBahanBSJModel();

        $id_bsj = $this->request->getPost('id_bsj');
        $bahan_ids = $this->request->getPost('bahan_id');
        $jumlahs = $this->request->getPost('jumlah');

        // Validasi awal
        if (!$id_bsj || empty($bahan_ids) || empty($jumlahs)) {
            return redirect()->back()->with('error', 'Data tidak lengkap');
        }

        // Simpan semua bahan ke database
        foreach ($bahan_ids as $i => $bahan_id) {
            $komposisiModel->insert([
                'id_bsj'    => $id_bsj,
                'id_bahan' => $bahan_id,
                'jumlah'   => $jumlahs[$i],
            ]);
        }

        return redirect()->to('/admin/komposisi')->with('success', 'Komposisi BSJ berhasil disimpan.');
    }

    public function hapusKomposisi($id_bsj)
    {
        $komposisiModel = new KomposisiBahanBSJModel();
        $komposisiModel->where('id_bsj', $id_bsj)->delete();

        return redirect()->to('/admin/komposisi')->with('success', 'Komposisi BSJ berhasil dihapus.');
    }

    // Form edit komposisi
    public function editKomposisi($id_bsj)
    {
        $komposisiModel = new KomposisiBahanBSJModel();
        $bsjModel = new BSJModel();
        $bahanModel = new BahanModel();

        $data = [
            'tittle'     => 'Edit Komposisi BSJ',
            'id_bsj'     => $id_bsj,
            'bsj'        => $bsjModel->findAll(),
            'bahan'      => $bahanModel->findAll(),
            'komposisi'  => $komposisiModel
                ->where('id_bsj', $id_bsj)
                ->select('komposisi_bahan_bsj.*, bahan.nama as nama_bahan, bahan.kategori')
                ->join('bahan', 'bahan.id = komposisi_bahan_bsj.id_bahan')
                ->findAll(),
        ];

        return view('admin/komposisi/edit', $data);
    }

    // Simpan ulang hasil edit komposisi
    public function updateKomposisi()
    {
        $komposisiModel = new KomposisiBahanBSJModel();

        $id_bsj     = $this->request->getPost('id_bsj');
        $bahan_ids  = $this->request->getPost('bahan_id');
        $jumlahs    = $this->request->getPost('jumlah');

        // Hapus komposisi lama terlebih dulu
        $komposisiModel->where('id_bsj', $id_bsj)->delete();

        // Simpan komposisi baru
        foreach ($bahan_ids as $i => $bahan_id) {
            $komposisiModel->insert([
                'id_bsj'    => $id_bsj,
                'id_bahan'  => $bahan_id,
                'jumlah'    => $jumlahs[$i],
            ]);
        }

        return redirect()->to('/admin/komposisi')->with('success', 'Komposisi BSJ berhasil diperbarui.');
    }

    public function master()
    {
        // Pastikan hanya admin yang bisa akses
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }
        $menuPenjualanModel = new \App\Models\MenuPenjualanModel();
        $varianMenuModel = new \App\Models\VarianMenuModel();
        $data['menu'] = $menuPenjualanModel->findAll();
        $data['varian_menu'] = $varianMenuModel->findAll();
        $data['tittle'] = 'SIOK | Master data';
        return view('admin/master', $data);
    }

    // Varian Menu
    public function varianmenu()
    {
        // Pastikan hanya admin yang bisa akses
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }
        $varianMenuModel = new \App\Models\VarianMenuModel();
        $data['varian_menu'] = $varianMenuModel->findAll();
        $data['tittle'] = 'SIOK | Varian Menu';
        return view('admin/varian_menu', $data);
    }
    public function tambahvarian()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $varianMenuModel = new VarianMenuModel();
        $data['tittle'] = 'SIOK | Varian Menu';
        $data['varian_menus'] = $varianMenuModel->findAll();

        return view('admin/varian_menu', $data);
    }

    public function simpanVarianMenu()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $varianMenuModel = new VarianMenuModel();

        $data = [
            'kategori' => $this->request->getPost('kategori'),
            'nama_menu' => $this->request->getPost('nama_menu'),
            'kode_barang' => $this->request->getPost('kode_barang'),
        ];

        $varianMenuModel->insert($data);

        return redirect()->to('/admin/varian_menu')->with('success', 'Varian menu berhasil disimpan');
    }

    public function updateVarianMenu($id)
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $varianMenuModel = new VarianMenuModel();
        $data = [
            'kategori' => $this->request->getPost('kategori'),
            'nama_menu' => $this->request->getPost('nama_menu'),
            'kode_barang' => $this->request->getPost('kode_barang'),
        ];

        $varianMenuModel->update($id, $data);

        return redirect()->to('/admin/varian_menu')->with('success', 'Data berhasil diperbarui');
    }

    public function hapusVarianMenu($id)
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $varianMenuModel = new VarianMenuModel();
        $varianMenuModel->delete($id);

        return redirect()->to('/admin/varian_menu')->with('success', 'Data berhasil dihapus');
    }

    public function simpanMenuPenjualan()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'kode_menu' => 'required|is_unique[menu.kode_menu]',
            'kategori' => 'required',
            'nama_menu' => 'required',
            'ukuran' => 'required',
            'daging' => 'required',
            'sayuran' => 'required',
            'kulit_kebab' => 'required',
            'harga' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            $errors = $validation->getErrors();
            $errorString = implode('<br>', array_values($errors));

            return redirect()->back()
                ->withInput()
                ->with('error', $errorString)
                ->with('show_modal', 'tambah_menu'); // ✅ gunakan string
        }

        $data = $this->request->getPost();

        $menuModel = new \App\Models\MenuPenjualanModel();
        $menuModel->save([
            'kode_menu' => $data['kode_menu'],
            'kategori' => $data['kategori'],
            'nama_menu' => $data['nama_menu'],
            'ukuran' => $data['ukuran'],
            'daging' => $data['daging'],
            'sayur' => $data['sayur'],
            'kulit_kebab' => $data['kulit_kebab'],
            'harga' => $data['harga'],
        ]);

        return redirect()->to('/admin/master')->with('success', 'Menu berhasil ditambahkan.');
    }


    public function editMenuPenjualan($id)
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $menuModel = new \App\Models\MenuPenjualanModel();

        $data = [
            'kode_menu' => $this->request->getPost('kode_menu'),
            'kategori' => $this->request->getPost('kategori'),
            'nama_menu' => $this->request->getPost('nama_menu'),
            'ukuran' => $this->request->getPost('ukuran'),
            'daging' => $this->request->getPost('daging'),
            'sayur' => $this->request->getPost('sayur'),
            'kulit_kebab' => $this->request->getPost('kulit_kebab'),
            'harga' => $this->request->getPost('harga'),
        ];

        $menuModel->update($id, $data);

        return redirect()->to('/admin/master')->with('success', 'Menu berhasil diperbarui');
    }

    public function hapusMenuPenjualan($id)
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $menuPenjualanModel = new MenuPenjualanModel();
        $menuPenjualanModel->delete($id);

        return redirect()->to('/admin/master')->with('success', 'Data menu berhasil dihapus');
    }

    public function input_transaksi()
    {
        if (!in_groups('admin') && !in_groups('penjualan')) {
            return redirect()->to('login');
        }

        $jualModel = new JualModel();
        $now = Time::now('Asia/Jakarta');
        $todayDate = $now->toDateString();

        $user = user();

        $outletModel = new OutletModel();
        $outlets = in_groups('admin') ? $outletModel->findAll() : [];

        // Determine selected outlet id: from GET param for admin, else user's outlet_id
        $selectedOutletId = null;

        if (in_groups('admin')) {
            $selectedOutletId = $this->request->getGet('outlet_id');

            // Fallback to first outlet if none selected and outlets exist
            if (empty($selectedOutletId) && !empty($outlets)) {
                $selectedOutletId = $outlets[0]['id'];
            }
        } else {
            $selectedOutletId = $user->outlet_id;
        }

        // Query last transaction for today at selected outlet to get invoice sequence
        $lastTrans = [];
        if ($selectedOutletId) {
            $lastTrans = $jualModel->where('tgl_jual', $todayDate)
                ->where('outlet_id', $selectedOutletId)
                ->orderBy('no_faktur', 'DESC')
                ->first();
        }

        if ($lastTrans) {
            if (is_array($lastTrans)) {
                $lastNumber = (int) substr($lastTrans['no_faktur'], -4);
            } else {
                $lastNumber = (int) substr($lastTrans->no_faktur, -4);
            }
        } else {
            $lastNumber = 0;
        }

        $nextNumber = $lastNumber + 1;
        $noFaktur = $now->format('Ymd') . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('admin/input_transaksi', [
            'tittle' => 'SIOK | Input Transaksi',
            'no_faktur' => $noFaktur,
            'tgl_jual' => $todayDate,
            'jam_jual' => $now->toTimeString(),
            'nama_kasir' => $user->username,
            'outlets' => $outlets,
            'selected_outlet_id' => $selectedOutletId
        ]);
    }

    public function generateNomorFaktur()
    {
        $outletId = $this->request->getGet('outlet_id');

        if (!$outletId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Outlet ID tidak ditemukan'
            ]);
        }

        $jualModel = new \App\Models\JualModel();
        $today = date('Y-m-d');

        $last = $jualModel->where('tgl_jual', $today)
            ->where('outlet_id', $outletId)
            ->orderBy('no_faktur', 'DESC')
            ->first();

        $lastNumber = 0;
        if ($last && isset($last['no_faktur'])) {
            $lastNumber = (int) substr($last['no_faktur'], -4);
        }

        $nextNumber = $lastNumber + 1;
        $noFaktur = date('Ymd') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return $this->response->setJSON([
            'success' => true,
            'no_faktur' => $noFaktur
        ]);
    }

    public function getVarianByKode($prefix)
    {
        $varianModel = new \App\Models\VarianMenuModel();
        $data = $varianModel
            ->like('kode_barang', $prefix, 'after')
            ->findAll();

        return $this->response->setJSON($data);
    }

    public function searchKodeMenu()
    {
        if (!in_groups('admin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Akses ditolak'
            ]);
        }

        $kodeMenu = $this->request->getGet('kode_menu');
        $menuModel = new \App\Models\MenuPenjualanModel();

        $result = $menuModel->where('kode_menu', $kodeMenu)->first();

        if ($result) {
            // Mapping kolom database ke nama yang dipakai di frontend
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'nama_menu' => $result['nama_menu'],   // sesuaikan dengan nama kolom DB
                    'kategori'  => $result['kategori'],    // sesuaikan dengan nama kolom DB
                    'ukuran'    => $result['ukuran'],      // sesuaikan dengan nama kolom DB
                    'harga'     => $result['harga']        // sesuaikan dengan nama kolom DB
                ]
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Menu tidak ditemukan'
            ]);
        }
    }

    public function searchKodeMenuAutocomplete()
    {
        $keyword = $this->request->getGet('keyword');
        $menuModel = new \App\Models\MenuPenjualanModel();

        $data = $menuModel
            ->like('kode_menu', $keyword)
            ->orLike('nama_menu', $keyword)
            ->findAll(10); // Batas maksimal 10

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    public function simpanTransaksi()
    {
        // Tentukan outlet_id berdasarkan role
        $outlet_id = in_groups('admin') ? $this->request->getPost('outlet_id') : user()->outlet_id;

        // Ambil dan sanitasi data
        $postData = $this->request->getPost();
        $postData['grand_total'] = str_replace('.', '', $postData['grand_total'] ?? '0');
        $postData['dibayar'] = str_replace('.', '', $postData['dibayar'] ?? '0');
        $postData['kembalian'] = str_replace('.', '', $postData['kembalian'] ?? '0');
        $postData['outlet_id'] = $outlet_id;

        // Decode detail transaksi
        $detailTransaksiJson = $this->request->getPost('detail_transaksi');
        if (empty($detailTransaksiJson)) {
            return redirect()->back()->withInput()->with('error', 'Detail transaksi kosong atau tidak valid.');
        }

        $detailTransaksi = json_decode($detailTransaksiJson, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($detailTransaksi)) {
            return redirect()->back()->withInput()->with('error', 'Detail transaksi kosong atau tidak valid.');
        }

        // Validasi data utama
        $rules = [
            'nofaktur'    => 'required',
            'tgl_jual'     => 'required|valid_date',
            'jam_jual'     => 'required',
            'nama_kasir'   => 'required',
            'grand_total'  => 'required|numeric',
            'dibayar'      => 'required|numeric',
            'kembalian'    => 'required|numeric',
        ];
        if (in_groups('admin')) {
            $rules['outlet_id'] = 'required|numeric';
        }

        if (!$this->validate($rules)) {
            $error_message = implode('<br>', $this->validator->getErrors());
            return redirect()->back()->withInput()->with('error', $error_message);
        }

        // Inisialisasi model dan koneksi DB
        $jualModel = new JualModel();
        $detailModel = new DetailJualModel();
        $menuModel = new MenuPenjualanModel();
        $persediaanModel = new PersediaanOutletModel();
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Simpan transaksi jual utama
            $dataJual = [
                'no_faktur'   => $postData['nofaktur'],
                'tgl_jual'    => $postData['tgl_jual'],
                'jam_jual'    => $postData['jam_jual'],
                'nama_kasir'  => $postData['nama_kasir'],
                'grand_total' => $postData['grand_total'],
                'dibayar'     => $postData['dibayar'],
                'kembalian'   => $postData['kembalian'],
                'outlet_id'   => $postData['outlet_id'],
            ];

            $jualModel->insert($dataJual);
            $idJual = $jualModel->getInsertID();

            // Simpan detail transaksi dan kurangi stok
            foreach ($detailTransaksi as $item) {
                $dataDetail = [
                    'id_jual'     => $idJual,
                    'kode_menu'   => $item['kode_menu'],
                    'kategori'    => $item['kategori'],
                    'nama_menu'   => $item['nama_menu'],
                    'ukuran'      => $item['ukuran'],
                    'harga'       => $item['harga'],
                    'qty'         => $item['qty'],
                    'total_harga' => $item['total_harga'],
                    'add_ons'     => $item['add_ons'] ?? '',
                    'extra'       => $item['extra'] ?? '',
                ];

                $detailModel->insert($dataDetail);

                // Ambil data menu dan proses pengurangan stok
                $menu = $menuModel->where('kode_menu', $item['kode_menu'])->first();
                if ($menu) {
                    $qty = (int) $item['qty'];
                    $namaMenu = strtolower($menu['nama_menu']);

                    $kodeDaging = null;
                    if (strpos($namaMenu, 'beef') !== false) {
                        $kodeDaging = 'BSJ03';
                    } elseif (strpos($namaMenu, 'chicken') !== false) {
                        $kodeDaging = 'BSJ02';
                    }

                    if ($kodeDaging && $menu['daging'] > 0) {
                        $totalDaging = $qty * $menu['daging'];
                        if (!$persediaanModel->kurangiStok($outlet_id, $kodeDaging, $totalDaging)) {
                            $db->transRollback();
                            return redirect()->back()->with('error', 'Stok daging tidak cukup untuk ' . $menu['nama_menu']);
                        }
                    }

                    if ($menu['sayur'] > 0) {
                        $totalSayur = $qty * $menu['sayur'];
                        if (!$persediaanModel->kurangiStok($outlet_id, 'BSJ04', $totalSayur)) {
                            $db->transRollback();
                            return redirect()->back()->with('error', 'Stok sayur tidak cukup untuk ' . $menu['nama_menu']);
                        }
                    }

                    if ($menu['kulit_kebab'] > 0) {
                        $totalKulit = $qty * $menu['kulit_kebab'];
                        if (!$persediaanModel->kurangiStok($outlet_id, 'BSJ01', $totalKulit)) {
                            $db->transRollback();
                            return redirect()->back()->with('error', 'Stok kulit tidak cukup untuk ' . $menu['nama_menu']);
                        }
                    }
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \Exception('Gagal menyimpan transaksi.');
            }

            return redirect()->to(base_url('admin/input_transaksi'))->with('success', 'Transaksi berhasil disimpan dan stok bahan berkurang.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage());
        }
    }


    public function tambahPersediaanOutlet()
    {
        $kode = $this->request->getPost('kode_bahan');
        $jumlah_input = $this->request->getPost('jumlah'); // input dari user
        $tanggal = $this->request->getPost('tanggal');

        $outlet_id = in_groups('admin')
            ? $this->request->getPost('outlet_id')
            : user()->outlet_id;

        if (!$kode || $jumlah_input === null || !$outlet_id || !$tanggal) {
            return redirect()->to('/admin/persediaanOutlet')->with('error', 'Data tidak lengkap.');
        }

        $bsjModel = new \App\Models\BSJModel();
        $bahan = $bsjModel->where('kode', $kode)->first();

        if (!$bahan) {
            return redirect()->to('/admin/persediaanOutlet')->with('error', 'Kode bahan tidak ditemukan.');
        }

        // Hitung jumlah aktual berdasarkan satuan
        if (strtolower($bahan['satuan']) === 'kg') {
            $jumlah = (int) ($jumlah_input * 1000); // simpan gram
        } else {
            $jumlah = (int) $jumlah_input; // pcs
        }

        // Kurangi stok BSJ pusat
        $bsjModel->kurangiStok($kode, $jumlah);

        // Tambah ke outlet
        $persediaanModel = new \App\Models\PersediaanOutletModel();
        $persediaanModel->tambahStok($outlet_id, $kode, $jumlah, $tanggal);

        // Catat log persediaan masuk agar laporan harian muncul
        $logMasukModel = new \App\Models\LogPersediaanMasukModel();
        $logMasukModel->insert([
            'outlet_id' => $outlet_id,
            'kode_bahan' => $kode,
            'jumlah' => $jumlah,
            'tanggal' => $tanggal,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/persediaanOutlet')->with('success', 'Stok berhasil ditambahkan ke outlet.');
    }

    public function persediaanOutlet()
    {
        $bsjModel = new \App\Models\BSJModel();
        $outletModel = new \App\Models\OutletModel();
        $persediaanModel = new \App\Models\PersediaanOutletModel();

        $outlet_id = in_groups('admin')
            ? $this->request->getGet('outlet_id')
            : user()->outlet_id;

        // fallback jika belum ada outlet_id (admin)
        if (!$outlet_id && in_groups('admin')) {
            $firstOutlet = $outletModel->first();
            $outlet_id = $firstOutlet ? $firstOutlet['id'] : null;
        }

        $persediaan = [];
        if ($outlet_id) {
            $persediaan = $persediaanModel
                ->where('outlet_id', $outlet_id)
                ->join('bsj', 'bsj.kode = persediaan_outlet.kode_bahan')
                ->select('persediaan_outlet.*, bsj.nama as nama_bahan, bsj.satuan')
                ->findAll();
        }

        return view('admin/persediaan_outlet', [
            'bsj' => $bsjModel->findAll(),
            'outlets' => $outletModel->findAll(),
            'selected_outlet' => $outlet_id,
            'persediaan' => $persediaan,
            'tittle' => 'Manajemen Persediaan Outlet'
        ]);
    }

    public function daftarTransaksi()
    {
        $jualModel = new JualModel();
        $outletModel = new OutletModel();

        $selectedOutlet = $this->request->getGet('outlet_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $outlets = $outletModel->findAll();
        $transaksi = $jualModel->getDaftarTransaksi($selectedOutlet, $startDate, $endDate);

        return view('admin/daftar_transaksi', [
            'tittle' => 'Daftar Transaksi',
            'transaksi' => $transaksi,
            'outlets' => $outlets,
            'selectedOutlet' => $selectedOutlet,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function detail($id)
    {
        $jualModel = new \App\Models\JualModel();
        $detailModel = new \App\Models\DetailJualModel();

        // Ambil transaksi utama
        $transaksi = $jualModel
            ->select('jual.*, outlet.nama_outlet')
            ->join('outlet', 'outlet.id = jual.outlet_id', 'left')
            ->where('jual.id', $id)
            ->first();

        if (!$transaksi) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Transaksi tidak ditemukan');
        }

        // Ambil detail transaksi (langsung dari tabel detail_jual)
        $detail = $detailModel
            ->where('id_jual', $id)
            ->findAll();

        return view('admin/detail_transaksi', [
            'tittle' => 'Detail Transaksi',
            'transaksi' => $transaksi,
            'detail' => $detail
        ]);
    }
    public function cetak($id)
    {
        $jualModel = new JualModel();
        $detailModel = new DetailJualModel();

        $transaksi = $jualModel
            ->select('jual.*, outlet.nama_outlet')
            ->join('outlet', 'outlet.id = jual.outlet_id', 'left')
            ->where('jual.id', $id)
            ->first();

        $detail = $detailModel
            ->select('detail_jual.*')  // cukup ambil semua kolom dari detail_jual
            ->where('id_jual', $id)
            ->findAll();

        return view('admin/cetak_transaksi', [
            'transaksi' => $transaksi,
            'detail' => $detail
        ]);
    }

    public function cetakSemua()
    {
        $jualModel = new JualModel();
        $outletId = $this->request->getGet('outlet_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $transaksi = $jualModel->getDaftarTransaksi($outletId, $startDate, $endDate);

        return view('admin/cetak_semua_transaksi', [
            'transaksi' => $transaksi
        ]);
    }

    public function inputShift()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $db = \Config\Database::connect();
        $shiftModel = new ShiftKerjaModel();

        // Ambil data outlet
        $outletModel = new \App\Models\OutletModel();
        $outlets = $outletModel->findAll(); // hasil: array asosiatif

        // Ambil user berdasarkan grup penjualan
        $builder = $db->table('users');
        $builder->select('users.id, users.username, users.outlet_id');
        $builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');
        $builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');
        $builder->where('auth_groups.name', 'penjualan');
        $users = $builder->get()->getResultArray();

        $data = [
            'outlets' => $outlets,
            'users'   => $users,
            'shifts'  => $shiftModel->findAll(),
            'tittle'  => 'Input Jadwal Shift'
        ];

        return view('admin/input_shift', $data);
    }

    public function getUsersByOutlet($outletId)
    {
        $users = $this->db->table('users')
            ->select('id, username')
            ->where('outlet_id', $outletId)
            ->get()
            ->getResultArray();

        return $this->response->setJSON($users);
    }

    public function simpanShift()
    {
        $userIds  = $this->request->getPost('user_id');
        $shiftId  = $this->request->getPost('shift_id');
        $tanggal  = $this->request->getPost('tanggal');

        // Ambil jam dari shift_kerja
        $shiftModel = new \App\Models\ShiftKerjaModel();
        $shift      = $shiftModel->find($shiftId);

        if (!$shift) {
            return redirect()->back()->with('error', 'Shift tidak ditemukan');
        }

        $pegawaiShiftModel = new \App\Models\PegawaiShiftModel();

        foreach ($userIds as $userId) {
            $pegawaiShiftModel->insert([
                'user_id'     => $userId,
                'shift_id'    => $shiftId,
                'tanggal'     => $tanggal,
                'jam_mulai'   => $shift['jam_mulai'],
                'jam_selesai' => $shift['jam_selesai'],
            ]);
        }

        return redirect()->to('admin/input-shift')->with('success', 'Shift berhasil disimpan');
    }


    public function dataShift()
    {
        $pegawaiShiftModel = new \App\Models\PegawaiShiftModel();

        $data['tittle'] = 'Data Jadwal Shift';
        $data['shifts'] = $pegawaiShiftModel
            ->select('pegawai_shift.*, users.username, shift_kerja.nama_shift, shift_kerja.jam_mulai, shift_kerja.jam_selesai')
            ->join('users', 'users.id = pegawai_shift.user_id')
            ->join('shift_kerja', 'shift_kerja.id = pegawai_shift.shift_id')
            ->orderBy('pegawai_shift.tanggal', 'DESC')
            ->findAll();

        return view('admin/data_shift', $data);
    }

    public function deleteShift($id)
    {
        $pegawaiShiftModel = new \App\Models\PegawaiShiftModel();
        $pegawaiShiftModel->delete($id);
        return redirect()->to('admin/data-shift')->with('success', 'Data shift berhasil dihapus.');
    }

    public function btklForm()
    {
        // Hanya admin yang bisa akses
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $data['tittle'] = 'SIOK | BTKL Form';
        return view('admin/btkl_form', $data);
    }

    public function hitungBTKL()
    {
        $start = $this->request->getPost('start_date');
        $end = $this->request->getPost('end_date');
        $gaji_per_shift = 40000;

        $db = \Config\Database::connect();
        $builder = $db->table('users');
        $builder->select('users.id, users.username, users.outlet_id');
        $builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');
        $builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');
        $builder->where('auth_groups.name', 'penjualan');
        $users = $builder->get()->getResult();

        $pegawaiShiftModel = new \App\Models\PegawaiShiftModel();
        $btklModel = new \App\Models\BTKLModel();

        $results = [];

        foreach ($users as $user) {
            $jumlahShift = $pegawaiShiftModel
                ->where('user_id', $user->id)
                ->where('tanggal >=', $start)
                ->where('tanggal <=', $end)
                ->countAllResults();

            if ($jumlahShift > 0) {
                $totalGaji = $jumlahShift * $gaji_per_shift;

                // Cek apakah data sudah ada
                $exists = $btklModel->where([
                    'user_id' => $user->id,
                    'periode_mulai' => $start,
                    'periode_selesai' => $end
                ])->first();

                if (!$exists) {
                    $btklModel->insert([
                        'user_id' => $user->id,
                        'outlet_id' => $user->outlet_id,
                        'jumlah_shift' => $jumlahShift,
                        'gaji_per_shift' => $gaji_per_shift,
                        'total_gaji' => $totalGaji,
                        'periode_mulai' => $start,
                        'periode_selesai' => $end
                    ]);
                }

                $results[] = [
                    'fullname' => $user->username,
                    'total_shift' => $jumlahShift,
                    'total_gaji' => $totalGaji,
                ];
            }
        }

        $data = [
            'tittle' => 'Hasil Perhitungan BTKL',
            'results' => $results,
            'gaji_per_shift' => $gaji_per_shift,
            'start' => $start,
            'end' => $end
        ];

        return view('admin/btkl_hasil', $data);
    }

    public function btkl()
    {
        $pegawaiShiftModel = new \App\Models\PegawaiShiftModel();
        $userModel = new \Myth\Auth\Models\UserModel();

        // Ambil semua shift yang berkaitan dengan user role 'penjualan'
        $shifts = $pegawaiShiftModel
            ->select('pegawai_shift.*, users.username, users.outlet_id')
            ->join('users', 'users.id = pegawai_shift.user_id')
            ->whereIn('users.id', function ($builder) {
                $builder->select('user_id')->from('auth_groups_users')->where('group_id', 3); // ID group 'penjualan'
            })
            ->orderBy('users.outlet_id', 'ASC')
            ->orderBy('tanggal', 'DESC')
            ->findAll();

        // Hitung total shift dan total gaji
        $rekap = [];

        foreach ($shifts as $shift) {
            $outlet = $shift['outlet_id'];
            $user = $shift['username'];

            if (!isset($rekap[$outlet])) $rekap[$outlet] = [];
            if (!isset($rekap[$outlet][$user])) $rekap[$outlet][$user] = 0;

            $rekap[$outlet][$user] += 1;
        }

        $data = [
            'tittle' => 'Perhitungan BTKL Gaji',
            'rekap'  => $rekap,
            'gaji_per_shift' => 40000
        ];

        return view('admin/btkl', $data);
    }

    // ==================== PERINTAH KERJA PRODUKSI ====================
    // 1. INDEX
    public function perintahKerjaIndex()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $data = [
            'tittle' => 'Daftar Perintah Kerja',
            'perintah_list' => $this->perintahModel->findAll(),
        ];

        return view('admin/perintah_kerja/index', $data);
    }

    // 2. INPUT
    public function perintahKerjaInput()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        // Ambil data bahan beserta stok terbaru
        $bahan_all = $this->bahanModel->findAll();
        // Pastikan field 'stok' ada dan benar (jika perlu join ke tabel stok, lakukan di sini)
        // Jika stok disimpan di tabel lain, lakukan join manual di sini

        $data = [
            'tittle' => 'Input Perintah Kerja',
            'bahan_all' => $bahan_all,
            'bsj' => $this->bsjModel->findAll(),
            'komposisi_bsj' => $this->komposisiModel->findAll(), // ID_BSJ, ID_Bahan, Jumlah
        ];

        return view('admin/perintah_kerja/input', $data);
    }

    // 3. SIMPAN
    public function perintahKerjaSimpan()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $produksiList = json_decode($this->request->getPost('produksi'), true);
        $rangkumanBahan = json_decode($this->request->getPost('rangkuman'), true);

        if (empty($produksiList)) {
            return redirect()->back()->with('error', 'Data produksi tidak boleh kosong.');
        }


        $perintahModel = new \App\Models\PerintahKerjaModel();
        $detailModel = new \App\Models\DetailPerintahKerjaModel();

        // Tanggal sama untuk semua produksi hari ini
        $tanggal = date('Y-m-d');
        $lastID = null;

        // Ambil admin_id terakhir dari tabel perintah_kerja
        $lastAdminId = $perintahModel->orderBy('admin_id', 'DESC')->select('admin_id')->first();
        $nextAdminId = ($lastAdminId && isset($lastAdminId['admin_id'])) ? ((int)$lastAdminId['admin_id'] + 1) : 1;

        // Gunakan 1 admin_id untuk semua item dalam 1 submit
        foreach ($produksiList as $i => $item) {
            $perintahModel->save([
                'tanggal' => $tanggal,
                'tipe'    => $item['tipe'],
                'nama'    => $item['nama'],
                'jumlah'  => $item['jumlah'],
                'satuan'  => $item['satuan'],
                'admin_id' => $nextAdminId
            ]);

            // Simpan ID salah satu (pertama) sebagai acuan
            if (!$lastID) {
                $lastID = $perintahModel->getInsertID();
            }
        }

        // Simpan rangkuman bahan hanya 1x, relasi ke salah satu produksi (via $lastID)
        if ($lastID && is_array($rangkumanBahan)) {
            foreach ($rangkumanBahan as $bahan) {
                $detailModel->insert([
                    'perintah_kerja_id' => $lastID,
                    'nama'     => $bahan['nama'],
                    'kategori' => $bahan['kategori'],
                    'jumlah'   => $bahan['jumlah'],
                    'satuan'   => $bahan['satuan']
                ]);
            }
        }

        // Kirim notifikasi ke produksi & keuangan
        $notifikasiModel = new \App\Models\NotifikasiModel();
        $db = \Config\Database::connect();

        // Ambil data produksi yang baru saja disimpan (semua dengan admin_id yang sama)
        $produksiBaru = $perintahModel->where('admin_id', $nextAdminId)->findAll();

        // Format daftar produksi
        $daftarProduksiText = "";
        foreach ($produksiBaru as $prod) {
            $daftarProduksiText .= "- " . ($prod['nama'] ?? '-') . " (" . ($prod['jumlah'] ?? 0) . " " . ($prod['satuan'] ?? '-') . ")\n";
        }

        // Format rangkuman bahan
        $rangkumanText = "";
        if (is_array($rangkumanBahan)) {
            foreach ($rangkumanBahan as $bahan) {
                $rangkumanText .= "- " . ($bahan['nama'] ?? '-') . " (" . ($bahan['jumlah'] ?? 0) . " " . ($bahan['satuan'] ?? '-') . ")\n";
            }
        }

        $pesan = "Perintah Kerja Baru:\n"
            . "Tanggal: " . ($tanggal ?? '-') . "\n"
            . "Daftar Produksi:\n" . $daftarProduksiText
            . "Kebutuhan Bahan yang Perlu Dibeli:\n" . $rangkumanText;

        // Kirim notifikasi ke semua user produksi dan keuangan
        $groups = ['produksi', 'keuangan'];
        foreach ($groups as $group) {
            $users = $db->table('users')
                ->select('users.id')
                ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
                ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
                ->where('auth_groups.name', $group)
                ->get()->getResultArray();

            foreach ($users as $user) {
                $notifikasiModel->insert([
                    'user_id'    => $user['id'],
                    'isi'        => $pesan,
                    'tipe'       => 'perintah_kerja',
                    'relasi_id'  => $lastID, // relasi ke salah satu id produksi
                    'dibaca'     => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()->to('admin/perintah-kerja')->with('success', 'Perintah kerja berhasil disimpan.');
    }

    // 4. DETAIL
    public function perintahKerjaDetail($id)
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $perintahModel = new \App\Models\PerintahKerjaModel();
        $detailModel = new \App\Models\DetailPerintahKerjaModel();

        $perintah = $perintahModel->find($id);
        if (!$perintah) {
            return redirect()->to('admin/perintah-kerja')->with('error', 'Perintah kerja tidak ditemukan.');
        }

        // Ambil semua produksi dengan tanggal yang sama
        $tanggal = $perintah['tanggal'];
        $perintah_list = $perintahModel->where('tanggal', $tanggal)->findAll();

        // Ambil rangkuman kebutuhan bahan dari detail_perintah_kerja
        $detail = $detailModel->where('perintah_kerja_id', $id)->findAll();

        $data = [
            'tittle'        => 'Detail Perintah Kerja',
            'perintah'      => $perintah,
            'perintah_list' => $perintah_list,
            'detail'        => $detail,
        ];

        return view('admin/perintah_kerja/detail', $data);
    }


    public function perintahKerjaHapus($id)
    {
        // Cek hak akses
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $perintahKerjaModel = new \App\Models\PerintahKerjaModel();
        $detailModel = new \App\Models\DetailPerintahKerjaModel();

        // Pastikan data perintah kerja ada
        $perintah = $perintahKerjaModel->find($id);
        if (!$perintah) {
            return redirect()->to('admin/perintah-kerja')->with('error', 'Data perintah kerja tidak ditemukan.');
        }

        $admin_id = $perintah['admin_id'];
        $tanggal = $perintah['tanggal'];

        // Ambil semua perintah kerja dengan admin_id dan tanggal yang sama
        $perintahList = $perintahKerjaModel->where('admin_id', $admin_id)->where('tanggal', $tanggal)->findAll();

        // Hapus semua detail terkait
        if (!empty($perintahList)) {
            foreach ($perintahList as $p) {
                $detailModel->where('perintah_kerja_id', $p['id'])->delete();
            }
            // Hapus semua perintah kerja utama
            $perintahKerjaModel->where('admin_id', $admin_id)->where('tanggal', $tanggal)->delete();
            return redirect()->to('admin/perintah-kerja')->with('success', 'Semua perintah kerja admin ini di tanggal tersebut berhasil dihapus.');
        } else {
            // Jika tidak ada list, hapus satu saja (fallback)
            $detailModel->where('perintah_kerja_id', $id)->delete();
            $perintahKerjaModel->delete($id);
            return redirect()->to('admin/perintah-kerja')->with('success', 'Perintah kerja berhasil dihapus.');
        }
    }
}
