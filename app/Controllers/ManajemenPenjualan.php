<?php

namespace App\Controllers;

use App\Models\VarianMenuModel;
use App\Models\MenuPenjualanModel;
use App\Models\JualModel;
use App\Models\DetailJualModel;
use App\Models\OutletModel;
use App\Models\PersediaanOutletModel;
use App\Models\LogPersediaanMasukModel;
use App\Models\LogPersediaanHarianModel;
use App\Models\PegawaiShiftModel;
use App\Models\ShiftKerjaModel;
use App\Models\BuktiPembelianModel;
use App\Models\LaporanShiftModel;
use App\Models\LaporanPenjualanModel;
use App\Models\PembelianOperasionalModel;
use App\Models\DetailPembelianOperasionalModel;
use App\Models\PermintaanModel;
use App\Models\PermintaanDetailModel;
use App\Models\HppPenjualanModel;
use Myth\Auth\Models\UserModel;
use App\Models\BSJModel;
use App\Models\BahanModel;
use App\Models\HPPModel;
use App\Models\BtklModel;
use App\Models\AkunModel;
use CodeIgniter\I18n\Time;
use Myth\Auth\Authorization\GroupModel;
use function auth;
use function user;
use function in_groups;

class ManajemenPenjualan extends BaseController
{
    protected $db, $builder, $jualModel, $detailJualModel, $permintaanModel, $detailPermintaanModel, $detailPembelianModel, $pembelianModel, $laporanModel, $shiftModel, $outletModel, $session, $auth;

    public function __construct()
    {
        helper(['auth']);
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('users');
        $this->jualModel = new JualModel();
        $this->detailJualModel = new DetailJualModel();
        $this->permintaanModel = new PermintaanModel();
        $this->detailPermintaanModel    = new PermintaanDetailModel();
        $this->pembelianModel = new PembelianOperasionalModel();
        $this->detailPembelianModel = new DetailPembelianOperasionalModel();
        $this->laporanModel = new LaporanPenjualanModel(); // ✅ INI YANG WAJIB ADA
        $this->shiftModel = new PegawaiShiftModel();
        $this->outletModel = new \App\Models\OutletModel();
        $this->session = session();
        $this->auth = service('authentication');
    }
    public function dashboard()
    {
        if (!in_groups('penjualan')) {
            return redirect()->to('login');
        }

        $data['tittle'] = 'SIOK | Dashboard Penjualan';
        return view('penjualan/dashboard', $data);
    }

