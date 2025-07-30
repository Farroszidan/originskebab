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
        $detailModel = new \App\Models\DetailBiayaOverheadModel();
        $bop = $model->findAll();
        // Ambil detail untuk setiap BOP
        foreach ($bop as &$b) {
            $b['detail'] = $detailModel->where('biaya_overhead_id', $b['id'])->findAll();
        }
        $data = [
            'tittle' => 'Manajemen Biaya Overhead Pabrik',
            'bop' => $bop,
        ];
        return view('admin/biaya/view_bop', $data);
    }

    public function simpanBOP()
    {
        $model = new BiayaOverheadModel();
        $detailModel = new \App\Models\DetailBiayaOverheadModel();
        $jenis_bsj = $this->request->getPost('jenis_bsj');
        $nama = $this->request->getPost('nama');
        $biaya = $this->request->getPost('biaya');
        // Simpan BOP utama
        $model->save([
            'nama'      => $nama,
            'jenis_bsj' => $jenis_bsj,
            'biaya'     => $biaya,
        ]);
        $bopId = $model->getInsertID();
        // Simpan detail BOP
        $nama_biaya = $this->request->getPost('nama_biaya'); // array
        $jumlah_biaya = $this->request->getPost('jumlah_biaya'); // array
        if ($nama_biaya && $jumlah_biaya) {
            foreach ($nama_biaya as $i => $nm) {
                if ($nm && isset($jumlah_biaya[$i])) {
                    $detailModel->insert([
                        'biaya_overhead_id' => $bopId,
                        'nama_biaya' => $nm,
                        'jumlah_biaya' => $jumlah_biaya[$i],
                    ]);
                }
            }
        }
        return redirect()->to('admin/biaya/view_bop')->with('success', 'Data BOP dan detail berhasil ditambahkan.');
    }
    // DETAIL BOP
    public function detailBOP($id)
    {
        $model = new BiayaOverheadModel();
        $detailModel = new \App\Models\DetailBiayaOverheadModel();
        $bop = $model->find($id);
        $bop['detail'] = $detailModel->where('biaya_overhead_id', $id)->findAll();
        $data = [
            'tittle' => 'Detail Biaya Overhead Pabrik',
            'bop' => $bop,
        ];
        return view('admin/biaya/detail_bop', $data);
    }

    // UPDATE BOP
    public function updateBOP($id)
    {
        $model = new BiayaOverheadModel();
        $detailModel = new \App\Models\DetailBiayaOverheadModel();
        $jenis_bsj = $this->request->getPost('jenis_bsj');
        $nama = $this->request->getPost('nama');
        $biaya = $this->request->getPost('biaya');
        // Update BOP utama
        $model->update($id, [
            'nama' => $nama,
            'jenis_bsj' => $jenis_bsj,
            'biaya' => $biaya,
        ]);
        // Hapus detail lama
        $detailModel->where('biaya_overhead_id', $id)->delete();
        // Simpan detail baru
        $nama_biaya = $this->request->getPost('nama_biaya');
        $jumlah_biaya = $this->request->getPost('jumlah_biaya');
        if ($nama_biaya && $jumlah_biaya) {
            foreach ($nama_biaya as $i => $n) {
                if ($n && isset($jumlah_biaya[$i])) {
                    $detailModel->save([
                        'biaya_overhead_id' => $id,
                        'nama_biaya' => $n,
                        'jumlah_biaya' => $jumlah_biaya[$i],
                    ]);
                }
            }
        }
        return redirect()->to('admin/biaya/view_bop')->with('success', 'Data BOP berhasil diupdate.');
    }

    // UPDATE TENAKER
    public function updateTNK($id)
    {
        $model = new BiayaTenagaKerjaModel();
        $nama = $this->request->getPost('nama');
        $biaya = $this->request->getPost('biaya');
        $model->update($id, [
            'nama' => $nama,
            'biaya' => $biaya,
        ]);
        return redirect()->to('admin/biaya/view_tenaker')->with('success', 'Data Biaya Tenaga Kerja berhasil diupdate.');
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
        if (!in_groups('produksi', 'admin')) return redirect()->to('login');
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
        $rangkumanModel = new \App\Models\RangkumanKekuranganGabunganModel();
        // Ambil daftar batch_id dan tanggal
        $batchList = $rangkumanModel->select('batch_id, tanggal')->groupBy('batch_id, tanggal')->orderBy('batch_id', 'DESC')->findAll();

        $data = [
            'tittle' => 'Input Perintah Kerja',
            'bahan_all' => $bahan_all,
            'bsj' => $this->bsjModel->findAll(),
            'komposisi_bsj' => $this->komposisiModel->findAll(), // ID_BSJ, ID_Bahan, Jumlah
            'batchList' => $batchList,
        ];

        return view('admin/perintah_kerja/input', $data);
    }
    // Endpoint AJAX: Ambil rangkuman kekurangan gabungan berdasarkan batch_id
    public function getRangkumanBatchJson()
    {
        if (!in_groups('admin')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }
        $batch_id = $this->request->getGet('batch_id');
        if (!$batch_id) {
            return $this->response->setJSON(['success' => false, 'message' => 'batch_id tidak ditemukan']);
        }
        $rangkumanModel = new \App\Models\RangkumanKekuranganGabunganModel();
        $data = $rangkumanModel->where('batch_id', $batch_id)->findAll();
        return $this->response->setJSON(['success' => true, 'data' => $data]);
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
                    'satuan'   => $bahan['satuan'],
                    'pembulatan' => isset($bahan['pembulatan']) ? $bahan['pembulatan'] : null
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

    // Endpoint: Hitung kebutuhan produksi minimum outlet (kulit kebab, daging ayam, daging sapi)
    public function autoKebutuhanProduksi()
    {
        if (!in_groups('admin')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }
        $persediaanModel = new \App\Models\PersediaanOutletModel();
        $outletModel = new \App\Models\OutletModel();
        $outlets = $outletModel->findAll();
        // Kode bahan harus sesuai dengan yang ada di persediaan_outlet
        $kodeMap = [
            'kulit_kebab' => 'BSJ01', // contoh kode kulit kebab
            'daging_ayam' => 'BSJ02', // contoh kode daging ayam
            'daging_sapi' => 'BSJ03', // contoh kode daging sapi
        ];
        $min = [
            'kulit_kebab' => 200,
            'daging_ayam' => 100,
            'daging_sapi' => 100
        ];
        $result = [
            'kulit_kebab' => 0,
            'daging_ayam' => 0,
            'daging_sapi' => 0
        ];
        foreach ($outlets as $outlet) {
            foreach ($kodeMap as $key => $kode) {
                // Ambil stok dari persediaan_outlet
                $stok = $persediaanModel
                    ->where('outlet_id', $outlet['id'])
                    ->where('kode_bahan', $kode)
                    ->select('jumlah')
                    ->get()->getRowArray();
                $jumlah = $stok ? (int)$stok['jumlah'] : 0;
                $kurang = $min[$key] - $jumlah;
                if ($kurang > 0) $result[$key] += $kurang;
            }
        }
        return $this->response->setJSON(['success' => true, 'data' => $result]);
    }

    // ==================== KEBUTUHAN OUTLET ====================

    public function kekuranganBahanPerOutlet($return = false)
    {
        $db = \Config\Database::connect();

        $kode_bahan_ditampilkan = [
            'BSJ01',
            'BSJ02',
            'BSJ03',
            'BP001',
            'BP007',
            'BP008',
            'BP009',
            'BP010',
            'BP011',
            'BP012',
            'BP013',
            'BP014',
            'BP015',
            'BP016',
            'BP017',
            'BP018',
            'BP019',
            'BP020',
            'BP021',
            'BP022',
            'BP023'
        ];

        $komposisi_paten = [
            'BSJ01' => 1,
            'BSJ02' => 0.045,
            'BSJ03' => 0.045,
            'BP001' => 1 / 16,
            'BP012' => 1,
            'BP013' => 0.025,
            'BP014' => 0.022,
            'BP015' => 0.025,
            'BP016' => 0.025,
            'BP017' => 0.025,
            'BP018' => 0,
            'BP019' => 1,
            'BP020' => 0.22,
            'BP021' => 1,
            'BP022' => 0.5,
            'BP023' => 1
        ];

        $porsi = 200;

        $stok_dalam_gram = [
            'BP001',
            'BP002',
            'BP003',
            'BP004',
            'BP005',
            'BP006',
            'BP007',
            'BP008',
            'BP009',
            'BP010',
            'BP011',
            'BP013',
            'BP014',
            'BP015',
            'BP016',
            'BP017',
            'BP018'
        ];

        // Ambil data bahan dari database
        $bahanList = $db->table('bahan')->get()->getResultArray();
        $bahanMap = [];
        foreach ($bahanList as $b) {
            $bahanMap[$b['kode']] = [
                'nama' => $b['nama'],
                'satuan' => $b['satuan']
            ];
        }

        // Override nama & satuan untuk BSJ
        $bahanMap['BSJ01'] = ['nama' => 'Kulit Kebab', 'satuan' => 'pcs'];
        $bahanMap['BSJ02'] = ['nama' => 'Olahan Daging Ayam', 'satuan' => 'porsi'];
        $bahanMap['BSJ03'] = ['nama' => 'Olahan Daging Sapi', 'satuan' => 'porsi'];

        // Hitung kebutuhan bahan dasar non-BSJ (per outlet 200 porsi)
        $kebutuhanMap = [];
        foreach ($komposisi_paten as $kode => $per_porsi) {
            $kebutuhanMap[$kode] = round($per_porsi * $porsi, 3);
        }
        $kebutuhanMap['BSJ02'] = $porsi;
        $kebutuhanMap['BSJ03'] = $porsi;

        // Hitung bahan turunan saus tomat (jika ada)
        $jumlah_saus_tomat_kg = $kebutuhanMap['BP015'] ?? 0;
        $kebutuhanMap['BP007'] = round($jumlah_saus_tomat_kg * 12 / 1000, 3);
        $kebutuhanMap['BP008'] = round($jumlah_saus_tomat_kg * 9 / 1000, 3);
        $kebutuhanMap['BP009'] = round($jumlah_saus_tomat_kg * 3 / 1000, 3);
        $kebutuhanMap['BP010'] = round($jumlah_saus_tomat_kg * 6 / 1000, 3);
        $kebutuhanMap['BP011'] = round($jumlah_saus_tomat_kg * 20 / 1000, 3);

        $outlets = $db->table('outlet')->get()->getResultArray();

        $data['tittle'] = 'Kekurangan Bahan Per Outlet';
        $data['kekurangan_per_outlet'] = [];

        // Hitung kebutuhan & kekurangan per outlet
        foreach ($outlets as $outlet) {
            $id_outlet = $outlet['id'];
            $nama_outlet = $outlet['nama_outlet'] ?? $outlet['nama'];

            $detail = [];

            foreach ($kode_bahan_ditampilkan as $kode_bahan) {
                $kebutuhan = $kebutuhanMap[$kode_bahan] ?? 0;

                $stokRow = $db->table('persediaan_outlet')
                    ->where('outlet_id', $id_outlet)
                    ->where('kode_bahan', $kode_bahan)
                    ->orderBy('tanggal', 'desc')
                    ->get()->getRowArray();

                $stok = $stokRow['stok'] ?? 0;
                if (in_array($kode_bahan, $stok_dalam_gram)) {
                    $stok = round($stok / 1000, 3); // gram ke kg
                }

                $kurang = max(0, $kebutuhan - $stok);

                $detail[] = [
                    'kode_bahan' => $kode_bahan,
                    'nama_bahan' => $bahanMap[$kode_bahan]['nama'] ?? '-',
                    'satuan'     => $bahanMap[$kode_bahan]['satuan'] ?? '-',
                    'kebutuhan'  => $kebutuhan,
                    'stok'       => $stok,
                    'kurang'     => $kurang
                ];
            }

            $data['kekurangan_per_outlet'][] = [
                'outlet' => $nama_outlet,
                'data'   => $detail
            ];
        }

        // ================= Komposisi Manual BSJ per porsi ====================
        // gram per porsi x jumlah porsi (200)
        $komposisi_bsj_manual = [
            // Kulit (BSJ01)
            'Tepung terigu' => ['kode' => 'BB003', 'jumlah' => 45, 'satuan' => 'gram'],
            'Ragi' => ['kode' => 'BP002', 'jumlah' => 1, 'satuan' => 'gram'],
            'Shortening' => ['kode' => 'BP003', 'jumlah' => 3, 'satuan' => 'gram'],
            'Gula' => ['kode' => 'BP004', 'jumlah' => 2, 'satuan' => 'gram'],
            'Garam' => ['kode' => 'BP005', 'jumlah' => 1, 'satuan' => 'gram'],

            // Daging Ayam (BSJ02)
            'Daging Ayam' => ['kode' => 'BB001', 'jumlah' => 45, 'satuan' => 'gram'],
            'Yogurt' => ['kode' => 'BP006', 'jumlah' => 7, 'satuan' => 'gram'],
            'Paprika Bubuk' => ['kode' => 'BP007', 'jumlah' => 1, 'satuan' => 'gram'],
            'Lada putih bubuk' => ['kode' => 'BP008', 'jumlah' => 2, 'satuan' => 'gram'],
            'Parsley' => ['kode' => 'BP009', 'jumlah' => 0.5, 'satuan' => 'gram'],
            'Oregano' => ['kode' => 'BP010', 'jumlah' => 0.2, 'satuan' => 'gram'],
            'Bawang putih bubuk' => ['kode' => 'BP011', 'jumlah' => 1, 'satuan' => 'gram'],
            'Selada' => ['kode' => 'BP013', 'jumlah' => 0.3, 'satuan' => 'gram'],

            // Daging Sapi (BSJ03)
            'Daging Sapi' => ['kode' => 'BB002', 'jumlah' => 45, 'satuan' => 'gram'],
            'Susu' => ['kode' => 'BB004', 'jumlah' => 7, 'satuan' => 'gram'],
            'Lada hitam bubuk' => ['kode' => 'BP024', 'jumlah' => 2, 'satuan' => 'gram'],
            'Saos curry' => ['kode' => 'BP017', 'jumlah' => 1, 'satuan' => 'gram'],
            'Parsley' => ['kode' => 'BP009', 'jumlah' => 0.5, 'satuan' => 'gram'],
            'Oregano' => ['kode' => 'BP010', 'jumlah' => 0.3, 'satuan' => 'gram'],
            'Garam' => ['kode' => 'BP005', 'jumlah' => 0.3, 'satuan' => 'gram'],
        ];

        $bandingkan_bahan = [];

        // Ambil total kekurangan BSJ dari semua outlet (per kode BSJ)
        $total_kekurangan_bsj = [
            'BSJ01' => 0,
            'BSJ02' => 0,
            'BSJ03' => 0,
        ];
        foreach ($data['kekurangan_per_outlet'] as $outlet_data) {
            foreach ($outlet_data['data'] as $item) {
                if (isset($total_kekurangan_bsj[$item['kode_bahan']])) {
                    $total_kekurangan_bsj[$item['kode_bahan']] += $item['kurang'];
                }
            }
        }

        // Kalkulasi total kebutuhan BSJ manual berdasarkan total kekurangan BSJ
        foreach ($komposisi_bsj_manual as $nama => $item) {
            $kode = $item['kode'];
            $gram_per_porsi = $item['jumlah'];
            $total_gram = 0;

            // Tentukan total porsi dari BSJ yang sesuai
            // Jika kode bahan masuk di BSJ01, BSJ02, BSJ03
            // cari total kekurangan yang sesuai untuk porsi
            if (in_array($kode, ['BB003', 'BP002', 'BP003', 'BP004', 'BP005'])) {
                // Kulit (BSJ01)
                $total_gram = $gram_per_porsi * $total_kekurangan_bsj['BSJ01'];
            } elseif (in_array($kode, ['BB001', 'BP006', 'BP007', 'BP008', 'BP009', 'BP010', 'BP011', 'BP013'])) {
                // Daging Ayam (BSJ02)
                $total_gram = $gram_per_porsi * $total_kekurangan_bsj['BSJ02'];
            } elseif (in_array($kode, ['BB002', 'BB004', 'BP024', 'BP017', 'BP009', 'BP010', 'BP005'])) {
                // Daging Sapi (BSJ03)
                $total_gram = $gram_per_porsi * $total_kekurangan_bsj['BSJ03'];
            } else {
                // Kalau tidak termasuk BSJ, ambil porsi default 200 saja
                $total_gram = $gram_per_porsi * $porsi;
            }

            $total_kg = $total_gram / 1000;

            if (isset($bandingkan_bahan[$kode])) {
                $bandingkan_bahan[$kode]['jumlah'] += $total_kg;
            } else {
                $bandingkan_bahan[$kode] = [
                    'kode' => $kode,
                    'nama' => $nama,
                    'satuan' => 'kg',
                    'jumlah' => $total_kg
                ];
            }
        }

        // Gabungkan kekurangan dari per outlet (non BSJ)
        $rangkuman = [];
        foreach ($data['kekurangan_per_outlet'] as $outlet_data) {
            foreach ($outlet_data['data'] as $item) {
                $kode = $item['kode_bahan'];
                if (in_array($kode, ['BSJ01', 'BSJ02', 'BSJ03'])) continue;

                if (!isset($rangkuman[$kode])) {
                    $rangkuman[$kode] = [
                        'nama' => $item['nama_bahan'],
                        'satuan' => $item['satuan'],
                        'kurang' => 0
                    ];
                }
                $rangkuman[$kode]['kurang'] += $item['kurang'];
            }
        }

        // Tambahkan bahan pembentuk BSJ dari bandingkan_bahan ke rangkuman (assign langsung, bukan tambah)
        foreach ($bandingkan_bahan as $kode => $row) {
            if (!isset($rangkuman[$kode])) {
                $rangkuman[$kode] = [
                    'nama' => $row['nama'],
                    'satuan' => $row['satuan'],
                    'kurang' => 0
                ];
            }
            $rangkuman[$kode]['kurang'] = $row['jumlah'];
        }

        // Urutkan hasil rangkuman
        ksort($rangkuman);
        usort($bandingkan_bahan, fn($a, $b) => strcmp($a['kode'], $b['kode']));

        $data['rangkuman_kekurangan'] = $rangkuman;
        $data['bandingkan_bahan'] = array_values($bandingkan_bahan);
        $data['bahan_all'] = $db->table('bahan')->select('nama, satuan, stok')->get()->getResultArray();

        return $return ? $data : view('admin/perintah_kerja/kekurangan_per_outlet', $data);
    }

    public function simpanRangkumanKekurangan()
    {
        $request = $this->request;
        // Accept both AJAX and standard form POST
        $gabungan = $request->getPost('gabungan');
        $perOutlet = $request->getPost('perOutlet');
        $tanggal = $request->getPost('tanggal');

        // Parse JSON if sent as string
        if (is_string($gabungan)) {
            $gabungan = json_decode($gabungan, true);
        }
        if (is_string($perOutlet)) {
            $perOutlet = json_decode($perOutlet, true);
        }

        if (!is_array($gabungan) || !is_array($perOutlet) || !$tanggal) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak lengkap']);
        }

        $gabunganModel = new \App\Models\RangkumanKekuranganGabunganModel();
        $perOutletModel = new \App\Models\RangkumanKekuranganPerOutletModel();

        $db = \Config\Database::connect();
        $db->transStart();

        // Generate batch_id: max(batch_id) + 1
        $maxBatchGabungan = $db->table('rangkuman_kekurangan_gabungan')->selectMax('batch_id')->get()->getRowArray();
        $maxBatchPerOutlet = $db->table('rangkuman_kekurangan_per_outlet')->selectMax('batch_id')->get()->getRowArray();
        $lastBatch = max((int)($maxBatchGabungan['batch_id'] ?? 0), (int)($maxBatchPerOutlet['batch_id'] ?? 0));
        $batch_id = $lastBatch + 1;

        // Simpan gabungan
        foreach ($gabungan as $row) {
            $dataGabungan = [
                'kode_bahan' => $row['kode_bahan'] ?? '',
                'tipe' => $row['tipe'] ?? '',
                'nama_barang' => $row['nama_barang'] ?? '',
                'satuan' => $row['satuan'] ?? '',
                'kekurangan' => $row['kekurangan'] ?? 0,
                'pembulatan' => $row['pembulatan'] ?? 0,
                'tanggal' => $tanggal,
                'batch_id' => $batch_id
            ];
            $gabunganModel->insert($dataGabungan);
        }

        // Simpan per outlet
        foreach ($perOutlet as $row) {
            $dataOutlet = [
                'outlet' => $row['outlet'] ?? '',
                'kode_bahan' => $row['kode_bahan'] ?? '',
                'tipe' => $row['tipe'] ?? '',
                'nama_barang' => $row['nama_barang'] ?? '',
                'satuan' => $row['satuan'] ?? '',
                'kekurangan' => $row['kekurangan'] ?? 0,
                'pembulatan' => $row['pembulatan'] ?? 0,
                'tanggal' => $tanggal,
                'batch_id' => $batch_id
            ];
            $perOutletModel->insert($dataOutlet);
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan rangkuman kekurangan']);
        }
        // If AJAX, return JSON. If not, redirect.
        if ($request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Rangkuman kekurangan berhasil disimpan']);
        } else {
            return redirect()->to('admin/perintah-kerja')->with('success', 'Rangkuman kekurangan berhasil disimpan');
        }
    }

    public function hitungKebutuhanBahan()
    {
        $tanggal = $this->request->getPost('tanggal');
        $targetPorsi = 200; // misalnya 200 porsi per outlet
        $outletIds = $this->request->getPost('outlet_id');

        if (!$outletIds || !is_array($outletIds)) {
            return redirect()->back()->with('error', 'Pilih outlet terlebih dahulu.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Ambil semua menu
        $menus = $db->table('menu')->get()->getResult();

        foreach ($outletIds as $outletId) {
            // Cek apakah sudah ada perintah kerja
            $perintah = $db->table('perintah_kerja')
                ->where(['outlet_id' => $outletId, 'tanggal' => $tanggal])
                ->get()->getRow();

            if (!$perintah) {
                $db->table('perintah_kerja')->insert([
                    'outlet_id' => $outletId,
                    'tanggal' => $tanggal,
                    'status' => 'draft',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $perintahId = $db->insertID();
            } else {
                $perintahId = $perintah->id;
                $db->table('detail_perintah_kerja')->where('perintah_kerja_id', $perintahId)->delete();
            }

            // Hitung kebutuhan bahan total dari semua menu
            $totalKebutuhan = [];

            foreach ($menus as $menu) {
                $komposisi = json_decode($menu->komposisi, true);
                if (!$komposisi) continue;

                foreach ($komposisi as $bahanNama => $detail) {
                    $jumlah = floatval($detail['jumlah']) * $targetPorsi;

                    if (!isset($totalKebutuhan[$bahanNama])) {
                        $totalKebutuhan[$bahanNama] = 0;
                    }
                    $totalKebutuhan[$bahanNama] += $jumlah;
                }
            }

            // Simpan ke detail_perintah_kerja
            foreach ($totalKebutuhan as $bahanNama => $kebutuhanTotal) {
                // Cari ID bahan berdasarkan nama
                $bahan = $db->table('bahan')->where('nama', $bahanNama)->get()->getRow();
                if (!$bahan) continue;

                // Ambil stok outlet
                $stokOutlet = $db->table('persediaan_outlet')
                    ->where(['outlet_id' => $outletId, 'bahan_id' => $bahan->id])
                    ->get()->getRow();
                $stokAkhir = $stokOutlet ? $stokOutlet->stok_akhir : 0;

                $jumlahDikirim = max($kebutuhanTotal - $stokAkhir, 0);

                $db->table('detail_perintah_kerja')->insert([
                    'perintah_kerja_id' => $perintahId,
                    'bahan_id' => $bahan->id,
                    'kebutuhan_total' => $kebutuhanTotal,
                    'stok_outlet' => $stokAkhir,
                    'jumlah_dikirim' => $jumlahDikirim,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal menghitung kebutuhan bahan.');
        }

        return redirect()->to('admin/perintah-kerja/')->with('success', 'Kebutuhan bahan berhasil dihitung dari komposisi menu.');
    }

    // ==================== PERINTAH PENGIRIMAN ====================
    // INDEX
    public function perintahPengirimanIndex()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $pengirimanModel = new \App\Models\PerintahPengirimanModel();
        $outletModel = new \App\Models\OutletModel();
        $outletList = $outletModel->findAll();
        $outletMap = [];
        foreach ($outletList as $o) {
            $outletMap[$o['id']] = $o['nama_outlet'];
        }

        $pengiriman_list = $pengirimanModel->findAll();
        // Ambil outlet tujuan untuk setiap pengiriman
        $pengirimanOutletModel = new \App\Models\PerintahPengirimanOutletModel();
        foreach ($pengiriman_list as &$row) {
            $row['outlets'] = $pengirimanOutletModel->where('perintah_pengiriman_id', $row['id'])->findAll();
            // Tambahkan nama outlet
            foreach ($row['outlets'] as &$outlet) {
                $outlet['nama_outlet'] = $outletMap[$outlet['outlet_id']] ?? $outlet['outlet_id'];
            }
        }

        $data = [
            'tittle' => 'Daftar Perintah Pengiriman',
            'pengiriman_list' => $pengiriman_list,
        ];
        return view('admin/perintah_pengiriman/index', $data);
    }

    // DETAIL
    public function perintahPengirimanDetail($id)
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $pengirimanModel = new \App\Models\PerintahPengirimanModel();
        $pengirimanOutletModel = new \App\Models\PerintahPengirimanOutletModel();
        $pengirimanDetailModel = new \App\Models\PerintahPengirimanDetailModel();
        $outletModel = new \App\Models\OutletModel();
        $outletList = $outletModel->findAll();
        $outletMap = [];
        foreach ($outletList as $o) {
            $outletMap[$o['id']] = $o['nama_outlet'];
        }

        $pengiriman = $pengirimanModel->find($id);
        if (!$pengiriman) {
            return redirect()->to('admin/perintah-pengiriman')->with('error', 'Data tidak ditemukan.');
        }

        $outlets = $pengirimanOutletModel->where('perintah_pengiriman_id', $id)->findAll();
        foreach ($outlets as &$outlet) {
            $outlet['nama_outlet'] = $outletMap[$outlet['outlet_id']] ?? $outlet['outlet_id'];
        }

        // Ambil detail item, join ke outlet tujuan
        $detail = [];
        $outletIds = array_column($outlets, 'id');
        if (!empty($outletIds)) {
            $detail = $pengirimanDetailModel
                ->select('perintah_pengiriman_detail.*, perintah_pengiriman_outlet.outlet_id')
                ->join('perintah_pengiriman_outlet', 'perintah_pengiriman_outlet.id = perintah_pengiriman_detail.perintah_pengiriman_outlet_id')
                ->whereIn('perintah_pengiriman_outlet_id', $outletIds)
                ->findAll();
            foreach ($detail as &$d) {
                $d['nama_outlet'] = $outletMap[$d['outlet_id']] ?? $d['outlet_id'];
            }
        }

        $data = [
            'tittle' => 'Detail Perintah Pengiriman',
            'pengiriman' => $pengiriman,
            'outlets' => $outlets,
            'detail' => $detail,
        ];
        return view('admin/perintah_pengiriman/detail', $data);
    }

    // INPUT (form)
    public function perintahPengirimanInput()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }
        $outletModel = new \App\Models\OutletModel();
        $bsjModel = new \App\Models\BSJModel();
        $bahanModel = new \App\Models\BahanModel();
        // Ambil unique perintah_kerja_id dari detail_perintah_kerja
        $perintahModel = new \App\Models\PerintahKerjaModel();
        // Ambil unique admin_id dari tabel perintah_kerja
        $builder = $perintahModel->builder();
        $builder->select('admin_id, MIN(id) as id, MIN(tanggal) as tanggal');
        $builder->groupBy('admin_id');
        $adminBatches = $builder->get()->getResultArray();
        $perintahKerjaData = [];
        foreach ($adminBatches as $row) {
            $perintahKerjaData[] = [
                'id' => $row['id'],
                'admin_id' => $row['admin_id'],
                'tanggal' => $row['tanggal'],
            ];
        }
        $rangkumanModel = new \App\Models\RangkumanKekuranganPerOutletModel();
        $rangkumanKekurangan = $rangkumanModel->findAll();
        $data = [
            'tittle' => 'Input Perintah Pengiriman',
            'outlets' => $outletModel->findAll(),
            'bsj' => $bsjModel->findAll(),
            'bahan' => $bahanModel->findAll(),
            'perintahKerjaData' => $perintahKerjaData,
            'rangkumanKekuranganPerOutlet' => $rangkumanKekurangan
        ];
        return view('admin/perintah_pengiriman/input', $data);
    }

    // Endpoint AJAX: Ambil rangkuman kekurangan per outlet berdasarkan batch_id dan outlet_id
    public function getKekuranganPerOutletJson()
    {
        $batch_id = $this->request->getGet('batch_id');
        $outlet_id = $this->request->getGet('outlet_id');
        $model = new \App\Models\RangkumanKekuranganPerOutletModel();
        $where = [];
        if ($batch_id) $where['batch_id'] = $batch_id;
        if ($outlet_id) $where['outlet'] = $outlet_id;
        $result = $model->where($where)->findAll();
        return $this->response->setJSON([
            'success' => true,
            'data' => $result
        ]);
    }

    // SIMPAN
    public function perintahPengirimanSimpan()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $pengirimanModel = new \App\Models\PerintahPengirimanModel();
        $pengirimanOutletModel = new \App\Models\PerintahPengirimanOutletModel();
        $pengirimanDetailModel = new \App\Models\PerintahPengirimanDetailModel();
        $bsjModel = new \App\Models\BSJModel();

        $tanggal = $this->request->getPost('tanggal');
        $keterangan = $this->request->getPost('keterangan');
        $outletData = $this->request->getPost('outlet'); // array: outlet[index][id_outlet], outlet[index][keterangan], outlet[index][items][itemIndex][...]

        // Simpan perintah_pengiriman utama
        $pengirimanModel->insert([
            'tanggal' => $tanggal,
            'keterangan' => $keterangan,
        ]);
        $pengiriman_id = $pengirimanModel->getInsertID();

        // Simpan outlet tujuan dan detail item per outlet
        if (is_array($outletData)) {
            foreach ($outletData as $outlet) {
                $outlet_id = $outlet['id_outlet'] ?? null;
                $outlet_ket = $outlet['keterangan'] ?? null;
                $items = $outlet['items'] ?? [];
                if (!$outlet_id) continue;
                $pengirimanOutletModel->insert([
                    'perintah_pengiriman_id' => $pengiriman_id,
                    'outlet_id' => $outlet_id,
                    'keterangan' => $outlet_ket,
                ]);
                $pengiriman_outlet_id = $pengirimanOutletModel->getInsertID();
                // Simpan detail item
                if (is_array($items) && count($items) > 0) {
                    foreach ($items as $item) {
                        // Pastikan semua item diproses, baik bsj maupun bahan
                        $nama_barang = $item['nama_barang'] ?? '';
                        $satuan = $item['satuan'] ?? '';
                        $tipe = $item['jenis'] ?? null;
                        if (!empty($tipe)) {
                            $tipe = strtolower($tipe);
                        }
                        // Jika nama_barang kosong dan id_barang ada, ambil dari BSJ
                        if ((empty($nama_barang) || empty($satuan)) && !empty($item['id_barang'])) {
                            $bsj = $bsjModel->where('id', $item['id_barang'])->first();
                            if ($bsj) {
                                if (empty($nama_barang)) {
                                    $nama_barang = $bsj['nama'] ?? '';
                                }
                                if (empty($satuan)) {
                                    $satuan = $bsj['satuan'] ?? '';
                                }
                                if (empty($tipe)) {
                                    $tipe = 'bsj';
                                }
                            } else {
                                if (empty($tipe)) {
                                    $tipe = 'bahan';
                                }
                            }
                        }
                        // Fallback: jika tipe masih kosong, default ke 'bahan'
                        if (empty($tipe)) {
                            $tipe = 'bahan';
                        }
                        $tipe = strtolower($tipe);
                        // Validasi minimal: id_barang, nama_barang, satuan, jumlah harus ada
                        if (!empty($item['id_barang']) && !empty($nama_barang) && !empty($satuan) && !empty($item['jumlah'])) {
                            $pengirimanDetailModel->insert([
                                'perintah_pengiriman_outlet_id' => $pengiriman_outlet_id,
                                'tipe' => $tipe,
                                'barang_id' => $item['id_barang'],
                                'nama_barang' => $nama_barang,
                                'jumlah' => $item['jumlah'],
                                'satuan' => $satuan,
                            ]);
                        }
                    }
                }
            }
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data.');
        }
        return redirect()->to('admin/perintah-pengiriman')->with('success', 'Perintah pengiriman berhasil disimpan.');
    }

    // HAPUS
    public function perintahPengirimanHapus($id)
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }
        $pengirimanModel = new \App\Models\PerintahPengirimanModel();
        $pengirimanOutletModel = new \App\Models\PerintahPengirimanOutletModel();
        $pengirimanDetailModel = new \App\Models\PerintahPengirimanDetailModel();

        $outlets = $pengirimanOutletModel->where('perintah_pengiriman_id', $id)->findAll();
        foreach ($outlets as $outlet) {
            $pengirimanDetailModel->where('perintah_pengiriman_outlet_id', $outlet['id'])->delete();
        }
        $pengirimanOutletModel->where('perintah_pengiriman_id', $id)->delete();
        $pengirimanModel->delete($id);
        return redirect()->to('admin/perintah-pengiriman')->with('success', 'Data perintah pengiriman berhasil dihapus.');
    }

    public function getBSJByAdminId($adminId)
    {
        // Ambil semua perintah kerja (BSJ & bahan) dengan admin_id ini
        $perintahKerjaModel = new \App\Models\PerintahKerjaModel();
        // Data utama hanya dari tabel perintah_kerja (BSJ dan bahan produksi)
        $list = $perintahKerjaModel->where('admin_id', $adminId)->findAll();
        $result = [];
        foreach ($list as $row) {
            $result[] = [
                'jenis' => strtolower($row['tipe']),
                'id' => $row['id'],
                'nama' => $row['nama'],
                'jumlah' => $row['jumlah'],
                'satuan' => $row['satuan'],
            ];
        }
        // Jika ingin rangkuman bahan dari detail_perintah_kerja, tambahkan di sini sesuai instruksi khusus user
        // Default: tidak ditambahkan kecuali diminta
        return $this->response->setJSON($result);
    }




    // ==================== ENDPOINT AJAX UNTUK FORM PENGIRIMAN ====================
    // 1. Ambil daftar perintah kerja berdasarkan outlet (untuk dropdown setelah pilih outlet)
    public function perintahKerjaListByOutlet()
    {
        if (!in_groups('admin')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }
        $outlet_id = $this->request->getGet('outlet_id');
        if (!$outlet_id) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID outlet tidak ditemukan']);
        }
        // Ambil perintah kerja yang sudah selesai produksi dan siap dikirim ke outlet ini
        $perintahModel = new \App\Models\PerintahKerjaModel();
        $list = $perintahModel->orderBy('tanggal', 'DESC')->findAll();
        // Map agar field yang dikirim ke JS selalu id, tanggal, nama
        $result = [];
        foreach ($list as $row) {
            $result[] = [
                'id' => isset($row['id']) ? $row['id'] : (isset($row['ID']) ? $row['ID'] : null),
                'tanggal' => isset($row['tanggal']) ? $row['tanggal'] : (isset($row['Tanggal']) ? $row['Tanggal'] : ''),
                'nama' => isset($row['nama']) ? $row['nama'] : (isset($row['Nama']) ? $row['Nama'] : ''),
            ];
        }
        return $this->response->setJSON(['success' => true, 'data' => $result]);
    }

    // ENDPOINT: Ambil semua perintah kerja (BSJ) berdasarkan admin_id (untuk tabel BSJ di form pengiriman)
    public function perintahKerjaByAdminIdJson()
    {
        if (!in_groups('admin')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }
        $admin_id = $this->request->getGet('admin_id');
        if (!$admin_id) {
            return $this->response->setJSON(['success' => false, 'message' => 'admin_id tidak ditemukan']);
        }
        $perintahModel = new \App\Models\PerintahKerjaModel();
        $list = $perintahModel->where('admin_id', $admin_id)->findAll();
        // Hanya ambil tipe=bsj saja (jika ada tipe lain)
        $result = [];
        foreach ($list as $row) {
            if (isset($row['tipe']) && strtolower($row['tipe']) === 'bsj') {
                $result[] = [
                    'id' => $row['id'],
                    'nama' => $row['nama'],
                    'jumlah' => $row['jumlah'],
                    'satuan' => $row['satuan'],
                ];
            }
        }
        return $this->response->setJSON(['success' => true, 'data' => $result]);
    }
    // 2. Ambil detail BSJ (barang/jumlah) dari perintah kerja terpilih (untuk tabel item)
    public function perintahKerjaDetailJson()
    {
        if (!in_groups('admin')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }
        $perintah_kerja_id = $this->request->getGet('perintah_kerja_id');
        if (!$perintah_kerja_id) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID perintah kerja tidak ditemukan']);
        }
        $detailModel = new \App\Models\DetailPerintahKerjaModel();
        $bsjModel = new \App\Models\BSJModel();
        $detail = $detailModel->where('perintah_kerja_id', $perintah_kerja_id)->findAll();
        // Join ke tabel BSJ untuk ambil nama barang, satuan, dsb
        foreach ($detail as &$d) {
            $bsj = $bsjModel->where('nama', $d['nama'])->first();
            if ($bsj) {
                $d['kode_bsj'] = $bsj['kode'] ?? null;
                $d['satuan'] = $bsj['satuan'] ?? null;
            }
        }
        return $this->response->setJSON(['success' => true, 'data' => $detail]);
    }

    public function simpanDraftPerintahKerja()
    {
        $outletId = $this->request->getPost('outlet_id');
        $tanggal = $this->request->getPost('tanggal');
        $shift = $this->request->getPost('shift');

        // Hitung kebutuhan bahan
        $dataBahan = $this->hitungKebutuhanBahan($outletId);

        // Simpan ke perintah_kerja
        $db = \Config\Database::connect();
        $db->transStart();

        $db->table('perintah_kerja')->insert([
            'outlet_id' => $outletId,
            'tanggal' => $tanggal,
            'shift' => $shift,
            'status' => 'draft'
        ]);
        $perintahKerjaId = $db->insertID();

        // Simpan detail bahan
        foreach ($dataBahan as $bahan) {
            $db->table('detail_perintah_kerja')->insert([
                'perintah_kerja_id' => $perintahKerjaId,
                'bahan_id' => $bahan['bahan_id'],
                'kebutuhan_total' => $bahan['kebutuhan_total'],
                'stok_outlet' => $bahan['stok_outlet'],
                'jumlah_dikirim' => $bahan['jumlah_dikirim']
            ]);
        }

        $db->transComplete();

        return redirect()->to('/admin/perintah-kerja')->with('success', 'Draft berhasil disimpan.');
    }

    public function daftarPerintahKerja()
    {
        $db = \Config\Database::connect();
        $data['perintah'] = $db->table('perintah_kerja')
            ->where('status', 'draft')
            ->get()->getResult();

        return view('admin/daftar_perintah_kerja', $data);
    }
}