    // ===================================== MASTER END ===================================================== //
    public function inputOutlet()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $data['tittle'] = 'SIOK | Input Outlet';
        $data['outlet'] = $this->outletModel->findAll();
        return view('manajemen-penjualan/input_outlet', $data);
    }

    public function hapusOutlet($id)
    {
        if (!in_groups('admin')) {
            return redirect()->to('login')->with('error', 'Anda tidak memiliki akses.');
        }

        $outletModel = new \App\Models\OutletModel();
        $outletModel->delete($id);

        return redirect()->to('/manajemen-penjualan/inputOutlet')->with('success', 'Outlet berhasil dihapus.');
    }

    public function simpanOutlet()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $data = [
            'nama_outlet' => $this->request->getPost('nama_outlet'),
            'alamat'      => $this->request->getPost('alamat'),
        ];

        $this->outletModel->insert($data);

        return redirect()->to('/manajemen-penjualan/inputOutlet')->with('success', 'Data outlet berhasil disimpan.');
    }

    public function inputJamShift()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $shiftModel = new \App\Models\ShiftKerjaModel();
        $data['tittle'] = 'SIOK | Input Jam Shift';
        $data['shifts'] = $shiftModel->findAll();
        return view('manajemen-penjualan/input_jam_shift', $data);
    }

    public function hapusJamShift($id)
    {
        if (!in_groups('admin')) {
            return redirect()->to('login')->with('error', 'Anda tidak memiliki akses.');
        }

        $shiftModel = new \App\Models\ShiftKerjaModel();
        $shiftModel->delete($id);

        return redirect()->to('/manajemen-penjualan/inputJamShift')->with('success', 'Jam shift berhasil dihapus.');
    }


    public function simpanJamShift()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $shiftModel = new \App\Models\ShiftKerjaModel();
        $data = [
            'nama_shift'  => $this->request->getPost('nama_shift'),
            'jam_mulai'   => $this->request->getPost('jam_mulai'),
            'jam_selesai' => $this->request->getPost('jam_selesai'),
        ];

        $shiftModel->insert($data);
        return redirect()->to('/manajemen-penjualan/inputJamShift')->with('success', 'Jam shift berhasil ditambahkan.');
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
        return view('manajemen-penjualan/master', $data);
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
        return view('manajemen-penjualan/varian_menu', $data);
    }

    public function tambahvarian()
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $varianMenuModel = new VarianMenuModel();
        $data['tittle'] = 'SIOK | Varian Menu';
        $data['varian_menus'] = $varianMenuModel->findAll();

        return view('manajemen-penjualan/varian_menu', $data);
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

        return redirect()->to('manajemen-penjualan/varian_menu')->with('success', 'Varian menu berhasil disimpan');
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

        return redirect()->to('manajemen-penjualan/varian_menu')->with('success', 'Data berhasil diperbarui');
    }

    public function hapusVarianMenu($id)
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $varianMenuModel = new VarianMenuModel();
        $varianMenuModel->delete($id);

        return redirect()->to('manajemen-penjualan/varian_menu')->with('success', 'Data berhasil dihapus');
    }

    public function simpanMenuPenjualan()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'kode_menu' => 'required|is_unique[menu.kode_menu]',
            'kategori' => 'required',
            'nama_menu' => 'required',
            'harga' => 'required|numeric',
            'komposisi' => 'required' // ✅ hapus 'array'
        ];

        if (!$this->validate($rules)) {
            $errors = $validation->getErrors();
            $errorString = implode('<br>', array_values($errors));

            return redirect()->back()
                ->withInput()
                ->with('error', $errorString)
                ->with('show_modal', 'tambah_menu');
        }

        // ✅ Validasi manual untuk komposisi
        $komposisi = $this->request->getPost('komposisi');
        if (!is_array($komposisi) || empty($komposisi)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Komposisi wajib diisi dan harus berbentuk array.')
                ->with('show_modal', 'tambah_menu');
        }

        $data = $this->request->getPost();

        $menuModel = new \App\Models\MenuPenjualanModel();

        $menuModel->save([
            'kode_menu' => $data['kode_menu'],
            'kategori' => $data['kategori'],
            'nama_menu' => $data['nama_menu'],
            'harga' => $data['harga'],
            'komposisi' => json_encode($komposisi, JSON_UNESCAPED_UNICODE)
        ]);

        return redirect()->to('manajemen-penjualan/master')
            ->with('success', 'Menu berhasil ditambahkan.');
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
            'harga' => $this->request->getPost('harga'),
            'komposisi' => json_encode($this->request->getPost('komposisi'), JSON_UNESCAPED_UNICODE)
        ];

        $menuModel->update($id, $data);

        return redirect()->to('manajemen-penjualan/master')->with('success', 'Menu berhasil diperbarui');
    }

    public function hapusMenuPenjualan($id)
    {
        if (!in_groups('admin')) {
            return redirect()->to('login');
        }

        $menuPenjualanModel = new MenuPenjualanModel();
        $menuPenjualanModel->delete($id);

        return redirect()->to('manajemen-penjualan/master')->with('success', 'Data menu berhasil dihapus');
    }
    // ===================================== MASTER END ===================================================== //

    // ===================================== INPUT TRANSAKSI START ===================================================== //
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

        return view('manajemen-penjualan/input_transaksi', [
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
            ->where('kode_barang', $prefix)
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
        if (!in_groups(['admin', 'penjualan'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        $outlet_id = in_groups('admin') ? $this->request->getPost('outlet_id') : user()->outlet_id;

        $postData = $this->request->getPost();
        $postData['grand_total'] = str_replace('.', '', $postData['grand_total'] ?? '0');
        $postData['dibayar'] = str_replace('.', '', $postData['dibayar'] ?? '0');
        $postData['kembalian'] = str_replace('.', '', $postData['kembalian'] ?? '0');
        $postData['outlet_id'] = $outlet_id;
        $postData['metode_pembayaran'] = $this->request->getPost('metode_pembayaran');
        $postData['jenis_cashless'] = $this->request->getPost('jenis_cashless') ?? null;
        $akunModel = new AkunModel();

        if ($postData['metode_pembayaran'] === 'cashless') {
            // Tentukan kode akun berdasarkan jenis cashless
            $cashlessKodeMap = [
                'qris' => 110,
                'gofood' => 111,
                'grabfood' => 112,
                'shopeefood' => 113,
            ];
            $kodeAkunCashless = $cashlessKodeMap[$postData['jenis_cashless']] ?? null;
            if ($kodeAkunCashless) {
                // Tambah saldo ke akun cashless (Debit)
                $akunModel->updateSaldo($kodeAkunCashless, $postData['grand_total'], 'debit');
            }

            // Tambah ke akun Penjualan (Kredit)
            $akunModel->updateSaldo(401, $postData['grand_total'], 'kredit');
            $postData['dibayar'] = $postData['grand_total'];
            $postData['kembalian'] = 0;
        }

        $detailTransaksiJson = $this->request->getPost('detail_transaksi');
        if (empty($detailTransaksiJson)) {
            return redirect()->back()->withInput()->with('error', 'Detail transaksi kosong atau tidak valid.');
        }

        $detailTransaksi = json_decode($detailTransaksiJson, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($detailTransaksi)) {
            return redirect()->back()->withInput()->with('error', 'Detail transaksi kosong atau tidak valid.');
        }

        $rules = [
            'nofaktur' => 'required',
            'tgl_jual' => 'required|valid_date',
            'jam_jual' => 'required',
            'nama_kasir' => 'required',
            'grand_total' => 'required|numeric',
            'metode_pembayaran' => 'required|in_list[cash,cashless]',
        ];

        if ($postData['metode_pembayaran'] === 'cash') {
            $rules['dibayar'] = 'required|numeric';
            $rules['kembalian'] = 'required|numeric';
        } elseif ($postData['metode_pembayaran'] === 'cashless') {
            $rules['jenis_cashless'] = 'required';
        }
        if (in_groups('admin')) {
            $rules['outlet_id'] = 'required|numeric';
        }

        if (!$this->validate($rules)) {
            $error_message = implode('<br>', $this->validator->getErrors());
            return redirect()->back()->withInput()->with('error', $error_message);
        }

        $jualModel = new JualModel();
        $detailModel = new DetailJualModel();
        $menuModel = new MenuPenjualanModel();
        $persediaanModel = new PersediaanOutletModel();
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $dataJual = [
                'no_faktur' => $postData['nofaktur'],
                'tgl_jual' => $postData['tgl_jual'],
                'jam_jual' => $postData['jam_jual'],
                'nama_kasir' => $postData['nama_kasir'],
                'grand_total' => $postData['grand_total'],
                'dibayar' => $postData['dibayar'],
                'kembalian' => $postData['kembalian'],
                'outlet_id' => $postData['outlet_id'],
                'metode_pembayaran' => $postData['metode_pembayaran'],
                'jenis_cashless' => $postData['metode_pembayaran'] === 'cashless' ? $postData['jenis_cashless'] : null,
            ];

            $jualModel->insert($dataJual);

            // Tambahkan ini setelah insert transaksi dan sebelum commit
            if ($postData['metode_pembayaran'] === 'cashless' && isset($kodeAkunCashless)) {
                $db = \Config\Database::connect();
                $akunModel = new \App\Models\AkunModel();

                // Ambil akun piutang (kas digital) berdasarkan kode
                $akunPiutang = $akunModel->where('kode_akun', $kodeAkunCashless)->first();
                $akunPenjualan = $akunModel->where('kode_akun', 401)->first(); // Kode akun penjualan

                if ($akunPiutang && $akunPenjualan) {
                    $jurnal = $db->table('jurnal_umum');
                    $tanggal = $postData['tgl_jual'];
                    $grandTotal = $postData['grand_total'];
                    $keterangan = 'Penjualan via ' . strtoupper($postData['jenis_cashless']);

                    // Debit ke Piutang (akun QRIS, GoFood, dll)
                    $jurnal->insert([
                        'tanggal' => $tanggal,
                        'akun_id' => $akunPiutang['id'],
                        'debit' => $grandTotal,
                        'kredit' => 0,
                        'keterangan' => 'Piutang dari ' . $keterangan,
                    ]);

                    // Kredit ke Penjualan
                    $jurnal->insert([
                        'tanggal' => $tanggal,
                        'akun_id' => $akunPenjualan['id'],
                        'debit' => 0,
                        'kredit' => $grandTotal,
                        'keterangan' => $keterangan,
                    ]);
                }
            }

            if ($postData['metode_pembayaran'] === 'cash') {
                $akunModel = new \App\Models\AkunModel();
                $jurnal = $db->table('jurnal_umum');

                // Mapping kode akun kas berdasarkan outlet
                $kasOutletMap = [
                    1 => 102, // Outlet Tembalang
                    2 => 103, // Outlet Pleburan
                    3 => 104, // dst
                    4 => 105,
                    5 => 106,
                ];

                $kodeAkunKas = $kasOutletMap[$postData['outlet_id']] ?? 102;

                $akunKas = $akunModel->where('kode_akun', $kodeAkunKas)->first();
                $akunPenjualan = $akunModel->where('kode_akun', 401)->first();

                if ($akunKas && $akunPenjualan) {
                    $jurnal->insert([
                        'tanggal' => $postData['tgl_jual'],
                        'akun_id' => $akunKas['id'],
                        'debit' => $postData['grand_total'],
                        'kredit' => 0,
                        'keterangan' => 'Penjualan tunai outlet ID ' . $postData['outlet_id'],
                    ]);

                    $jurnal->insert([
                        'tanggal' => $postData['tgl_jual'],
                        'akun_id' => $akunPenjualan['id'],
                        'debit' => 0,
                        'kredit' => $postData['grand_total'],
                        'keterangan' => 'Penjualan tunai outlet ID ' . $postData['outlet_id'],
                    ]);
                }
            }


            $idJual = $jualModel->getInsertID();

            $mapBahan = [
                'kulit'                => 'BSJ01',     // Kulit Kebab (pcs)
                'daging'               => ['beef' => 'BSJ03', 'chicken' => 'BSJ02'], // Olahan Daging (kg)
                'telur'                => 'BP001',     // Telur (kg) → konversi dari butir
                'mayo'                 => 'BP014',     // Mayonaise (kg)
                'saus_signature'       => 'SS01',      // Signature Sauce (kg)
                'saus_curry'           => 'BP017',     // Saos curry (kg)
                'saus_mentai'          => 'BP016',     // Saos mentai (kg)
                'saus_demiglace'       => 'BP018',     // Saos demiglace (kg)
                'red_chedar'           => 'BP012',     // Red cheddar (pcs / slice)
                'aluminium_foil'       => 'BP020',     // Alumunium foil (meter)
                'stiker'               => 'BP023',     // Stiker (pcs)
                'saus_sambal_sachet'   => 'BP019',     // Saos sambal sachet (pcs)
                'plastik_kresek'       => 'BP021',     // Plastik kresek (pcs)
                'selada'               => 'BP013',     // Selada (kg)
            ];


            foreach ($detailTransaksi as $item) {
                $detailModel->insert([
                    'id_jual'     => $idJual,
                    'kode_menu'   => $item['kode_menu'],
                    'kategori'    => $item['kategori'],
                    'nama_menu'   => $item['nama_menu'],
                    'harga'       => $item['harga'],
                    'qty'         => $item['qty'],
                    'total_harga' => $item['total_harga'],
                    'add_ons'     => $item['add_ons'] ?? '',
                    'extra'       => $item['extra'] ?? '',
                ]);

                $menu = $menuModel->where('kode_menu', $item['kode_menu'])->first();
                if (!$menu) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Menu tidak ditemukan: ' . $item['nama_menu']);
                }

                $komposisi = json_decode($menu['komposisi'], true);
                if (!$komposisi || json_last_error() !== JSON_ERROR_NONE) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Komposisi tidak valid untuk menu ' . $menu['nama_menu']);
                }

                $qty = (int)$item['qty'];
                $namaMenu = strtolower($menu['nama_menu']);
                $isBeef = stripos($namaMenu, 'beef') !== false;
                $isChicken = stripos($namaMenu, 'chicken') !== false;

                foreach ($komposisi as $namaBahan => $data) {
                    $jumlah = floatval($data['jumlah'] ?? 0);
                    $satuan = strtolower($data['satuan'] ?? '');

                    if ($jumlah <= 0) continue;

                    // Konversi satuan ke satuan stok
                    if ($satuan === 'butir' && $namaBahan === 'telur') {
                        $jumlah /= 16; // 1 butir = 1/16 kg
                    } elseif ($satuan === 'gr') {
                        $jumlah /= 1000; // gr → kg
                    } elseif ($satuan === 'cm') {
                        $jumlah /= 100; // cm → meter
                    }

                    // Ambil kode bahan dari mapping
                    if ($namaBahan === 'daging') {
                        $kodeBahan = $isBeef ? $mapBahan['daging']['beef'] : ($isChicken ? $mapBahan['daging']['chicken'] : null);
                    } else {
                        $kodeBahan = $mapBahan[$namaBahan] ?? null;
                    }

                    if (!$kodeBahan) {
                        $db->transRollback();
                        return redirect()->back()->with('error', 'Kode bahan tidak ditemukan untuk: ' . $namaBahan);
                    }

                    // Jumlah total yang dipakai
                    $totalPakai = round($qty * $jumlah, 2); // ✅ Pembulatan 2 angka desimal

                    if (!$persediaanModel->kurangiStok($outlet_id, $kodeBahan, $totalPakai)) {
                        $db->transRollback();
                        return redirect()->back()->with('error', 'Stok tidak cukup untuk bahan ' . $kodeBahan . ' pada menu ' . $menu['nama_menu']);
                    }
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \Exception('Gagal menyimpan transaksi.');
            }

            return redirect()->to(base_url('manajemen-penjualan/daftar-transaksi'))->with('success', 'Transaksi berhasil disimpan dan stok bahan berkurang.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function daftarTransaksi()
    {
        $jualModel = new \App\Models\JualModel();
        $outletModel = new \App\Models\OutletModel();

        $selectedOutlet = $this->request->getGet('outlet_id');
        $startDate      = $this->request->getGet('start_date');
        $endDate        = $this->request->getGet('end_date');

        // Builder query
        $query = $jualModel->select('jual.*, outlet.nama_outlet')
            ->join('outlet', 'outlet.id = jual.outlet_id', 'left');

        if ($selectedOutlet) {
            $query->where('jual.outlet_id', $selectedOutlet);
        }

        if ($startDate && $endDate) {
            $query->where('tgl_jual >=', $startDate)
                ->where('tgl_jual <=', $endDate);
        }

        $transaksi = $query->orderBy('tgl_jual', 'DESC')->paginate(10, 'transaksi');
        $pager = $jualModel->pager;

        return view('manajemen-penjualan/daftar_transaksi', [
            'tittle'         => 'Daftar Transaksi',
            'transaksi'      => $transaksi,
            'pager'          => $pager,
            'outlets'        => $outletModel->findAll(),
            'selectedOutlet' => $selectedOutlet,
            'startDate'      => $startDate,
            'endDate'        => $endDate
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

        return view('manajemen-penjualan/detail_transaksi', [
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

        return view('manajemen-penjualan/cetak_transaksi', [
            'transaksi' => $transaksi,
            'detail' => $detail
        ]);
    }

    public function cetakTerpilih()
    {
        $ids = $this->request->getPost('transaksi_ids');

        if (!$ids || !is_array($ids)) {
            return redirect()->back()->with('error', 'Tidak ada transaksi yang dipilih.');
        }

        $jualModel = new JualModel();
        $detailModel = new DetailJualModel();

        $transaksi_list = [];

        foreach ($ids as $id) {
            $transaksi = $jualModel
                ->select('jual.*, outlet.nama_outlet')
                ->join('outlet', 'outlet.id = jual.outlet_id', 'left')
                ->where('jual.id', $id)
                ->first();

            if ($transaksi) {
                $detail = $detailModel
                    ->select('detail_jual.*')
                    ->where('id_jual', $id)
                    ->findAll();

                $transaksi['detail'] = $detail;
                $transaksi_list[] = $transaksi;
            }
        }

        return view('manajemen-penjualan/cetak_terpilih', [
            'transaksi_list' => $transaksi_list
        ]);
    }

    public function cetakSemua()
    {
        $jualModel = new JualModel();
        $outletId = $this->request->getGet('outlet_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $transaksi = $jualModel->getDaftarTransaksi($outletId, $startDate, $endDate);

        return view('manajemen-penjualan/cetak_semua_transaksi', [
            'transaksi' => $transaksi
        ]);
    }
    // ===================================== INPUT TRANSAKSI END ===================================================== //

    // ===================================== PERSEDIAAN OUTLET START ===================================================== //

    public function tambahPersediaanOutlet()
    {
        $mode = $this->request->getPost('mode');
        $tanggal = $this->request->getPost('tanggal') ?? date('Y-m-d');

        $outlet_id = in_groups('admin')
            ? $this->request->getPost('outlet_id')
            : user()->outlet_id;

        $persediaanModel = new \App\Models\PersediaanOutletModel();
        $logMasukModel   = new \App\Models\LogPersediaanMasukModel();

        // 🔁 MODE: PRODUKSI SIGNATURE SAUCE
        if ($mode === 'produksi_signature_sauce') {
            $bawang  = (float) $this->request->getPost('bawang');
            $paprika = (float) $this->request->getPost('paprika');
            $lada    = (float) $this->request->getPost('lada');
            $oregano = (float) $this->request->getPost('oregano');
            $parsley = (float) $this->request->getPost('parsley');
            $saus    = (float) $this->request->getPost('saus_tomat');

            $total_bahan = $bawang + $paprika + $lada + $oregano + $parsley + $saus;

            if ($total_bahan <= 0) {
                return redirect()->to('manajemen-penjualan/persediaanOutlet')->with('error', 'Total bahan tidak boleh kosong.');
            }

            $jumlahProduksi = ($total_bahan / 1050) * 1450;

            // 🆕 Ganti kode dari tabel bahan (bukan BSJ lagi)
            $komposisi = [
                'BP011' => $bawang,   // Bawang Putih Bubuk
                'BP007' => $paprika,  // Paprika Bubuk
                'BP008' => $lada,     // Lada Putih Bubuk
                'BP010' => $oregano,  // Oregano
                'BP009' => $parsley,  // Parsley
                'BP015' => $saus      // Saos Tomat
            ];

            foreach ($komposisi as $kode => $jumlah) {
                if ($jumlah > 0) {
                    $stokTersedia = $persediaanModel->cekStok($outlet_id, $kode);
                    if ($stokTersedia < $jumlah) {
                        return redirect()->to('manajemen-penjualan/persediaanOutlet')
                            ->with('error', "Stok $kode tidak cukup. Tersedia: $stokTersedia gr, dibutuhkan: $jumlah gr");
                    }
                }
            }

            foreach ($komposisi as $kode => $jumlah) {
                if ($jumlah > 0) {
                    $persediaanModel->kurangiStok($outlet_id, $kode, $jumlah);
                }
            }

            $kodeSaus = 'SS01';
            $persediaanModel->tambahStok($outlet_id, $kodeSaus, $jumlahProduksi, $tanggal);

            $logMasukModel->insert([
                'outlet_id'   => $outlet_id,
                'kode_bahan' => $kodeSaus,
                'jumlah'     => $jumlahProduksi,
                'tanggal'    => $tanggal,
                'created_at' => date('Y-m-d H:i:s'),
                'keterangan' => 'Produksi Signature Sauce'
            ]);

            return redirect()->to('manajemen-penjualan/persediaanOutlet')
                ->with('success', 'Berhasil produksi Signature Sauce sebanyak ' . number_format($jumlahProduksi, 2) . ' gr.');
        }


        // 🔁 MODE: TAMBAH PERSEDIAAN BIASA
        $kode = $this->request->getPost('kode_bahan');
        $jumlah_input = $this->request->getPost('jumlah');

        if (!$kode || $jumlah_input === null || !$outlet_id || !$tanggal) {
            return redirect()->to('manajemen-penjualan/persediaanOutlet')->with('error', 'Data tidak lengkap.');
        }

        $jumlah = 0;
        $satuan = '';

        // 🔍 Cek di BSJ
        $bsjModel = new \App\Models\BSJModel();
        $bahan = $bsjModel->where('kode', $kode)->first();

        if ($bahan) {
            $satuan = strtolower($bahan['satuan']);
        } else {
            // 🔍 Jika tidak ada di BSJ, cek di Tabel Bahan
            $bahanModel = new \App\Models\BahanModel();
            $bahan = $bahanModel->where('kode', $kode)->first();

            if (!$bahan) {
                return redirect()->to('manajemen-penjualan/persediaanOutlet')->with('error', 'Kode bahan tidak ditemukan di BSJ atau Bahan.');
            }

            $satuan = strtolower($bahan['satuan']);
        }

        // 💡 Konversi satuan ke gram jika kg
        if ($satuan === 'kg') {
            $jumlah = (int) ($jumlah_input * 1000);
        } else {
            $jumlah = (int) $jumlah_input;
        }

        $persediaanModel->tambahStok($outlet_id, $kode, $jumlah, $tanggal);

        $logMasukModel->insert([
            'outlet_id'   => $outlet_id,
            'kode_bahan' => $kode,
            'jumlah'     => $jumlah,
            'tanggal'    => $tanggal,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('manajemen-penjualan/persediaanOutlet')
            ->with('success', 'Stok berhasil ditambahkan ke outlet.');
    }

    public function persediaanOutlet()
    {
        $bsjModel = new \App\Models\BSJModel();
        $bahanModel = new \App\Models\BahanModel();
        $outletModel = new \App\Models\OutletModel();
        $persediaanModel = new \App\Models\PersediaanOutletModel();

        $outlet_id = in_groups('admin')
            ? $this->request->getGet('outlet_id')
            : user()->outlet_id;

        if (!$outlet_id && in_groups('admin')) {
            $firstOutlet = $outletModel->first();
            $outlet_id = $firstOutlet ? $firstOutlet['id'] : null;
        }

        $persediaan = [];
        if ($outlet_id) {
            $persediaanRaw = $persediaanModel
                ->where('outlet_id', $outlet_id)
                ->findAll();

            foreach ($persediaanRaw as $item) {
                $kode = $item['kode_bahan'];

                // Cek di bsj
                $bahan = $bsjModel->where('kode', $kode)->first();

                if (!$bahan) {
                    // Jika tidak ditemukan di BSJ, cek di tabel bahan
                    $bahan = $bahanModel->where('kode', $kode)->first();
                }

                $item['nama_bahan'] = $bahan['nama'] ?? '-';
                $item['satuan'] = $bahan['satuan'] ?? '-';

                $persediaan[] = $item;
            }
        }

        // 🔁 Gabungkan BSJ dan BAHAN untuk dropdown
        $bsjList = $bsjModel->findAll();
        $bahanList = $bahanModel->findAll();

        foreach ($bsjList as &$item) {
            $item['asal'] = 'bsj';
        }
        foreach ($bahanList as &$item) {
            $item['asal'] = 'bahan';
        }

        $daftarBahan = array_merge($bsjList, $bahanList);

        return view('manajemen-penjualan/persediaan_outlet', [
            'bsj' => $daftarBahan,
            'outlets' => $outletModel->findAll(),
            'selected_outlet' => $outlet_id,
            'persediaan' => $persediaan,
            'tittle' => 'Manajemen Persediaan Outlet'
        ]);
    }

    public function rekapStokHarian()
    {
        // Ambil parameter dari GET (karena di routes pakai GET & POST)
        $tanggal = $this->request->getGet('tanggal') ?? date('Y-m-d');
        $outlet_id = in_groups('admin')
            ? $this->request->getGet('outlet_id')
            : user()->outlet_id;

        $kemarin = date('Y-m-d', strtotime($tanggal . ' -1 day'));

        $persediaanModel = new PersediaanOutletModel();
        $logMasukModel = new LogPersediaanMasukModel();
        $rekapModel = new LogPersediaanHarianModel();
        $penjualanModel = new DetailJualModel();

        $bahanList = $persediaanModel->where('outlet_id', $outlet_id)->findAll();

        foreach ($bahanList as $bahan) {
            $kode = $bahan['kode_bahan'];

            // Ambil stok akhir kemarin
            $stokKemarin = $rekapModel
                ->where('outlet_id', $outlet_id)
                ->where('kode_bahan', $kode)
                ->where('tanggal', $kemarin)
                ->first();

            $stok_awal = $stokKemarin['stok_akhir'] ?? 0;

            // Total masuk hari ini dari log persediaan masuk
            $masuk = $logMasukModel->where([
                'outlet_id' => $outlet_id,
                'kode_bahan' => $kode,
                'tanggal' => $tanggal
            ])->selectSum('jumlah')->first()['jumlah'] ?? 0;

            // Total keluar hari ini dari penjualan
            // Pastikan kamu punya fungsi getKeluarBahan di DetailJualModel
            $keluar = $penjualanModel->getKeluarBahan($outlet_id, $kode, $tanggal);

            $stok_akhir = $stok_awal + $masuk - $keluar;

            // Upsert data rekap stok harian
            // Pastikan method upsert di model LogPersediaanHarianModel sudah diimplementasikan
            $rekapModel->upsert([
                'outlet_id' => $outlet_id,
                'kode_bahan' => $kode,
                'tanggal' => $tanggal,
                'stok_awal' => $stok_awal,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'stok_akhir' => $stok_akhir,
            ]);
        }

        return redirect()->back()->with('success', 'Rekap stok harian berhasil dihitung.');
    }

    // ===================================== PERSEDIAAN OUTLET END ===================================================== //

    // ===================================== LAIN - LAIN START ===================================================== //
    public function laporanHarian()
    {
        $tanggal = $this->request->getGet('tanggal') ?? date('Y-m-d');
        $outletId = $this->request->getGet('outlet_id');

        $shiftModel = new ShiftKerjaModel();
        $pegawaiShiftModel = new PegawaiShiftModel();
        $jualModel = new JualModel();
        $buktiPembelianModel = new BuktiPembelianModel();
        $outletModel = new OutletModel();
        $userModel = new UserModel();

        $shifts = $shiftModel->findAll();
        $outlets = $outletModel->findAll();
        $data = [];

        // Tambahkan jenis pembayaran termasuk cash
        $jenisPembayaran = [
            'cash' => 0,
            'qris' => 0,
            'grabfood' => 0,
            'gofood' => 0,
            'shopeefood' => 0,
        ];

        // Persentase potongan untuk metode selain cash
        $potonganPersen = [
            'qris' => 0.007,
            'grabfood' => 0.18,
            'gofood' => 0.20,
            'shopeefood' => 0.20,
        ];

        foreach ($shifts as $shift) {
            $pegawaiShifts = $pegawaiShiftModel
                ->where('shift_id', $shift['id'])
                ->where('tanggal', $tanggal)
                ->findAll();

            foreach ($pegawaiShifts as $ps) {
                $user = $userModel->find($ps['user_id']);
                $outletUserId = $user['outlet_id'] ?? null;

                if ($outletId && $outletUserId != $outletId) {
                    continue;
                }

                $penjualan = $jualModel
                    ->where('outlet_id', $outletUserId)
                    ->where('tgl_jual', $tanggal)
                    ->where('jam_jual >=', $ps['jam_mulai'])
                    ->where('jam_jual <=', $ps['jam_selesai'])
                    ->findAll();

                foreach ($penjualan as $pj) {
                    $jenis = strtolower($pj['jenis_cashless'] ?? 'cash');
                    if (!array_key_exists($jenis, $jenisPembayaran)) {
                        $jenis = 'cash'; // fallback
                    }
                    $jenisPembayaran[$jenis] += $pj['grand_total'];
                }

                $pengeluaran = $buktiPembelianModel
                    ->where('outlet_id', $outletUserId)
                    ->where('tanggal', $tanggal)
                    ->findAll();

                $totalPenjualan = array_sum(array_column($penjualan, 'grand_total'));
                $totalPengeluaran = array_sum(array_column($pengeluaran, 'total'));

                $data[] = [
                    'shift' => $shift['nama_shift'],
                    'jam' => $ps['jam_mulai'] . ' - ' . $ps['jam_selesai'],
                    'kasir' => $user['username'] ?? 'Tidak diketahui',
                    'outlet' => $this->getOutletName($outlets, $outletUserId),
                    'total_penjualan' => $totalPenjualan,
                    'total_pengeluaran' => $totalPengeluaran,
                    'keterangan_pengeluaran' => implode(', ', array_column($pengeluaran, 'keterangan')),
                ];
            }
        }

        // Hitung potongan & bersih
        $rincianPotongan = [];
        foreach ($jenisPembayaran as $jenis => $total) {
            $potongan = isset($potonganPersen[$jenis]) ? $total * $potonganPersen[$jenis] : 0;
            $netto = $total - $potongan;

            $rincianPotongan[] = [
                'jenis' => ucfirst($jenis),
                'bruto' => $total,
                'potongan' => $potongan,
                'netto' => $netto,
                'persen' => $jenis === 'cash' ? 0 : $potonganPersen[$jenis] * 100,
            ];
        }

        return view('manajemen-penjualan/input_laporan_shift', [
            'tittle' => 'SIOK | Laporan Harian Shift',
            'laporan' => $data,
            'tanggal' => $tanggal,
            'outlets' => $outlets,
            'selectedOutlet' => $outletId,
            'rincianPotongan' => $rincianPotongan,
        ]);
    }

    // Helper untuk nama outlet
    private function getOutletName($outlets, $id)
    {
        foreach ($outlets as $outlet) {
            if ($outlet['id'] == $id) {
                return $outlet['nama_outlet'];
            }
        }
        return $id ? 'Unknown' : 'Semua Outlet';
    }

    public function hapusLaporanShift($id)
    {
        $laporanShiftModel = new \App\Models\LaporanShiftModel();
        $laporanShift = $laporanShiftModel->find($id);

        if (!$laporanShift) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $laporanShiftModel->delete($id);

        return redirect()->to('/manajemen-penjualan/laporanpenjualan?tanggal=' . $this->request->getPost('tanggal') . '&outlet_id=' . $this->request->getPost('outlet_id'))
            ->with('success', 'Laporan shift berhasil dihapus.');
    }

    public function hapusLaporanHarian()
    {
        $tanggal = $this->request->getPost('tanggal');
        $outlet_id = $this->request->getPost('outlet_id');

        if (!$tanggal || !$outlet_id) {
            return redirect()->back()->with('error', 'Tanggal dan Outlet tidak boleh kosong.');
        }

        $logModel = new LogPersediaanHarianModel();
        $deleted = $logModel->where([
            'tanggal' => $tanggal,
            'outlet_id' => $outlet_id
        ])->delete();

        if ($deleted) {
            return redirect()->back()->with('success', 'Laporan harian berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Tidak ada data yang dihapus.');
        }
    }

    public function tambahStokMasuk()
    {
        $outlet_id = $this->request->getPost('outlet_id');
        $kode_bahan = $this->request->getPost('kode_bahan');
        $jumlah = (int) $this->request->getPost('jumlah');
        $tanggal = $this->request->getPost('tanggal') ?? date('Y-m-d');

        if (!$outlet_id || !$kode_bahan || !$jumlah) {
            return redirect()->back()->with('error', 'Data stok masuk tidak lengkap.');
        }

        $persediaanModel = new PersediaanOutletModel();
        $logMasukModel = new LogPersediaanMasukModel();

        // Tambah stok di persediaan_outlet
        $persediaanModel->tambahStok($outlet_id, $kode_bahan, $jumlah, $tanggal);

        // Simpan log stok masuk
        $logMasukModel->insert([
            'outlet_id' => $outlet_id,
            'kode_bahan' => $kode_bahan,
            'jumlah' => $jumlah,
            'tanggal' => $tanggal,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Hitung ulang rekap stok harian
        $this->rekapStokHarian();

        return redirect()->back()->with('success', 'Stok berhasil ditambahkan dan direkap.');
    }
    // ===================================== LAIN - LAIN START ===================================================== //

    // =========================================== LAPORAN PENJUALAN START ============================================//
    public function laporanPenjualan()
    {
        $tanggalReview = $this->request->getGet('tanggal_review') ?: '';
        $tanggalDaftar = $this->request->getGet('tanggal_daftar') ?: '';
        $requestedOutletId = $this->request->getGet('outlet_id');

        $outletModel = new \App\Models\OutletModel();
        $outlets = $outletModel->findAll();

        $userId = user_id(); // Dari Myth:Auth
        $db = \Config\Database::connect();

        $builder = $db->table('users');
        $builder->select('users.id, users.username, users.outlet_id');
        $builder->where('users.id', $userId);

        $userInfo = $builder->get()->getRow();

        // Cek role
        $isAdmin = in_groups('admin');
        $isKeuangan = in_groups('keuangan');

        // Jika bukan admin dan keuangan, set outlet berdasarkan user login
        if (!$isAdmin && !$isKeuangan) {
            $outletId = $userInfo->outlet_id ?? null;
        } else {
            $outletId = $requestedOutletId;
        }

        $penjualan = [];
        $pengeluaran = [];
        $detailPengeluaran = [];
        $totalPenjualan = 0;
        $totalPengeluaran = 0;
        $detailMenuTerjual = [];
        $platforms = [
            'qris' => 0.007,
            'grabfood' => 0.18,
            'gofood' => 0.20,
            'shopeefood' => 0.20,
        ];
        $potonganData = [];

        if ($tanggalReview && $outletId) {
            $penjualan = $this->jualModel
                ->where('tgl_jual', $tanggalReview)
                ->where('outlet_id', $outletId)
                ->findAll();

            foreach ($penjualan as $jual) {
                $grandTotal = $jual['grand_total'];
                $metode = strtolower($jual['metode_pembayaran']);
                $jenis = strtolower($jual['jenis_cashless']);

                $potongan = 0;
                if ($metode === 'cashless' && isset($platforms[$jenis])) {
                    $potongan = $grandTotal * $platforms[$jenis];
                    if (!isset($potonganData[$jenis])) {
                        $potonganData[$jenis] = ['jumlah' => 1, 'total' => $potongan];
                    } else {
                        $potonganData[$jenis]['jumlah'] += 1;
                        $potonganData[$jenis]['total'] += $potongan;
                    }
                }

                $totalPenjualan += ($grandTotal - $potongan);
            }

            if (!empty($penjualan)) {
                $idJuals = array_column($penjualan, 'id');

                $builder = $db->table('detail_jual');
                $builder->select('nama_menu, SUM(qty) as total_qty, SUM(total_harga) as total_harga');
                $builder->whereIn('id_jual', $idJuals);
                $builder->groupBy('nama_menu');
                $detailMenuTerjual = $builder->get()->getResultArray();
            }

            $pengeluaran = $this->pembelianModel
                ->where('tanggal', $tanggalReview)
                ->where('outlet_id', $outletId)
                ->findAll();

            foreach ($pengeluaran as $item) {
                $totalPengeluaran += $item['total'];
            }

            $detailPengeluaran = $this->detailPembelianModel
                ->select('detail_pembelian_operasional.nama_barang, detail_pembelian_operasional.jumlah, detail_pembelian_operasional.total')
                ->join('pembelian_operasional', 'pembelian_operasional.id = detail_pembelian_operasional.pembelian_id')
                ->where('pembelian_operasional.tanggal', $tanggalReview)
                ->where('pembelian_operasional.outlet_id', $outletId)
                ->findAll();
        }

        $tanggalDaftar = $this->request->getGet('tanggalDaftar');

        // Ambil semua laporan jika tidak ada filter tanggal (langsung tampilkan)
        $laporanBuilder = $db->table('laporan_penjualan');
        $laporanBuilder->select('laporan_penjualan.*, outlet.nama_outlet');
        $laporanBuilder->join('outlet', 'outlet.id = laporan_penjualan.outlet_id');
        $laporanBuilder->orderBy('laporan_penjualan.tanggal', 'DESC');
        $laporanBuilder->orderBy('laporan_penjualan.outlet_id', 'ASC');

        if ($tanggalDaftar) {
            $laporanBuilder->where('laporan_penjualan.tanggal', $tanggalDaftar);
        }

        $laporanTersimpan = $laporanBuilder->get()->getResultArray();

        return view('manajemen-penjualan/laporan_penjualan', [
            'tittle' => 'Laporan Penjualan',
            'tanggalReview' => $tanggalReview,
            'tanggalDaftar' => $tanggalDaftar,
            'outlet_id' => $outletId,
            'penjualan' => $penjualan,
            'totalPenjualan' => $totalPenjualan,
            'totalPengeluaran' => $totalPengeluaran,
            'detailPengeluaran' => $detailPengeluaran,
            'detailMenuTerjual' => $detailMenuTerjual,
            'laporanTersimpan' => $laporanTersimpan,
            'outlets' => $outlets,
            'platforms' => $platforms,
            'potonganData' => $potonganData,
            'isAdmin' => $isAdmin,
            'isKeuangan' => $isKeuangan,
        ]);
    }


    public function simpanLaporanPenjualan()
    {
        $tanggal = $this->request->getPost('tanggal');
        $outletId = $this->request->getPost('outlet_id');
        $totalPenjualan = $this->request->getPost('total_penjualan');
        $totalPengeluaran = $this->request->getPost('total_pengeluaran');
        $keterangan = $this->request->getPost('keterangan_pengeluaran');
        $konfirmasi = $this->request->getPost('konfirmasi_simpan'); // tombol konfirmasi jika jam operasional belum selesai

        // Cek apakah laporan sudah ada
        $sudahAda = $this->laporanModel
            ->where('tanggal', $tanggal)
            ->where('outlet_id', $outletId)
            ->countAllResults();

        if ($sudahAda > 0) {
            return redirect()->back()->with('error', 'Laporan tanggal ini sudah disimpan.');
        }

        // Ambil jam selesai terakhir shift
        $shiftModel = new \App\Models\ShiftKerjaModel();
        $shiftTerakhir = $shiftModel->selectMax('jam_selesai')->first();
        $jamSelesaiTerakhir = $shiftTerakhir['jam_selesai']; // format: H:i:s
        $jamSekarang = date('H:i:s');

        // Cek apakah jam sekarang masih di bawah jam selesai shift, dan belum ada konfirmasi
        if ($jamSekarang < $jamSelesaiTerakhir && $konfirmasi != '1') {
            return redirect()->back()->withInput()->with('warning', 'Jam operasional belum berakhir. Yakin ingin simpan laporan penjualan hari ini? Klik lagi untuk konfirmasi.');
        }

        // Simpan ke database
        $this->laporanModel->save([
            'tanggal' => $tanggal,
            'outlet_id' => $outletId,
            'total_penjualan' => $totalPenjualan,
            'total_pengeluaran' => $totalPengeluaran,
            'keterangan_pengeluaran' => $keterangan,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('success', 'Laporan berhasil disimpan.');
    }


    public function cetakLaporanPenjualan()
    {
        $tanggalAwal = $this->request->getGet('tanggal_awal');
        $tanggalAkhir = $this->request->getGet('tanggal_akhir');
        $outletId = $this->request->getGet('outlet_id');

        // Ambil laporan yang sesuai periode
        $query = $this->laporanModel
            ->where('tanggal >=', $tanggalAwal)
            ->where('tanggal <=', $tanggalAkhir);

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('laporan_penjualan');
        $builder->select('laporan_penjualan.*, outlet.nama_outlet');
        $builder->join('outlet', 'outlet.id = laporan_penjualan.outlet_id');
        $builder->where('tanggal >=', $tanggalAwal);
        $builder->where('tanggal <=', $tanggalAkhir);

        if ($outletId) {
            $builder->where('laporan_penjualan.outlet_id', $outletId);
        }

        $builder->orderBy('tanggal', 'ASC');
        $builder->orderBy('outlet_id', 'ASC');

        $laporan = $builder->get()->getResultArray();

        // Hitung total
        $grandTotalPenjualan = array_sum(array_column($laporan, 'total_penjualan'));
        $grandTotalPengeluaran = array_sum(array_column($laporan, 'total_pengeluaran'));
        $grandTotalLaba = $grandTotalPenjualan - $grandTotalPengeluaran;

        return view('manajemen-penjualan/cetak_laporan_penjualan', [
            'laporan' => $laporan,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'grandTotalPenjualan' => $grandTotalPenjualan,
            'grandTotalPengeluaran' => $grandTotalPengeluaran,
            'grandTotalLaba' => $grandTotalLaba,
            'outletId' => $outletId,
        ]);
    }

    public function hapusLaporan()
    {
        $tanggal = $this->request->getPost('tanggal');
        $outletId = $this->request->getPost('outlet_id');

        $hapus = $this->laporanModel
            ->where('tanggal', $tanggal)
            ->where('outlet_id', $outletId)
            ->delete();

        if ($hapus) {
            return redirect()->back()->with('success', 'Laporan berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Gagal menghapus laporan.');
        }
    }
    // =========================================== LAPORAN PENJUALAN END ============================================//

    // =========================================== LAPORAN SHIFT START ============================================//
    public function inputLaporanShift()
    {
        $outletModel = new OutletModel();
        $shiftModel = new ShiftKerjaModel();
        $user = user();

        $data = [
            'shifts' => $shiftModel->findAll(),
            'tittle' => 'SIOK | Laporan Shift',
        ];

        if (in_groups('admin')) {
            $data['outlets'] = $outletModel->findAll();
        } elseif (in_groups('penjualan')) {
            $outletId = $user->outlet_id ?? null;
            $outlet = $outletModel->find($outletId);

            $data['outlets'] = [];
            $data['outlet_id'] = $outletId;
            $data['nama_outlet'] = $outlet['nama_outlet'] ?? 'Outlet Tidak Diketahui';
        }

        return view('manajemen-penjualan/input_laporan_shift', $data);
    }

    public function getDataShift()
    {
        $tanggal = $this->request->getGet('tanggal');
        $outletId = $this->request->getGet('outlet_id');
        $shiftId = $this->request->getGet('shift_id');

        $shiftModel = new ShiftKerjaModel();
        $jualModel = new JualModel();
        $pembelianModel = new \App\Models\PembelianOperasionalModel();
        $detailModel = new \App\Models\DetailPembelianOperasionalModel();

        $shift = $shiftModel->find($shiftId);
        $jamMulai = $shift['jam_mulai'];
        $jamSelesai = $shift['jam_selesai'];

        $penjualanQuery = $jualModel->where('outlet_id', $outletId);

        if ($jamMulai < $jamSelesai) {
            $penjualanQuery->where('tgl_jual', $tanggal)
                ->where('jam_jual >=', $jamMulai)
                ->where('jam_jual <=', $jamSelesai);
        } else {
            $tanggalBesok = date('Y-m-d', strtotime($tanggal . ' +1 day'));
            $penjualanQuery->groupStart()
                ->groupStart()
                ->where('tgl_jual', $tanggal)
                ->where('jam_jual >=', $jamMulai)
                ->groupEnd()
                ->orGroupStart()
                ->where('tgl_jual', $tanggalBesok)
                ->where('jam_jual <=', $jamSelesai)
                ->groupEnd()
                ->groupEnd();
        }

        $penjualan = $penjualanQuery->findAll();

        // Jenis pembayaran & potongan
        $jenisPembayaran = [
            'cash' => 0,
            'qris' => 0,
            'grabfood' => 0,
            'gofood' => 0,
            'shopeefood' => 0,
        ];
        $potonganPersen = [
            'qris' => 0.007,
            'grabfood' => 0.18,
            'gofood' => 0.20,
            'shopeefood' => 0.20,
        ];

        foreach ($penjualan as $pj) {
            $jenis = strtolower($pj['jenis_cashless'] ?? 'cash');
            if (!array_key_exists($jenis, $jenisPembayaran)) {
                $jenis = 'cash';
            }
            $jenisPembayaran[$jenis] += $pj['grand_total'];
        }

        $totalBruto = 0;
        $totalPotongan = 0;
        $rincianPotongan = [];

        foreach ($jenisPembayaran as $jenis => $total) {
            $potongan = isset($potonganPersen[$jenis]) ? $total * $potonganPersen[$jenis] : 0;
            $netto = $total - $potongan;

            $totalBruto += $total;
            $totalPotongan += $potongan;

            $rincianPotongan[] = [
                'jenis' => ucfirst($jenis),
                'bruto' => $total,
                'potongan' => $potongan,
                'netto' => $netto,
                'persen' => $jenis === 'cash' ? 0 : $potonganPersen[$jenis] * 100,
            ];
        }

        // Pengeluaran
        $pembelian = $pembelianModel
            ->where('outlet_id', $outletId)
            ->where('tanggal', $tanggal)
            ->findAll();

        $pembelianIds = array_column($pembelian, 'id');

        $detailPengeluaran = [];
        if (!empty($pembelianIds)) {
            $detailPengeluaran = $detailModel
                ->whereIn('pembelian_id', $pembelianIds)
                ->findAll();
        }

        $totalPengeluaran = array_sum(array_column($pembelian, 'total'));

        $keterangan = [];
        foreach ($detailPengeluaran as $detail) {
            $keterangan[] = $detail['nama_barang'] . ' Rp' . number_format($detail['total'], 0, ',', '.');
        }

        return $this->response->setJSON([
            'total_penjualan' => $totalBruto,
            'total_potongan' => $totalPotongan,
            'total_netto' => $totalBruto - $totalPotongan,
            'total_pengeluaran' => $totalPengeluaran,
            'keterangan_pengeluaran' => implode(", ", $keterangan),
            'rincian_penjualan' => $rincianPotongan
        ]);
    }

    public function simpanLaporanShift()
    {
        $laporanModel = new LaporanShiftModel();

        $data = [
            'tanggal' => $this->request->getPost('tanggal'),
            'outlet_id' => $this->request->getPost('outlet_id'),
            'shift_id' => $this->request->getPost('shift_id'),
            'user_id' => user_id(),
            'total_penjualan' => floatval($this->request->getPost('total_penjualan')), // gunakan total netto
            'total_pengeluaran' => floatval($this->request->getPost('total_pengeluaran')),
            'keterangan_pengeluaran' => $this->request->getPost('keterangan_pengeluaran'),
        ];

        $laporanModel->insert($data);

        return redirect()->back()->with('success', 'Laporan shift berhasil disimpan.');
    }
    // =========================================== LAPORAN SHIFT END ============================================//

    // ===================================== BTKL START ===================================================== //
    public function btklForm()
    {
        if (!in_groups(['admin', 'keuangan'])) {
            return redirect()->to('login');
        }

        $gaji_per_shift = 40000;
        $start = $this->request->getPost('start_date');
        $end = $this->request->getPost('end_date');
        $results = [];
        $totalKeseluruhan = 0;

        if ($start && $end) {
            $db = \Config\Database::connect();
            $builder = $db->table('users');
            $builder->select('users.id, users.username, users.outlet_id');
            $builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');
            $builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');
            $builder->where('auth_groups.name', 'penjualan');
            $users = $builder->get()->getResult();

            $pegawaiShiftModel = new \App\Models\PegawaiShiftModel();
            $btklModel = new \App\Models\BTKLModel();

            foreach ($users as $user) {
                $jumlahShift = $pegawaiShiftModel
                    ->where('user_id', $user->id)
                    ->where('tanggal >=', $start)
                    ->where('tanggal <=', $end)
                    ->countAllResults();

                if ($jumlahShift > 0) {
                    $totalGaji = $jumlahShift * $gaji_per_shift;
                    $totalKeseluruhan += $totalGaji;

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
                        'username' => $user->username,
                        'jumlah_shift' => $jumlahShift,
                        'total_gaji' => $totalGaji,
                    ];
                }
            }
        }

        return view('manajemen-penjualan/btkl_form', [
            'tittle' => 'Perhitungan Gaji Shift (BTKL)',
            'start' => $start,
            'end' => $end,
            'results' => $results,
            'gaji_per_shift' => $gaji_per_shift,
            'total_keseluruhan' => $totalKeseluruhan
        ]);
    }

    public function btkl()
    {
        if (!in_groups(['admin', 'keuangan'])) {
            return redirect()->to('login');
        }

        helper('number');

        $btklModel = new \App\Models\BTKLModel();
        $userModel = new \Myth\Auth\Models\UserModel();
        $outletModel = new \App\Models\OutletModel();

        // Ambil filter dari GET
        $start = $this->request->getGet('start_date');
        $end = $this->request->getGet('end_date');
        $outletFilter = $this->request->getGet('outlet_id');
        $userFilter = $this->request->getGet('user_id');

        // Ambil semua outlet untuk filter
        $outlets = $outletModel->findAll();

        // Ambil user role penjualan
        $userQuery = $userModel
            ->select('id, username, outlet_id')
            ->whereIn('id', function ($builder) {
                $builder->select('user_id')
                    ->from('auth_groups_users')
                    ->where('group_id', 3); // role penjualan
            });

        if ($outletFilter) {
            $userQuery->where('outlet_id', $outletFilter);
        }

        $users = $userQuery->findAll();

        // Ambil data dari tabel btkl
        $btklModel->select('btkl.*, users.username, outlet.nama_outlet')
            ->join('users', 'users.id = btkl.user_id')
            ->join('outlet', 'outlet.id = users.outlet_id');

        if ($start && $end) {
            $btklModel->where('periode_mulai', $start)
                ->where('periode_selesai', $end);
        }

        if ($outletFilter) {
            $btklModel->where('btkl.outlet_id', $outletFilter);
        }

        if ($userFilter) {
            $btklModel->where('btkl.user_id', $userFilter);
        }

        $btklModel->orderBy('btkl.outlet_id', 'ASC')->orderBy('btkl.total_gaji', 'DESC');

        $btklData = $btklModel->findAll();

        // Susun rekap
        $rekap = [];
        foreach ($btklData as $row) {
            $outlet = $row['nama_outlet'];
            $user = $row['username'];

            if (!isset($rekap[$outlet])) $rekap[$outlet] = [];

            $rekap[$outlet][$user] = [
                'total_shift' => $row['jumlah_shift'],
                'total_gaji' => $row['total_gaji'],
            ];
        }

        return view('manajemen-penjualan/btkl', [
            'tittle' => 'Daftar Gaji Karyawan',
            'rekap' => $rekap,
            'gaji_per_shift' => 40000,
            'outlets' => $outlets,
            'users' => $users,
            'filter' => [
                'start_date' => $start,
                'end_date' => $end,
                'outlet_id' => $outletFilter,
                'user_id' => $userFilter,
            ]
        ]);
    }
    // ===================================== BTKL END ===================================================== //

    // ===================================== INPUT JADWAL SHIFT ===================================================== //
    public function inputShift()
    {
        if (!in_groups(['admin', 'keuangan', 'penjualan'])) {
            return redirect()->to('login');
        }

        $db = \Config\Database::connect();
        $shiftModel = new ShiftKerjaModel();
        $outletModel = new \App\Models\OutletModel();

        $user = user(); // user login
        $userId = $user->id;
        $outletId = $user->outlet_id;

        // Ambil semua shift
        $shifts = $shiftModel->findAll();

        // Cek role login
        if (in_groups('penjualan')) {
            // Jika penjualan, hanya tampilkan outlet sesuai user
            $outlet = $outletModel->find($outletId);

            // Ambil hanya user penjualan di outlet tersebut
            $builder = $db->table('users');
            $builder->select('users.id, users.username, users.outlet_id');
            $builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');
            $builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');
            $builder->where('auth_groups.name', 'penjualan');
            $builder->where('users.outlet_id', $outletId);
            $users = $builder->get()->getResultArray();

            $data = [
                'outlets' => [$outlet], // satu outlet saja
                'users'   => $users,
                'shifts'  => $shifts,
                'readonly_outlet' => true,
                'tittle'  => 'Input Jadwal Shift'
            ];
        } else {
            // Untuk admin dan keuangan, semua data
            $outlets = $outletModel->findAll();

            $users = $db->table('users')
                ->select('users.id, users.username, users.outlet_id')
                ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
                ->where('auth_groups_users.group_id', 3) // pastikan 3 = group_id penjualan
                ->where('users.outlet_id', $outletId)
                ->get()
                ->getResultArray();

            $data = [
                'outlets' => $outlets,
                'users'   => $users,
                'shifts'  => $shifts,
                'readonly_outlet' => false,
                'tittle'  => 'Input Jadwal Shift'
            ];
        }

        return view('manajemen-penjualan/input_shift', $data);
    }

    public function dataShift()
    {
        if (!in_groups(['admin', 'keuangan', 'penjualan'])) {
            return redirect()->to('login');
        }

        $pegawaiShiftModel = new \App\Models\PegawaiShiftModel();
        $outletModel = new \App\Models\OutletModel();

        $user = user();
        $userOutletId = $user->outlet_id;

        // Ambil filter tanggal
        $start = $this->request->getGet('start_date') ?? date('Y-m-d');
        $end   = $this->request->getGet('end_date') ?? date('Y-m-d');

        // Ambil filter outlet (jika admin/keuangan)
        $outletFilter = $this->request->getGet('outlet_id');

        // Mulai query builder
        $builder = $pegawaiShiftModel
            ->select('pegawai_shift.*, users.username, shift_kerja.nama_shift, shift_kerja.jam_mulai, shift_kerja.jam_selesai, users.outlet_id, outlet.nama_outlet')
            ->join('users', 'users.id = pegawai_shift.user_id')
            ->join('shift_kerja', 'shift_kerja.id = pegawai_shift.shift_id')
            ->join('outlet', 'outlet.id = users.outlet_id')
            ->where('pegawai_shift.tanggal >=', $start)
            ->where('pegawai_shift.tanggal <=', $end);

        // Filter outlet berdasarkan role
        if (in_groups('penjualan')) {
            // Hanya data dari outlet user login
            $builder->where('users.outlet_id', $userOutletId);
        } elseif ($outletFilter) {
            // Jika admin/keuangan dan memilih outlet tertentu
            $builder->where('users.outlet_id', $outletFilter);
        }

        $shifts = $builder->orderBy('pegawai_shift.tanggal', 'DESC')->findAll();

        $data = [
            'tittle' => 'Data Jadwal Shift',
            'shifts' => $shifts,
            'start_date' => $start,
            'end_date' => $end,
            'outlet_id' => $outletFilter ?? '',
            'outlets' => (in_groups(['admin', 'keuangan'])) ? $outletModel->findAll() : [],
        ];

        return view('manajemen-penjualan/data_shift', $data);
    }

    public function deleteShift($id)
    {
        $pegawaiShiftModel = new \App\Models\PegawaiShiftModel();
        $pegawaiShiftModel->delete($id);
        return redirect()->to('manajemen-penjualan/data-shift')->with('success', 'Data shift berhasil dihapus.');
    }

    public function simpanShift()
    {
        $userIds  = $this->request->getPost('user_id');
        $shiftId  = $this->request->getPost('shift_id');
        $tanggal  = $this->request->getPost('tanggal');

        // Pastikan $userIds adalah array
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        // Validasi shift
        $shiftModel = new \App\Models\ShiftKerjaModel();
        $shift = $shiftModel->find($shiftId);
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

        return redirect()->to('manajemen-penjualan/input-shift')->with('success', 'Shift berhasil disimpan');
    }


    public function getUsersByOutlet($outletId)
    {
        $db = \Config\Database::connect();

        $users = $db->table('users')
            ->select('users.id, users.username')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
            ->where('auth_groups.name', 'penjualan')
            ->where('users.outlet_id', $outletId)
            ->get()
            ->getResultArray();

        return $this->response->setJSON($users);
    }
    // ===================================== INPUT JADWAL SHIFT END ===================================================== //

    // ====================== PEMBELIAN OPERASIONAL START ===================== //
    public function pembelian_operasional()
    {
        $user = user();
        $outletModel = new OutletModel();
        $pembelianModel = new PembelianOperasionalModel();

        $data['tittle'] = 'Daftar Pembelian Operasional';

        // Ambil filter dari query string
        $selectedOutletId = $this->request->getGet('outlet_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $builder = $pembelianModel
            ->select('pembelian_operasional.*, outlet.nama_outlet')
            ->join('outlet', 'outlet.id = pembelian_operasional.outlet_id');

        // Jika admin atau keuangan, bisa filter outlet
        if (in_groups(['admin', 'keuangan'])) {
            $data['outlets'] = $outletModel->findAll();
            $data['selectedOutletId'] = $selectedOutletId;

            if ($selectedOutletId) {
                $builder->where('pembelian_operasional.outlet_id', $selectedOutletId);
            }
        } else {
            // Selain admin/keuangan hanya bisa lihat outlet sendiri
            $data['outlets'] = null;
            $data['selectedOutletId'] = $user->outlet_id;
            $builder->where('pembelian_operasional.outlet_id', $user->outlet_id);
        }

        // Filter berdasarkan tanggal jika ada
        if ($startDate && $endDate) {
            $builder->where('tanggal >=', $startDate);
            $builder->where('tanggal <=', $endDate);
        }

        $pembelian = $builder
            ->orderBy('tanggal', 'DESC')
            ->findAll();

        // Ambil detail item
        foreach ($pembelian as &$row) {
            $row['item'] = $pembelianModel->getDetailPembelian($row['id']);
        }

        $data['pembelian_operasional'] = $pembelian;
        $data['filter'] = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        return view('manajemen-penjualan/pembelian_operasional', $data);
    }

    public function detail_pembelian_operasional($id)
    {
        $pembelianModel = new PembelianOperasionalModel();

        // Ambil data pembelian utama
        $pembelian = $pembelianModel->find($id);
        if (!$pembelian) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data pembelian tidak ditemukan.');
        }

        // Ambil data detail barang
        $pembelian['item'] = $pembelianModel->getDetailPembelian($id);

        $data['pembelian'] = $pembelian;
        $data['tittle'] = 'Detail Pembelian';

        return view('manajemen-penjualan/detail_pembelian_operasional', $data);
    }

    public function tambah_pembelian_operasional()
    {
        $user = user();
        $outletModel = new \App\Models\OutletModel();
        $outlet = $outletModel->find($user->outlet_id);

        return view('manajemen-penjualan/tambah_pembelian_operasional', [
            'tittle' => 'Tambah Pembelian Operasional',
            'outlet' => $outlet
        ]);
    }

    public function simpan_pembelian_operasional()
    {
        $pembelianModel = new PembelianOperasionalModel();
        $detailModel = new DetailPembelianOperasionalModel();

        $outlet_id = $this->request->getPost('outlet_id');
        $tanggal = $this->request->getPost('tanggal');
        $nama_barang = $this->request->getPost('nama_barang');
        $jumlah = $this->request->getPost('jumlah');
        $total = $this->request->getPost('total');

        // Hitung total keseluruhan
        $grandTotal = array_sum($total);

        // Upload bukti
        $bukti = $this->request->getFile('bukti');
        $buktiName = null;
        if ($bukti && $bukti->isValid() && !$bukti->hasMoved()) {
            $buktiName = $bukti->getRandomName();
            $bukti->move('uploads/bukti', $buktiName);
        }

        // Simpan ke tabel pembelian_operasional
        $pembelianId = $pembelianModel->insert([
            'tanggal' => $tanggal,
            'outlet_id' => $outlet_id,
            'total' => $grandTotal,
            'bukti' => $buktiName
        ]);

        // Simpan ke detail_pembelian_operasional
        foreach ($nama_barang as $i => $nama) {
            $detailModel->insert([
                'pembelian_id' => $pembelianId,
                'nama_barang' => $nama,
                'jumlah' => $jumlah[$i],
                'total' => $total[$i]
            ]);
        }

        // === Otomatis simpan ke jurnal_umum ===
        $db = \Config\Database::connect();
        $akunModel = new \App\Models\AkunModel();
        $jurnal = $db->table('jurnal_umum');

        // Mapping akun kas outlet berdasarkan outlet_id
        $kasOutletMap = [
            1 => 102,
            2 => 103,
            3 => 104,
            4 => 105,
            5 => 106,
        ];

        // Ambil kode akun kas sesuai outlet ID
        $kodeAkunKas = $kasOutletMap[$outlet_id] ?? null;

        if ($kodeAkunKas) {
            $akunKas = $akunModel->where('kode_akun', $kodeAkunKas)->first();
            $akunOperasional = $akunModel->where('kode_akun', 507)->first();

            if ($akunKas && $akunOperasional) {
                // Hitung total pembelian
                $totalPembelian = 0;
                foreach ($total as $jumlah) {
                    $totalPembelian += $jumlah;
                }

                // Simpan jurnal debit untuk beban operasional penjualan
                $jurnal->insert([
                    'tanggal' => $tanggal,
                    'akun_id' => $akunOperasional['id'],
                    'debit' => $totalPembelian,
                    'kredit' => 0,
                    'keterangan' => 'Pembelian operasional outlet ID ' . $outlet_id
                ]);

                // Simpan jurnal kredit untuk kas outlet
                $jurnal->insert([
                    'tanggal' => $tanggal,
                    'akun_id' => $akunKas['id'],
                    'debit' => 0,
                    'kredit' => $totalPembelian,
                    'keterangan' => 'Pengeluaran kas outlet ID ' . $outlet_id . ' untuk pembelian operasional'
                ]);
            }
        }


        return redirect()->to(base_url('manajemen-penjualan/pembelian-operasional'))->with('success', 'Pembelian berhasil disimpan.');
    }
    // ===================================== PEMEELIAN OPERASIONAL END ===================================================== //

    // ===================================== PERMINTAAN START ===================================================== //
    // 1. Daftar permintaan
    public function permintaan()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');
        $perPage   = 10; // Jumlah item per halaman

        $bahanModel   = new \App\Models\BahanModel();
        $bsjModel     = new \App\Models\BSJModel();
        $outletModel  = new \App\Models\OutletModel();
        $userModel    = model('UserModel');

        // Siapkan query builder
        $builder = $this->permintaanModel->orderBy('tanggal', 'DESC');

        if ($startDate && $endDate) {
            $builder->where('tanggal >=', $startDate)
                ->where('tanggal <=', $endDate);
        }

        // Gunakan pagination
        $list = $builder->paginate($perPage, 'permintaan');
        $pager = $this->permintaanModel->pager;

        $permintaan = [];
        foreach ($list as $p) {
            // Ambil nama outlet
            $p['nama_outlet'] = '-';
            if (isset($p['created_by'])) {
                $user = $userModel->find($p['created_by']);
                if ($user && isset($user->outlet_id)) {
                    $outlet = $outletModel->find($user->outlet_id);
                    $p['nama_outlet'] = $outlet['nama_outlet'] ?? '-';
                }
            }

            // Ambil detail barang
            $barangList = $this->detailPermintaanModel
                ->where('permintaan_id', $p['id'])
                ->findAll();

            foreach ($barangList as &$b) {
                if ($b['tipe'] === 'bsj') {
                    $barang = $bsjModel->where('kode', $b['kode_bahan'])->first();
                } else {
                    $barang = $bahanModel->where('kode', $b['kode_bahan'])->first();
                }
                $b['nama'] = $barang['nama'] ?? $b['nama']; // fallback
            }

            $p['barang'] = $barangList;
            $permintaan[] = $p;
        }

        return view('manajemen-penjualan/data_permintaan', [
            'tittle' => 'Data Permintaan Barang',
            'permintaan' => $permintaan,
            'filter' => [
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ],
            'pager' => $pager
        ]);
    }


    // 2. Form input permintaan
    public function formPermintaan()
    {
        $bsjModel = new BSJModel();
        $bahanModel = new BahanModel();

        // Ambil data dari tabel bsj
        $barangBSJ = $bsjModel
            ->select('id, nama, satuan')
            ->findAll();

        // Ambil data dari tabel bahan
        $barangBahan = $bahanModel
            ->select('id, kategori, nama, satuan')
            ->findAll();

        return view('manajemen-penjualan/form_permintaan', [
            'tittle'      => 'Form Permintaan Barang ke Produksi',
            'barang_bsj'  => $barangBSJ,
            'bahan'       => $barangBahan,
        ]);
    }

    // 3. Simpan permintaan
    public function storePermintaan()
    {
        $data = $this->request->getPost();

        $bahanModel = new \App\Models\BahanModel();
        $bsjModel   = new \App\Models\BSJModel();

        $permintaanId = $this->permintaanModel->insert([
            'tanggal'    => $data['tanggal'],
            'catatan'    => $data['catatan'] ?? '',
            'tujuan'     => 'produksi',
            'created_by' => user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $detail = [];
        foreach ($data['barang'] as $b) {
            // Ambil nama dari database berdasarkan tipe dan kode
            if ($b['tipe'] === 'bsj') {
                $barang = $bsjModel->where('kode', $b['barang_id'])->first();
            } else {
                $barang = $bahanModel->where('kode', $b['barang_id'])->first();
            }

            $namaBarang = $barang['nama'] ?? $b['nama'] ?? '-';

            $detail[] = [
                'permintaan_id' => $permintaanId,
                'tipe'          => $b['tipe'],
                'kode_bahan'    => $b['barang_id'],
                'nama'          => $namaBarang,
                'jumlah'        => $b['jumlah'],
                'satuan'        => $b['satuan'],
                'created_at'    => date('Y-m-d H:i:s')
            ];
        }

        $this->detailPermintaanModel->insertBatch($detail);

        // Notifikasi
        helper('notifikasi_helper');
        $isi = 'Ada permintaan barang baru dari penjualan.';
        $pengirimRole = in_groups('penjualan') ? 'penjualan' : 'unknown';
        kirimNotifikasi('produksi', 'permintaan', $isi, $permintaanId, null, $pengirimRole);

        return redirect()->to('/manajemen-penjualan/permintaan')->with('success', 'Permintaan berhasil dikirim.');
    }

    // 4. Detail permintaan
    public function detailPermintaan($id)
    {
        $permintaan = $this->permintaanModel->find($id);
        if (!$permintaan) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $barang = $this->detailPermintaanModel->where('permintaan_id', $id)->findAll();

        return view('manajemen-penjualan/detail_permintaan', [
            'tittle'      => 'Detail Permintaan Barang',
            'permintaan' => $permintaan,
            'barang'     => $barang
        ]);
    }

    // 5. Hapus permintaan
    public function hapusPermintaan($id)
    {
        $this->detailPermintaanModel->where('permintaan_id', $id)->delete();
        $this->permintaanModel->delete($id);

        return redirect()->to('/manajemen-penjualan/permintaan')->with('success', 'Permintaan berhasil dihapus.');
    }

    // ===================================== PERMINTAAN END ===================================================== //

    // ===================================== HPP PENJUALAN START ===================================================== //
    // Hpp Penjualan
    public function hppPenjualan()
    {
        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');

        if ($start && $end) {
            $hppBsjModel        = new HPPModel();
            $btklModel          = new BtklModel();
            $operasionalModel   = new PembelianOperasionalModel();

            // 1. Total biaya produksi dari hpp_bsj
            $total_biaya_hpp = $hppBsjModel
                ->where('created_at >=', $start)
                ->where('created_at <=', $end)
                ->selectSum('total_biaya')
                ->first()['total_biaya'] ?? 0;

            // 2. Total produksi dari hpp_bsj
            $total_produksi = $hppBsjModel
                ->where('created_at >=', $start)
                ->where('created_at <=', $end)
                ->selectSum('jumlah_produksi')
                ->first()['jumlah_produksi'] ?? 0;

            // 3. Total gaji dari tabel btkl
            $total_btkl = $btklModel
                ->where('created_at >=', $start)
                ->where('created_at <=', $end)
                ->selectSum('total_gaji')
                ->first()['total_gaji'] ?? 0;

            // 4. Total pembelian operasional
            $total_operasional = $operasionalModel
                ->where('tanggal >=', $start)
                ->where('tanggal <=', $end)
                ->selectSum('total')
                ->first()['total'] ?? 0;

            // 5. Hitung jumlah hari
            $days = (new Time($start))->difference(new Time($end))->getDays() + 1;

            // 6. Hitung total biaya keseluruhan
            $total_semua_biaya = $total_biaya_hpp + $total_btkl + $total_operasional;

            // 7. Hitung HPP Harian
            $hpp_per_hari = $days > 0 ? $total_semua_biaya / 30 : 0;

            // 8. Hitung HPP Penjualan per porsi
            $hpp_per_porsi = $total_produksi > 0 ? $hpp_per_hari / $total_produksi : 0;

            return view('manajemen-penjualan/hpp_penjualan', [
                'tittle'              => 'HPP Penjualan',
                'start'               => $start,
                'end'                 => $end,
                'days'                => $days,
                'total_biaya_hpp'     => $total_biaya_hpp,
                'total_produksi'      => $total_produksi,
                'total_btkl'          => $total_btkl,
                'total_operasional'   => $total_operasional,
                'total_semua_biaya'   => $total_semua_biaya,
                'hpp_per_hari'        => $hpp_per_hari,
                'hpp_per_porsi'       => $hpp_per_porsi,
            ]);
        }

        // Jika belum ada filter tanggal, tetap tampilkan form
        return view('manajemen-penjualan/hpp_penjualan', [
            'tittle' => 'HPP Penjualan'
        ]);
    }

    public function simpanHppPenjualan()
    {
        if (!in_groups(['admin', 'keuangan'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses menyimpan data HPP.');
        }

        $model = new HppPenjualanModel();

        $data = [
            'start_date'         => $this->request->getPost('start'),
            'end_date'           => $this->request->getPost('end'),
            'jumlah_hari'        => $this->request->getPost('days'),
            'total_biaya_hpp'    => $this->request->getPost('total_biaya_hpp'),
            'total_produksi'     => $this->request->getPost('total_produksi'),
            'total_btkl'         => $this->request->getPost('total_btkl'),
            'total_operasional'  => $this->request->getPost('total_operasional'),
            'total_semua_biaya'  => $this->request->getPost('total_semua_biaya'),
            'hpp_per_hari'       => $this->request->getPost('hpp_per_hari'),
            'hpp_per_porsi'      => $this->request->getPost('hpp_per_porsi'),
        ];

        // Cek apakah sudah ada data untuk periode tersebut
        $existing = $model->where('start_date', $data['start_date'])
            ->where('end_date', $data['end_date'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Data HPP untuk periode ini sudah ada.');
        }

        $model->save($data);

        return redirect()->back()->with('success', 'Data HPP Penjualan berhasil disimpan.');
    }
    // ===================================== HPP PENJUALAN START ===================================================== //
}
