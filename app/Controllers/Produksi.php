<?php

namespace App\Controllers;

use App\Models\PembelianModel;
use App\Models\PemasokModel;
use App\Models\BahanModel;
use App\Models\DetailPembelianModel;
use App\Models\BSJModel;
use App\Models\BiayaTenagaKerjaModel;
use App\Models\BiayaOverheadModel;
use App\Models\ProduksiModel;
use App\Models\DetailProduksiModel;
use App\Models\KomposisiBahanBSJModel;
use App\Models\NotifikasiModel;
use App\Models\OutletModel;
use App\Models\AkunModel;
use App\Models\KartuPersediaanModel;
use App\Models\KartuPersediaanBSJModel;


class Produksi extends BaseController
{
    public function index()
    {
        // Pastikan hanya admin dan produksi yang bisa akses
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $data['tittle'] = 'SIOK | Dashboard';
        return view('produksi/index_produksi', $data);
    }

    //PEMBELIAN
    public function pembelian()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $pembelianModel = new PembelianModel();
        $detailModel    = new DetailPembelianModel();
        $bahanModel     = new BahanModel();
        $pemasokModel   = new PemasokModel();

        $dataPembelian = $pembelianModel
            ->select('pembelian.*, pemasok.nama AS nama_pemasok')
            ->join('pemasok', 'pemasok.id = pembelian.pemasok_id')
            ->orderBy('pembelian.tanggal', 'DESC')
            ->findAll();

        $pembelian = [];

        foreach ($dataPembelian as $row) {
            $detail = $detailModel->where('pembelian_id', $row['id'])->findAll();

            $items = [];
            foreach ($detail as $d) {
                $bahan = $bahanModel->find($d['bahan_id']);
                $items[] = [
                    'nama'   => $bahan['nama'],
                    'jumlah' => $d['jumlah'],
                    'satuan' => $bahan['satuan']
                ];
            }

            $row['item'] = $items;
            $pembelian[] = $row;
        }

        return view('produksi/pembelian/index', [
            'tittle'     => 'Daftar Pembelian',
            'pembelian'  => $pembelian
        ]);
    }

    public function simpanPembelian()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $pembelianModel       = new PembelianModel();
        $bahanModel           = new BahanModel();
        $detailPembelianModel = new DetailPembelianModel();
        $akunModel            = new AkunModel();
        $kartuModel = new KartuPersediaanModel();
        $transaksiModel = new \App\Models\TransaksiBahanModel();

        $tanggal    = $this->request->getPost('tanggal');
        $noNota     = $this->request->getPost('no_nota');
        $pemasokId  = $this->request->getPost('pemasok_id');
        $jenisPembelian = $this->request->getPost('jenis_pembelian');
        $total      = 0;

        // simpan file upload bukti pembelian
        $bukti = $this->request->getFile('bukti');
        $namaFile = null;

        if ($bukti && $bukti->isValid() && !$bukti->hasMoved()) {
            $namaFile = $bukti->getRandomName();
            $bukti->move('uploads/bukti_pembelian/', $namaFile);
        }

        // Status barang: jika tunai langsung diterima, jika kredit/PO belum diterima
        $statusBarang = ($jenisPembelian === 'tunai') ? 'sudah_diterima' : 'belum_diterima';

        // Simpan data pembelian (header)
        $pembelianId = $pembelianModel->insert([
            'no_nota'    => $noNota,
            'tanggal'    => $tanggal,
            'pemasok_id' => $pemasokId,
            'total'      => 0, // nanti diupdate setelah hitung semua subtotal
            'bukti_transaksi' => $namaFile,
            'jenis_pembelian' => $jenisPembelian,
            'status_barang'   => $statusBarang
        ]);

        // Loop simpan detail per item
        foreach ($this->request->getPost('bahan_id') as $key => $bahanId) {
            $jumlah = $this->request->getPost('jumlah')[$key];
            $harga  = $this->request->getPost('harga_satuan')[$key];
            $bahan  = $bahanModel->find($bahanId);
            $satuan = strtolower($bahan['satuan']);
            // Konversi jumlah sesuai satuan
            if ($satuan === 'kg') {
                $jumlah_db = $jumlah * 1000; // simpan dalam gram
            } elseif ($satuan === 'liter') {
                $jumlah_db = $jumlah * 1000; // simpan dalam ml
            } else {
                $jumlah_db = $jumlah; // pcs dan meter tetap
            }
            $subtotal = (float)$jumlah * (float)$harga;

            // Simpan ke detail pembelian
            $detailPembelianModel->save([
                'pembelian_id' => $pembelianId,
                'bahan_id'     => $bahanId,
                'jumlah'       => $jumlah_db,
                'harga_satuan' => $harga,
                'subtotal'     => $subtotal,
            ]);

            // Update stok bahan hanya jika tunai
            if ($bahan && $jenisPembelian === 'tunai') {
                $satuan = strtolower($bahan['satuan']);
                $stokLama = $bahan['stok'];

                // Konversi stok ke satuan tampil
                $stokLamaTampil = $stokLama;
                if ($satuan === 'kg' || $satuan === 'liter') {
                    $stokLamaTampil = $stokLama / 1000;
                }

                // Konversi jumlah masuk (tambahan) ke satuan tampilan juga
                $jumlahMasukTampil = $jumlah_db;
                if ($satuan === 'kg' || $satuan === 'liter') {
                    $jumlahMasukTampil = $jumlah_db / 1000;
                }

                // Tambah stok
                $stokBaru = $stokLama + $jumlah_db;

                // Hitung saldo berdasarkan satuan tampil × harga
                $saldoBaru = ($stokLamaTampil + $jumlahMasukTampil) * $bahan['harga_satuan'];

                // Simpan update stok dan saldo
                $bahanModel->update($bahanId, [
                    'stok' => $stokBaru,
                    'saldo' => $saldoBaru
                ]);

                $transaksiModel->save([
                    'id_bahan' => $bahanId,
                    'tanggal' => $tanggal,
                    'jenis' => 'masuk',
                    'jumlah' => $jumlah_db,
                    'satuan' => $bahan['satuan'],
                    'keterangan' => 'Pembelian No. Nota: ' . $noNota,
                ]);

                $kartuModel->save([
                    'bahan_id' => $bahanId,
                    'tanggal' => $tanggal,
                    'jenis' => 'masuk',
                    'jumlah' => $jumlah_db,
                    'keterangan' => 'Pembelian No. Nota: ' . $noNota,
                ]);
            }

            $total += $subtotal;
        }

        // Update total di header pembelian
        $pembelianModel->update($pembelianId, [
            'total' => $total
        ]);

        // Update saldo akun sesuai transaksi
        if ($jenisPembelian === 'tunai') {
            // Kas (101) berkurang (kredit)
            $akunModel->updateSaldo(101, $total, 'kredit');
            // Persediaan bahan penolong (108) bertambah (debit)
            $akunModel->updateSaldo(108, $total, 'debit');
        } elseif ($jenisPembelian === 'kredit') {
            // Persediaan bahan baku (107) bertambah (debit)
            $akunModel->updateSaldo(107, $total, 'debit');
            // Utang usaha (201) bertambah (kredit)
            $akunModel->updateSaldo(201, $total, 'kredit');

            // Catat jurnal umum untuk pembelian kredit
            $db = \Config\Database::connect();
            $akunPersediaan = $akunModel->where('kode_akun', 107)->first(); // Persediaan bahan baku
            $akunUtang = $akunModel->where('kode_akun', 201)->first(); // Utang usaha
            if ($akunPersediaan && $akunUtang) {
                $jurnal = $db->table('jurnal_umum');
                $grandTotal = $total;
                $keterangan = 'Pembelian Kredit';

                // Debit ke Persediaan bahan baku dan Kredit ke Utang usaha, satu baris saja
                $jurnal->insert([
                    'tanggal' => $tanggal,
                    'akun_id' => $akunPersediaan['id'],
                    'debit' => $grandTotal,
                    'kredit' => 0,
                    'keterangan' => $keterangan,
                    'supplier_id' => $pemasokId,
                ]);
                $jurnal->insert([
                    'tanggal' => $tanggal,
                    'akun_id' => $akunUtang['id'],
                    'debit' => 0,
                    'kredit' => $grandTotal,
                    'keterangan' => $keterangan,
                    'supplier_id' => $pemasokId,
                ]);
            }
        }

        return redirect()->to('produksi/pembelian')->with('success', 'Pembelian berhasil disimpan.');
    }

    public function detailPembelian($id)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $pembelianModel = new PembelianModel();
        $detailModel    = new DetailPembelianModel();
        $pemasokModel   = new PemasokModel();

        $pembelian = $pembelianModel->find($id);
        $pemasok   = $pemasokModel->find($pembelian['pemasok_id']);
        $detail    = $detailModel->where('pembelian_id', $id)->findAll();

        return view('produksi/pembelian/detail', [
            'tittle'     => 'Detail Pembelian',
            'pembelian' => $pembelian,
            'pemasok'   => $pemasok,
            'detail'    => $detail
        ]);
    }

    public function createPembelian()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        $pembelianmodel = new PembelianModel();
        $pemasokModel   = new PemasokModel();
        $bahanModel = new BahanModel();

        $data = [
            'tittle' => 'Form Tambah Pembelian',
            'pembelian' => $pembelianmodel->findAll(),
            'pemasok'   => $pemasokModel->findAll(),
            'bahan' => $bahanModel->findAll(),

        ];
        return view('produksi/pembelian/tambah', $data);
    }

    public function hapusPembelian($id)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $pembelianModel = new PembelianModel();
        $pembelianModel->delete($id);

        return redirect()->to(base_url('produksi/pembelian'))->with('success', 'Data berhasil dihapus.');
    }

    // Form edit pembelian (edit nota dan jumlah bahan)
    public function editPembelian($id)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $pembelianModel = new PembelianModel();
        $detailModel = new DetailPembelianModel();
        $bahanModel = new BahanModel();

        $pembelian = $pembelianModel->find($id);
        if (!$pembelian) {
            return redirect()->to('produksi/pembelian')->with('error', 'Data pembelian tidak ditemukan.');
        }
        $detail = $detailModel->where('pembelian_id', $id)->findAll();
        // Ambil nama dan satuan bahan
        foreach ($detail as &$item) {
            $bahan = $bahanModel->find($item['bahan_id']);
            $item['nama'] = $bahan['nama'] ?? '-';
            $item['satuan'] = $bahan['satuan'] ?? '-';
        }
        return view('produksi/pembelian/edit', [
            'tittle' => 'Edit Pembelian',
            'pembelian' => $pembelian,
            'detail' => $detail
        ]);
    }

    // Proses update pembelian (nota dan jumlah bahan)
    public function updatePembelian($id)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $pembelianModel = new PembelianModel();
        $detailModel = new DetailPembelianModel();
        $bahanModel = new BahanModel();

        $pembelian = $pembelianModel->find($id);
        if (!$pembelian) {
            return redirect()->to('produksi/pembelian')->with('error', 'Data pembelian tidak ditemukan.');
        }

        // Update nota
        $no_nota = $this->request->getPost('no_nota');
        $pembelianModel->update($id, ['no_nota' => $no_nota]);

        // Update jumlah bahan
        $jumlahArr = $this->request->getPost('jumlah'); // [detail_id => jumlah]
        $total = 0;
        foreach ($jumlahArr as $detailId => $jumlah) {
            $detail = $detailModel->find($detailId);
            if ($detail) {
                $bahan = $bahanModel->find($detail['bahan_id']);
                $harga = $detail['harga_satuan'];
                $satuan = strtolower($bahan['satuan']);
                // Konversi jumlah sesuai satuan
                if ($satuan === 'kg') {
                    $jumlah_db = $jumlah * 1000;
                } elseif ($satuan === 'liter') {
                    $jumlah_db = $jumlah * 1000;
                } else {
                    $jumlah_db = $jumlah;
                }
                $subtotal = (float)$jumlah * (float)$harga;
                $detailModel->update($detailId, [
                    'jumlah' => $jumlah_db,
                    'subtotal' => $subtotal
                ]);
                $total += $subtotal;
            }
        }
        // Update total pembelian
        $pembelianModel->update($id, ['total' => $total]);

        return redirect()->to('produksi/pembelian')->with('success', 'Pembelian berhasil diperbarui.');
    }

    // Update status pembelian dan jika sudah diterima, tambah stok bahan
    public function updateStatusPembelian($id, $status)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $pembelianModel = new PembelianModel();
        $detailModel = new DetailPembelianModel();
        $bahanModel = new BahanModel();
        $kartuModel = new KartuPersediaanModel();
        $transaksiModel = new \App\Models\TransaksiBahanModel();

        $pembelian = $pembelianModel->find($id);
        if (!$pembelian) {
            return redirect()->back()->with('error', 'Data pembelian tidak ditemukan.');
        }

        // Jika status diubah menjadi sudah_diterima dan sebelumnya belum_diterima
        if ($status === 'sudah_diterima' && $pembelian['status_barang'] === 'belum_diterima') {
            $details = $detailModel->where('pembelian_id', $id)->findAll();
            foreach ($details as $d) {
                $bahan = $bahanModel->find($d['bahan_id']);
                if ($bahan) {
                    $satuan = strtolower($bahan['satuan']);
                    $jumlahMasuk = $d['jumlah'];

                    // Konversi stok tampil
                    $stokLama = $bahan['stok'];
                    $stokTampilLama = ($satuan === 'kg' || $satuan === 'liter') ? $stokLama / 1000 : $stokLama;
                    $jumlahMasukTampil = ($satuan === 'kg' || $satuan === 'liter') ? $jumlahMasuk / 1000 : $jumlahMasuk;

                    // Hitung stok & saldo baru
                    $stokBaru = $stokLama + $jumlahMasuk;
                    $saldoBaru = ($stokTampilLama + $jumlahMasukTampil) * $bahan['harga_satuan'];

                    // ✅ Update stok & saldo bahan
                    $bahanModel->update($bahan['id'], [
                        'stok' => $stokBaru,
                        'saldo' => $saldoBaru
                    ]);

                    // ✅ Simpan ke kartu persediaan
                    $kartuModel->save([
                        'bahan_id'   => $bahan['id'],
                        'tanggal'    => $pembelian['tanggal'],
                        'jenis'      => 'masuk',
                        'jumlah'     => $d['jumlah'],
                        'keterangan' => 'Penerimaan Pembelian Kredit - No Nota: ' . $pembelian['no_nota']
                    ]);

                    // ✅ Simpan ke transaksi bahan
                    $transaksiModel->save([
                        'id_bahan'   => $bahan['id'],
                        'tanggal'    => $pembelian['tanggal'],
                        'jenis'      => 'masuk',
                        'jumlah'     => $d['jumlah'],
                        'satuan'     => $bahan['satuan'],
                        'keterangan' => 'Penerimaan Pembelian Kredit - No Nota: ' . $pembelian['no_nota']
                    ]);
                }
            }
        }

        // Update status_barang
        $pembelianModel->update($id, ['status_barang' => $status]);
        return redirect()->to(base_url('produksi/pembelian'))->with('success', 'Status pembelian berhasil diubah.');
    }

    //PERSEDIAAN
    //BAHAN MENTAH
    public function bahan()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        $bahanModel = new BahanModel();
        $data = [
            'tittle' => 'Manajemen Bahan',
            'bahan' => $bahanModel->findAll()
        ];

        return view('produksi/persediaan/bahan', $data);
    }

    public function create()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $bahanmodel = new BahanModel();

        $data = [
            'tittle' => 'Tambah Bahan',
            'bahan' => $bahanmodel->findAll(),

        ];
        return view('produksi/persediaan/tambah', $data);
    }

    public function simpanBahan()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        $bahanModel = new BahanModel();
        $kode = $this->request->getPost('kode');

        // Cek apakah kode sudah ada
        if ($bahanModel->where('kode', $kode)->first()) {
            return redirect()->back()->withInput()->with('error', 'Kode sudah digunakan.');
        }

        $nama = $this->request->getPost('nama');
        $kategori = $this->request->getPost('kategori');
        $jenis = $this->request->getPost('jenis');
        $satuan = strtolower($this->request->getPost('satuan'));
        $stok_input = (float) $this->request->getPost('stok'); // nilai dari form
        $harga = (float) $this->request->getPost('harga_satuan');

        // Konversi stok sesuai satuan untuk disimpan
        $stok_simpan = $stok_input;
        if ($satuan === 'kg' || $satuan === 'liter') {
            $stok_simpan = $stok_input * 1000;
        }

        // Hitung saldo berdasarkan input user (bukan stok yang dikonversi)
        $saldo = $stok_input * $harga;

        // Simpan data
        $bahanModel->save([
            'kode' => $kode,
            'nama' => $nama,
            'kategori' => $kategori,
            'jenis' => $jenis,
            'stok' => $stok_simpan,
            'satuan' => $satuan,
            'harga_satuan' => $harga,
            'saldo' => $saldo
        ]);

        return redirect()->to(base_url('produksi/persediaan'))->with('success', 'Data berhasil ditambahkan.');
    }

    public function editBahan($id)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $bahanmodel = new BahanModel();
        $bahan = $bahanmodel->find($id);
        // Konversi stok ke satuan tampil
        $stok = $bahan['stok'];
        $satuan = strtolower($bahan['satuan']);
        if ($satuan === 'kg') {
            $stok = $stok / 1000;
        } elseif ($satuan === 'liter') {
            $stok = $stok / 1000;
        } // pcs dan meter tetap
        $bahan['stok'] = $stok;
        $data = [
            'tittle' => 'Edit Bahan',
            'bahan' => $bahan,
        ];
        return view('produksi/persediaan/edit', $data);
    }

    public function updateBahan($id)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        $bahanModel = new BahanModel();
        $kode = $this->request->getPost('kode');

        // Cek apakah kode sudah digunakan oleh bahan lain
        if ($bahanModel->where('kode', $kode)->where('id !=', $id)->first()) {
            return redirect()->back()->withInput()->with('error', 'Kode sudah digunakan oleh bahan lain.');
        }

        $satuan = strtolower($this->request->getPost('satuan'));
        $stok_input = (float) $this->request->getPost('stok');
        $harga = (float) $this->request->getPost('harga_satuan');

        // Simpan stok ke database (konversi)
        $stok_simpan = $stok_input;
        if ($satuan === 'kg' || $satuan === 'liter') {
            $stok_simpan = $stok_input * 1000; // Simpan dalam gram/ml
        }

        // Hitung saldo berdasarkan tampilan user (bukan yang dikonversi)
        $saldo = $stok_input * $harga;

        $bahanModel->update($id, [
            'kode' => $kode,
            'nama' => $this->request->getPost('nama'),
            'kategori' => $this->request->getPost('kategori'),
            'jenis' => $this->request->getPost('jenis'),
            'stok' => $stok_simpan,
            'satuan' => $satuan,
            'harga_satuan' => $harga,
            'saldo' => $saldo
        ]);

        return redirect()->to(base_url('produksi/persediaan'))->with('success', 'Data berhasil diperbarui.');
    }

    public function hapusBahan($id)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $bahanModel = new BahanModel();
        $bahanModel->delete($id);

        return redirect()->to(base_url('produksi/persediaan'))->with('success', 'Data berhasil dihapus.');
    }

    //BSJ
    public function bsj()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        $bsjModel = new BSJModel();
        $data = [
            'tittle' => 'Manajemen Barang Setengah Jadi',
            'bsj' => $bsjModel->findAll()
        ];

        return view('produksi/persediaan/bsj', $data);
    }

    public function tambahBSJ()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $bsjModel = new BSJModel();

        $data = [
            'tittle' => 'Tambah Barang Setengah Jadi',
            'bsj' => $bsjModel->findAll(),

        ];
        return view('produksi/persediaan/tambah_bsj', $data);
    }

    public function simpanBSJ()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $bsjModel = new BSJModel();
        $kode = $this->request->getPost('kode');

        // Cek apakah kode sudah ada
        if ($bsjModel->where('kode', $kode)->first()) {
            return redirect()->back()->withInput()->with('error', 'Kode sudah digunakan.');
        }

        $stok = $this->request->getPost('stok');
        $satuan = strtolower(trim($this->request->getPost('satuan')));

        // Jika satuan kg/kilogram, ubah ke gram
        if (in_array($satuan, ['kg', 'kilogram'])) {
            $stok = $stok * 1000;
        }

        $bsjModel->save([
            'kode' => $kode,
            'nama' => $this->request->getPost('nama'),
            'stok' => (int) $stok, // pastikan disimpan tanpa desimal
            'satuan' => $this->request->getPost('satuan'),
        ]);

        return redirect()->to(base_url('produksi/persediaan/bsj'))->with('success', 'Data berhasil ditambahkan.');
    }

    public function editBSJ($id)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $bsjModel = new BSJModel();

        $data = [
            'tittle' => 'Edit Barang Setengah Jadi',
            'bsj' => $bsjModel->find($id),

        ];
        return view('produksi/persediaan/edit_bsj', $data);
    }

    public function updateBSJ($id)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $bsjModel = new BSJModel();
        $kode       = $this->request->getPost('kode');
        $stok = $this->request->getPost('stok');
        $satuan = strtolower($this->request->getPost('satuan'));

        if (in_array($satuan, ['kg', 'kilogram'])) {
            $stok = $stok * 1000;
        }
        // Cek apakah kode sudah digunakan oleh bahan lain
        if ($bsjModel->where('kode', $kode)->where('id !=', $id)->first()) {
            return redirect()->back()->withInput()->with('error', 'Kode sudah digunakan oleh bahan lain.');
        }

        $bsjModel->update($id, [
            'kode' => $this->request->getPost('kode'),
            'nama' => $this->request->getPost('nama'),
            'stok' => $stok,
            'satuan' => $this->request->getPost('satuan'),
        ]);

        return redirect()->to(base_url('produksi/persediaan/bsj'))->with('success', 'Data berhasil diperbarui.');
    }

    public function hapusBSJ($id)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $bsjModel = new \App\Models\BSJModel();
        $komposisiModel = new \App\Models\KomposisiBahanBSJModel();

        // Cek apakah BSJ masih punya komposisi
        $jumlahKomposisi = $komposisiModel->where('id_bsj', $id)->countAllResults();

        if ($jumlahKomposisi > 0) {
            return redirect()->to(base_url('produksi/persediaan/bsj'))
                ->with('error', 'Tidak bisa menghapus BSJ karena masih memiliki komposisi bahan.');
        }

        // Jika tidak ada relasi, aman dihapus
        $bsjModel->delete($id);

        return redirect()->to(base_url('produksi/persediaan/bsj'))->with('success', 'Data BSJ berhasil dihapus.');
    }

    //PRODUKSI
    public function inputProduksi()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $tkModel = new BiayaTenagaKerjaModel();
        $bopModel = new BiayaOverheadModel();
        $bsjModel = new BSJModel();
        $bahanModel = new BahanModel();
        $komposisiModel = new KomposisiBahanBSJModel();

        $tenaga_kerja = $tkModel->findAll();
        $overhead = $bopModel->findAll();

        // Hitung total biaya tenaga kerja dan bop dibagi 3 (karena ada 3 jenis BSJ)
        $total_tk = array_sum(array_column($tenaga_kerja, 'biaya'));
        $total_bop = array_sum(array_column($overhead, 'biaya'));

        $data = [
            'tittle'              => 'Input Produksi',
            'bsj'                 => $bsjModel->findAll(),
            'bahan_all'           => $bahanModel->findAll(),
            'komposisi'           => $komposisiModel->findAll(),
            'tenaga_kerja'        => $tenaga_kerja,
            'overhead'            => $overhead,
            'total_tenaga_kerja'  => $total_tk,
            'total_bop'           => $total_bop / 3
        ];

        return view('produksi/produksi/input', $data);
    }

    public function simpanProduksi()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $produksiModel     = new \App\Models\ProduksiModel();
        $detailModel       = new \App\Models\DetailProduksiModel();
        $bahanModel        = new \App\Models\BahanModel();
        $tkModel           = new \App\Models\BiayaTenagaKerjaModel();
        $bopModel          = new \App\Models\BiayaOverheadModel();

        $tanggal  = date('Y-m-d');
        $bsjId    = $this->request->getPost('bsj_id');
        $jumlah   = $this->request->getPost('jumlah');
        $noProd   = 'PRD' . date('YmdHis');

        if (!$bsjId || !$jumlah || !is_numeric($jumlah)) {
            return redirect()->back()->with('error', 'Data tidak valid. Pastikan semua isian lengkap.');
        }

        $totalBiaya = 0;

        // Simpan produksi utama, status awal draft
        $produksiId = $produksiModel->insert([
            'tanggal'      => $tanggal,
            'bsj_id'       => $bsjId,
            'jumlah'       => $jumlah,
            'no_produksi'  => $noProd,
            'total_biaya'  => 0,
            'status'       => 'draft',
        ]);

        // === BAHAN BAKU ===
        $bahanBaku = $this->request->getPost('bahan_baku');
        $totalBiayaBahan = 0;
        foreach ($bahanBaku as $bahanId => $qty) {
            if ($qty > 0) {
                $bahan = $bahanModel->find($bahanId);
                if ($bahan['stok'] < $qty) {
                    return redirect()->back()->with('error', 'Stok bahan tidak mencukupi untuk: ' . $bahan['nama']);
                }
                $jumlah_kg = $qty / 1000; // konversi ke kg
                $subtotal = $jumlah_kg * $bahan['harga_satuan'];
                $detailModel->save([
                    'produksi_id'   => $produksiId,
                    'bahan_id'      => $bahanId,
                    'kategori'      => 'baku',
                    'jumlah'        => $qty,
                    'harga_satuan'  => $bahan['harga_satuan'],
                    'subtotal'      => $subtotal,
                ]);
                $totalBiayaBahan += $subtotal;
            }
        }

        // === TENAGA KERJA (total biaya tetap, tidak dikali jumlah produksi) ===
        $tenaga_kerja = $tkModel->findAll();
        $totalTenagaKerja = 0;
        foreach ($tenaga_kerja as $tk) {
            $subtotal = $tk['biaya'];
            $detailModel->save([
                'produksi_id'   => $produksiId,
                'kategori'      => 'tenaga_kerja',
                'nama_biaya'    => $tk['nama'],
                'jumlah'        => 1,
                'harga_satuan'  => $tk['biaya'],
                'subtotal'      => $subtotal,
            ]);
            $totalTenagaKerja += $subtotal;
        }

        // === BOP (dibagi 3, tidak dikali jumlah produksi) ===
        $overhead = $bopModel->findAll();
        $totalBOP = 0;
        foreach ($overhead as $bop) {
            $subtotal = $bop['biaya'] / 3;
            $detailModel->save([
                'produksi_id'   => $produksiId,
                'kategori'      => 'overhead',
                'nama_biaya'    => $bop['nama'],
                'jumlah'        => 1,
                'harga_satuan'  => $subtotal,
                'subtotal'      => $subtotal,
            ]);
            $totalBOP += $subtotal;
        }

        // === Update total biaya ===
        $totalBiaya = $totalBiayaBahan + $totalTenagaKerja + $totalBOP;
        $produksiModel->update($produksiId, ['total_biaya' => $totalBiaya]);

        return redirect()->to(base_url('produksi/produksi/daftar'))->with('success', 'Produksi berhasil disimpan. Silakan proses produksi untuk mengurangi bahan dan menambah stok BSJ.');
    }

    public function daftarProduksi()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $produksiModel = new \App\Models\ProduksiModel();
        $bsjModel      = new \App\Models\BSJModel();

        $dataProduksi = $produksiModel->orderBy('tanggal', 'DESC')->findAll();

        foreach ($dataProduksi as &$p) {
            $bsj = $bsjModel->find($p['bsj_id']);
            $p['bsj_nama'] = $bsj['nama'] ?? '-';
            $p['bsj_kode'] = $bsj['kode'] ?? '-';
            $p['bsj_satuan'] = $bsj['satuan'] ?? '-';
        }

        $data = [
            'tittle' => 'Daftar Produksi',
            'produksi' => $dataProduksi
        ];

        return view('produksi/produksi/index', $data);
    }

    public function detailProduksi($id)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $produksiModel = new \App\Models\ProduksiModel();
        $bsjModel = new \App\Models\BSJModel();
        $detailModel = new \App\Models\DetailProduksiModel();
        $bahanModel = new \App\Models\BahanModel();

        $produksi = $produksiModel
            ->select('produksi.*, bsj.nama as nama_bsj, bsj.satuan')
            ->join('bsj', 'bsj.id = produksi.bsj_id')
            ->where('produksi.id', $id)
            ->first();
        $bsj = $bsjModel->find($produksi['bsj_id']);
        $detail = $detailModel
            ->select('detail_produksi.*, bahan.nama as nama_bahan')
            ->join('bahan', 'bahan.id = detail_produksi.bahan_id', 'left')
            ->where('produksi_id', $id)
            ->findAll();

        $rincian = [
            'baku' => [],
            'penolong' => [],
            'tenaga_kerja' => [],
            'overhead' => [],
        ];

        foreach ($detail as $d) {
            if (in_array($d['kategori'], ['baku', 'penolong'])) {
                $bahan = $bahanModel->find($d['bahan_id']);
                $d['nama_bahan'] = $bahan['nama'] ?? '-';
                $d['satuan'] = $bahan['satuan'] ?? '-';
            }
            $rincian[$d['kategori']][] = $d;
        }

        return view('produksi/produksi/detail', [
            'tittle' => 'Detail Produksi',
            'produksi' => $produksi,
            'detail' => $rincian['baku'] + $rincian['penolong'] + $rincian['tenaga_kerja'] + $rincian['overhead']
        ]);
    }

    public function hapusProduksi($id)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $produksiModel = new ProduksiModel();
        $produksiModel->delete($id);

        return redirect()->to(base_url('produksi/produksi/daftar'))->with('success', 'Data berhasil dihapus.');
    }

    public function updateStatusProduksi($id, $status)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $produksiModel = new \App\Models\ProduksiModel();
        $bsjModel = new \App\Models\BSJModel();
        $detailModel = new \App\Models\DetailProduksiModel();
        $bahanModel = new \App\Models\BahanModel();
        $kartuModel = new \App\Models\KartuPersediaanModel();
        $kartubsjModel = new KartuPersediaanBSJModel();

        $produksi = $produksiModel->find($id);
        if (!$produksi) {
            return redirect()->back()->with('error', 'Data produksi tidak ditemukan.');
        }


        // Jika status draft -> proses: kurangi stok bahan
        if ($status == 'proses' && $produksi['status'] == 'draft') {
            $details = $detailModel->where('produksi_id', $id)->where('kategori', 'baku')->findAll();
            $transaksiBahanModel = new \App\Models\TransaksiBahanModel();
            foreach ($details as $d) {
                $bahan = $bahanModel->find($d['bahan_id']);
                if ($bahan && $bahan['stok'] >= $d['jumlah']) {
                    $jumlah = $d['jumlah'];
                    $satuan = strtolower($bahan['satuan']);
                    $hargaSatuan = $bahan['harga_satuan'];

                    // Konversi untuk perhitungan saldo (karena stok disimpan dalam gram/ml)
                    $jumlahTampil = ($satuan === 'kg' || $satuan === 'liter') ? $jumlah / 1000 : $jumlah;
                    $stokBaru = $bahan['stok'] - $jumlah;
                    $stokBaruTampil = ($satuan === 'kg' || $satuan === 'liter') ? $stokBaru / 1000 : $stokBaru;

                    $saldoBaru = $stokBaruTampil * $hargaSatuan;

                    // ✅ Update stok & saldo bahan
                    $bahanModel->update($d['bahan_id'], [
                        'stok' => $stokBaru,
                        'saldo' => $saldoBaru
                    ]);
                    $kartuModel->save([
                        'bahan_id'   => $bahan['id'],
                        'tanggal'    => $produksi['tanggal'],
                        'jenis'      => 'keluar',
                        'jumlah'     => $d['jumlah'],
                        'keterangan' => 'Produksi BSJ ID: ' . $produksi['id']
                    ]);
                    // Catat ke transaksi_bahan sebagai barang keluar
                    $transaksiBahanModel->insert([
                        'id_bahan' => $bahan['id'],
                        'tanggal' => $produksi['tanggal'],
                        'jenis' => 'keluar',
                        'jumlah' => $d['jumlah'],
                        'satuan' => $bahan['satuan'],
                        'keterangan' => 'proses produksi'
                    ]);
                } else {
                    return redirect()->back()->with('error', 'Stok bahan tidak cukup untuk: ' . ($bahan['nama'] ?? ''));
                }
            }
        }

        // Jika status proses -> selesai: tambah stok BSJ
        if ($status == 'selesai' && $produksi['status'] == 'proses') {
            $bsj = $bsjModel->find($produksi['bsj_id']);
            if ($bsj) {
                $bsjModel->update($bsj['id'], [
                    'stok' => $bsj['stok'] + $produksi['jumlah']
                ]);
                $kartubsjModel = new \App\Models\KartuPersediaanBSJModel();
                $kartubsjModel->save([
                    'bsj_id'     => $bsj['id'],
                    'tanggal'    => date('Y-m-d'),
                    'jenis'      => 'masuk',
                    'jumlah'     => $produksi['jumlah'],
                    'keterangan' => 'Hasil Produksi No: ' . $produksi['id']
                ]);
            }
        }

        // Jika status draft/proses -> dibatalkan: kembalikan stok bahan jika sudah dikurangi
        if ($status == 'dibatalkan' && in_array($produksi['status'], ['draft', 'proses'])) {
            if ($produksi['status'] == 'proses') {
                $details = $detailModel->where('produksi_id', $id)->where('kategori', 'baku')->findAll();
                foreach ($details as $d) {
                    $bahan = $bahanModel->find($d['bahan_id']);
                    if ($bahan) {
                        $bahanModel->update($d['bahan_id'], [
                            'stok' => $bahan['stok'] + $d['jumlah']
                        ]);
                    }
                }
            }
        }

        $produksiModel->update($id, ['status' => $status]);
        return redirect()->to(base_url('produksi/produksi/daftar'))->with('success', 'Status produksi berhasil diubah.');
    }

    public function formHPP()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $produksiModel = new \App\Models\ProduksiModel();
        $produksi = $produksiModel->where('status', 'selesai')->findAll();
        return view('produksi/hpp/form', [
            'tittle' => 'Form Input HPP',
            'produksi' => $produksi
        ]);
    }

    public function simpanHPP()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $hppModel = new \App\Models\HPPModel();
        $produksiModel = new \App\Models\ProduksiModel();
        $produksi_id = $this->request->getPost('produksi_id');
        $keterangan = $this->request->getPost('keterangan');

        $produksi = $produksiModel->find($produksi_id);
        if (!$produksi) {
            return redirect()->back()->with('error', 'Data produksi tidak ditemukan.');
        }
        $total_biaya = $produksi['total_biaya'];
        $jumlah_produksi = $produksi['jumlah'];
        $hpp_per_unit = $jumlah_produksi > 0 ? $total_biaya / $jumlah_produksi : 0;

        $hppModel->save([
            'produksi_id' => $produksi_id,
            'kode_produksi' => $produksi['no_produksi'],
            'total_biaya' => $total_biaya,
            'jumlah_produksi' => $jumlah_produksi,
            'hpp_per_unit' => $hpp_per_unit,
            'keterangan' => $keterangan
        ]);
        return redirect()->to(base_url('produksi/hpp/form'))->with('success', 'Data HPP berhasil disimpan.');
    }

    public function indexHPP()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $hppModel = new \App\Models\HPPModel();
        $produksiModel = new \App\Models\ProduksiModel();
        $bsjModel = new \App\Models\BSJModel();
        $hpp_list = $hppModel->findAll();
        // Gabungkan info produksi dan BSJ
        foreach ($hpp_list as &$hpp) {
            $produksi = $produksiModel->find($hpp['produksi_id']);
            $hpp['tanggal'] = $produksi['tanggal'] ?? '-';
            $bsj = $bsjModel->find($produksi['bsj_id'] ?? null);
            $hpp['nama_bsj'] = $bsj['nama'] ?? '-';
        }
        return view('produksi/hpp/index', [
            'tittle' => 'Perhitungan HPP per BSJ',
            'hpp_list' => $hpp_list
        ]);
    }

    //PENGIRIMAN BARANG
    public function pengirimanIndex()
    {
        $pengirimanModel = new \App\Models\PengirimanModel();
        $outletModel = new \App\Models\OutletModel();
        $pengirimanDetailModel = new \App\Models\PengirimanDetailModel();
        $list = $pengirimanModel->orderBy('tanggal', 'DESC')->findAll();
        $pengiriman = [];
        foreach ($list as $row) {
            $outlet = null;
            if (isset($row['outlet_id']) && $row['outlet_id']) {
                $outlet = $outletModel->find($row['outlet_id']);
            }
            $detail = $pengirimanDetailModel->where('pengiriman_id', $row['id'])->findAll();
            $barang = [];
            foreach ($detail as $d) {
                $barang[] = [
                    'nama' => $d['nama_barang'] ?? '-',
                    'jumlah' => strtolower($d['satuan']) === 'gram' ? $d['jumlah'] / 1000 : $d['jumlah'],
                    'satuan' => strtolower($d['satuan']) === 'gram' ? 'kg' : $d['satuan']
                ];
            }
            $pengiriman[] = [
                'id' => $row['id'],
                'tanggal' => $row['tanggal'],
                'outlet_id' => $row['outlet_id'],
                'outlet_nama' => $outlet['nama_outlet'] ?? '-',
                'barang' => $barang
            ];
        }

        return view('produksi/pengiriman/index', [
            'tittle' => 'Daftar Pengiriman Barang',
            'pengiriman' => $pengiriman
        ]);
    }

    public function pengirimanInput()
    {
        $outletModel = new \App\Models\OutletModel();
        $bsjModel = new \App\Models\BSJModel();
        $bahanModel = new \App\Models\BahanModel();
        $data = [
            'tittle' => 'Form Pengiriman Barang',
            'outlets' => $outletModel->findAll(),
            'barang_bsj' => $bsjModel->findAll(),
            'bahan' => $bahanModel->findAll(),
        ];
        return view('produksi/pengiriman/form_pengiriman', $data);
    }

    public function pengirimanSimpan()
    {
        $notifikasiModel = new \App\Models\NotifikasiModel();
        $bsjModel = new \App\Models\BSJModel();
        $bahanModel = new \App\Models\BahanModel();

        $tanggal = $this->request->getPost('tanggal');
        $outletId = $this->request->getPost('outlet_id');
        $barangData = $this->request->getPost('barang');
        $catatan = $this->request->getPost('catatan');
        $kartubsjModel = new \App\Models\KartuPersediaanBSJModel();
        $kartuModel = new \App\Models\KartuPersediaanModel();

        if (!$tanggal || !$outletId || !$barangData) {
            return redirect()->back()->with('error', 'Semua field harus diisi.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Insert ke tabel pengiriman
            $pengirimanModel = new \App\Models\PengirimanModel();
            $pengirimanData = [
                'tanggal' => $tanggal,
                'user_id' => user_id(),
                'outlet_id' => $outletId,
                'catatan' => $catatan,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $pengirimanModel->insert($pengirimanData);
            $pengirimanId = $pengirimanModel->getInsertID();

            // 2. Insert detail pengiriman dan update stok BSJ/bahan
            $pengirimanDetailModel = new \App\Models\PengirimanDetailModel();
            foreach ($barangData as $barang) {
                $tipe = $barang['tipe'] ?? null;
                $barangId = $barang['barang_id'] ?? null;
                $jumlah = $barang['jumlah'] ?? 0;
                $satuan = $barang['satuan'] ?? '';
                $namaBarang = '-';
                if ($tipe === 'bsj') {
                    $bsj = $bsjModel->find($barangId);
                    if (!$bsj || $bsj['stok'] < $jumlah) {
                        $db->transRollback();
                        return redirect()->back()->with('error', 'Stok BSJ tidak cukup.');
                    }
                    $namaBarang = $bsj['nama'];
                    $bsjModel->update($barangId, [
                        'stok' => $bsj['stok'] - $jumlah
                    ]);
                    // Catat ke kartu persediaan BSJ
                    $kartubsjModel->save([
                        'bsj_id'     => $bsj['id'],
                        'tanggal'    => date('Y-m-d'),
                        'jenis'      => 'keluar',
                        'jumlah'     => $jumlah,
                        'keterangan' => 'Pengiriman No: ' . $pengirimanId
                    ]);
                } elseif ($tipe === 'bahan') {
                    $bahan = $bahanModel->find($barangId);
                    if (!$bahan || $bahan['stok'] < $jumlah) {
                        $db->transRollback();
                        return redirect()->back()->with('error', 'Stok bahan tidak cukup.');
                    }
                    $namaBarang = $bahan['nama'];
                    $satuan = strtolower($bahan['satuan']);
                    $hargaSatuan = $bahan['harga_satuan'];

                    // Hitung stok baru
                    $stokBaru = $bahan['stok'] - $jumlah;

                    // Hitung saldo baru dengan konversi tampilan
                    $stokBaruTampil = ($satuan === 'kg' || $satuan === 'liter') ? $stokBaru / 1000 : $stokBaru;
                    $saldoBaru = $stokBaruTampil * $hargaSatuan;

                    // ✅ Update stok dan saldo bahan
                    $bahanModel->update($barangId, [
                        'stok' => $stokBaru,
                        'saldo' => $saldoBaru
                    ]);
                    // Catat ke kartu persediaan bahan
                    $kartuModel->save([
                        'bahan_id'   => $bahan['id'],
                        'tanggal'    => date('Y-m-d'),
                        'jenis'      => 'keluar',
                        'jumlah'     => $jumlah,
                        'keterangan' => 'Pengiriman No: ' . $pengirimanId
                    ]);
                } else {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Tipe barang tidak valid.');
                }
                $pengirimanDetailModel->insert([
                    'pengiriman_id' => $pengirimanId,
                    'barang_id' => $barangId,
                    'tipe_barang' => $tipe,
                    'nama_barang' => $namaBarang,
                    'jumlah' => $jumlah,
                    'satuan' => $satuan
                ]);
            }

            // 3. Kirim notifikasi ke penjualan outlet tujuan pakai helper
            helper('notifikasi_helper');
            $isi = 'Ada pengiriman barang baru untuk outlet Anda';
            $pengirimRole = in_groups('produksi') ? 'produksi' : 'unknown';
            kirimNotifikasi('penjualan', 'pengiriman', $isi, $pengirimanId, $outletId, $pengirimRole);

            $db->transCommit();
            return redirect()->to('/produksi/pengiriman')->with('success', 'Form pengiriman berhasil disimpan dan notifikasi telah dikirim.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function pengirimanDetail($id)
    {
        $pengirimanModel = new \App\Models\PengirimanModel();
        $pengirimanDetailModel = new \App\Models\PengirimanDetailModel();
        $outletModel = new \App\Models\OutletModel();
        $bsjModel = new \App\Models\BSJModel();

        $pengiriman = $pengirimanModel->find($id);
        if (!$pengiriman) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data pengiriman tidak ditemukan.');
        }
        $outlet = (isset($pengiriman['outlet_id']) && $pengiriman['outlet_id']) ? $outletModel->find($pengiriman['outlet_id']) : null;
        $detail = $pengirimanDetailModel->where('pengiriman_id', $id)->findAll();
        $barang = [];
        foreach ($detail as $d) {
            $jumlah = $d['jumlah'] ?? '-';
            $satuan = $d['satuan'] ?? '-';
            $barang[] = [
                'nama'   => $d['nama_barang'] ?? '-',
                'jumlah' => strtolower($satuan) === 'gram' ? $jumlah / 1000 : $jumlah,
                'satuan' => strtolower($satuan) === 'gram' ? 'kg' : $satuan
            ];
        }
        return view('produksi/pengiriman/detail', [
            'tittle' => 'Detail Pengiriman Barang',
            'pengiriman' => $pengiriman,
            'outlet' => $outlet,
            'barang' => $barang
        ]);
    }

    public function hapusPengiriman($id)
    {
        $pengirimanModel = new \App\Models\PengirimanModel();
        $pengirimanDetailModel = new \App\Models\PengirimanDetailModel();
        $bsjModel = new \App\Models\BSJModel();
        $bahanModel = new \App\Models\BahanModel();

        $db = \Config\Database::connect();
        $db->transStart();
        try {
            // Ambil detail pengiriman untuk mengembalikan stok BSJ/bahan
            $details = $pengirimanDetailModel->where('pengiriman_id', $id)->findAll();
            foreach ($details as $detail) {
                if (($detail['tipe_barang'] ?? null) === 'bsj') {
                    $bsj = $bsjModel->find($detail['barang_id']);
                    if ($bsj) {
                        $bsjModel->update($bsj['id'], [
                            'stok' => $bsj['stok'] + $detail['jumlah']
                        ]);
                    }
                } elseif (($detail['tipe_barang'] ?? null) === 'bahan') {
                    $bahan = $bahanModel->find($detail['barang_id']);
                    if ($bahan) {
                        $bahanModel->update($bahan['id'], [
                            'stok' => $bahan['stok'] + $detail['jumlah']
                        ]);
                    }
                }
            }
            // Hapus detail pengiriman
            $pengirimanDetailModel->where('pengiriman_id', $id)->delete();
            // Hapus pengiriman utama
            $pengirimanModel->delete($id);
            $db->transCommit();
            return redirect()->to('/produksi/pengiriman')->with('success', 'Data pengiriman berhasil dihapus.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Gagal menghapus pengiriman: ' . $e->getMessage());
        }
    }
    // FORM KARTU PERSEDIAAN BAHAN
    public function kartuPersediaanBahan()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->to('login');
        }

        $bahanModel = new \App\Models\BahanModel();
        $kartuModel = new \App\Models\KartuPersediaanModel();

        $bahan = $bahanModel->findAll();
        $bahanId = $this->request->getGet('bahan_id');
        $rawData = $kartuModel
            ->where('bahan_id', $bahanId)
            ->orderBy('tanggal', 'ASC')
            ->findAll();

        $saldo_qty = 0;
        $saldo_harga = 0;
        $kartu = [];
        $harga_satuan = 0;
        $satuan = '';
        if ($bahanId) {
            $bahanData = $bahanModel->find($bahanId);
            $harga_satuan = $bahanData['harga_satuan'] ?? 0;
            $satuan = strtolower($bahanData['satuan'] ?? '');
        }

        foreach ($rawData as $item) {
            // Selalu bagi jumlah di database dengan 1000
            // Bagi 1000 hanya jika satuan adalah kg atau liter
            if (in_array($satuan, ['kg', 'liter'])) {
                $jumlah_db = $item['jumlah'] / 1000;
            } else {
                $jumlah_db = $item['jumlah'];
            }
            $masuk_qty = $item['jenis'] === 'masuk' ? $jumlah_db : 0;
            $keluar_qty = $item['jenis'] === 'keluar' ? $jumlah_db : 0;
            $harga = $harga_satuan;
            if ($item['jenis'] === 'masuk') {
                $saldo_qty += $masuk_qty;
                $saldo_harga += $masuk_qty * $harga;
            } else {
                $saldo_qty -= $keluar_qty;
                $saldo_harga -= $keluar_qty * $harga;
            }
            $kartu[] = [
                'tanggal' => $item['tanggal'],
                'keterangan' => $item['keterangan'],
                'jenis' => $item['jenis'],
                'jumlah' => $item['jumlah'],
                'harga_satuan' => $harga,
                'saldo_qty' => $saldo_qty,
                'saldo_harga' => $saldo_harga,
                'masuk_qty' => $masuk_qty,
                'keluar_qty' => $keluar_qty,
                'satuan' => $satuan,
            ];
        }

        return view('produksi/persediaan/kartu/bahan', [
            'tittle' => 'Kartu Persediaan Bahan',
            'bahan' => $bahan,
            'kartu' => $kartu,
            'bahanId' => $bahanId
        ]);
    }

    // FORM KARTU PERSEDIAAN BSJ
    public function kartuPersediaanBSJ()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->to('login');
        }

        $bsjModel = new \App\Models\BSJModel();
        $kartuModel = new \App\Models\KartuPersediaanBSJModel();

        $bsj = $bsjModel->findAll();
        $kartu = [];

        $bsjId = $this->request->getGet('bsj_id');
        $tanggalMulai = $this->request->getGet('tanggal_mulai');
        $tanggalSelesai = $this->request->getGet('tanggal_selesai');

        if ($bsjId && $tanggalMulai && $tanggalSelesai) {
            $kartu = $kartuModel
                ->select('tanggal, jenis, jumlah, harga_satuan, keterangan')
                ->where('bsj_id', $bsjId)
                ->where('tanggal >=', $tanggalMulai)
                ->where('tanggal <=', $tanggalSelesai)
                ->orderBy('tanggal', 'ASC')
                ->findAll();

            // Hitung masuk, keluar, dan saldo secara manual
            $saldo = 0;
            foreach ($kartu as &$row) {
                $row['masuk_qty']  = ($row['jenis'] === 'masuk') ? $row['jumlah'] : 0;
                $row['keluar_qty'] = ($row['jenis'] === 'keluar') ? $row['jumlah'] : 0;
                $row['saldo_qty']  = $saldo = $saldo + $row['masuk_qty'] - $row['keluar_qty'];
                $row['harga_satuan'] = $row['harga_satuan'] ?? 0;
            }
        }

        return view('produksi/persediaan/kartu/bsj', [
            'tittle' => 'Kartu Persediaan BSJ',
            'bsj' => $bsj,
            'kartu' => $kartu,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'bsjId' => $bsjId
        ]);
    }

    // ========== LAPORAN ==========
    public function formCetakPembelian()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->to('login');
        }
        return view('produksi/laporan/form_filter_pembelian', [
            'tittle' => 'Form Cetak Laporan Pembelian'
        ]);
    }
    public function cetakPembelian()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->to('login');
        }

        $start = $this->request->getGet('tanggal_awal') ?? date('Y-m-01');
        $end = $this->request->getGet('tanggal_akhir') ?? date('Y-m-d');


        $pembelianModel = new \App\Models\PembelianModel();
        $pemasokModel = new \App\Models\PemasokModel();

        $dataPembelian = $pembelianModel
            ->select('pembelian.*, pemasok.nama as nama_pemasok')
            ->join('pemasok', 'pemasok.id = pembelian.pemasok_id', 'left')
            ->where('pembelian.tanggal >=', $start)
            ->where('pembelian.tanggal <=', $end)
            ->orderBy('pembelian.tanggal', 'ASC')
            ->findAll();

        // Pastikan field yang digunakan di view tersedia
        $pembelian = [];
        foreach ($dataPembelian as $row) {
            $row['jenis_pembayaran'] = $row['jenis_pembelian'] ?? '-';
            $pembelian[] = $row;
        }

        return view('produksi/laporan/cetak_pembelian', [
            'pembelian' => $pembelian,
            'start' => $start,
            'end' => $end,
        ]);
    }
    public function formCetakPersediaanBahan()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->to('login');
        }
        return view('produksi/laporan/form_filter_persediaan_bahan', [
            'tittle' => 'Form Cetak Laporan Persediaan Bahan'
        ]);
    }
    public function cetakPersediaanBahan()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->to('login');
        }

        $start = $this->request->getGet('tanggal_awal') ?? date('Y-m-01');
        $end   = $this->request->getGet('tanggal_akhir') ?? date('Y-m-d');

        $bahanModel = new \App\Models\BahanModel();
        $kartuModel = new \App\Models\KartuPersediaanModel();

        $bahan = $bahanModel->findAll();

        // Ambil semua pergerakan kartu persediaan dalam rentang waktu
        $kartu = $kartuModel
            ->where('tanggal >=', $start)
            ->where('tanggal <=', $end)
            ->orderBy('tanggal', 'ASC')
            ->findAll();

        return view('produksi/laporan/cetak_persediaan_bahan', [
            'tittle' => 'Laporan Persediaan Bahan',
            'start' => $start,
            'end' => $end,
            'bahan' => $bahan,
            'kartu' => $kartu,
        ]);
    }

    public function cetakPersediaanBSJ()
    {
        $tittle = 'Laporan Persediaan BSJ';
        $bsjModel = new \App\Models\BSJModel();
        $kartuModel = new \App\Models\KartuPersediaanBSJModel();
        $tanggalMulai = $this->request->getGet('tanggal_mulai');
        $tanggalSelesai = $this->request->getGet('tanggal_selesai');
        $builder = $bsjModel->select('bsj.id, bsj.kode, bsj.nama, bsj.stok, bsj.satuan');
        if ($tanggalMulai && $tanggalSelesai) {
            $builder->selectSum("CASE WHEN kp.jenis = 'masuk' THEN kp.jumlah ELSE 0 END", 'masuk');
            $builder->selectSum("CASE WHEN kp.jenis = 'keluar' THEN kp.jumlah ELSE 0 END", 'keluar');
            $builder->join('kartu_persediaan_bsj kp', 'kp.bsj_id = bsj.id', 'left')
                ->where('kp.tanggal >=', $tanggalMulai)
                ->where('kp.tanggal <=', $tanggalSelesai)
                ->groupBy('bsj.id');
        }
        $bsj = $builder->findAll();
        return view('produksi/laporan/cetak_persediaan_bsj', compact('tittle', 'bsj', 'tanggalMulai', 'tanggalSelesai'));
    }
    public function formCetakProduksi()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->to('login');
        }
        return view('produksi/laporan/form_filter_produksi', [
            'tittle' => 'Form Cetak Laporan Produksi'
        ]);
    }
    public function cetakProduksi()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->to('login');
        }

        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end = $this->request->getGet('end') ?? date('Y-m-d');

        $produksiModel = new \App\Models\ProduksiModel();
        $bsjModel = new \App\Models\BSJModel();

        $data = $produksiModel
            ->select('produksi.*, bsj.nama as nama_bsj')
            ->join('bsj', 'bsj.id = produksi.bsj_id', 'left')
            ->where('produksi.tanggal >=', $start)
            ->where('produksi.tanggal <=', $end)
            ->orderBy('produksi.tanggal', 'ASC')
            ->findAll();

        return view('produksi/laporan/cetak_produksi', [
            'tittle' => 'Laporan Produksi',
            'produksi' => $data,
            'start' => $start,
            'end' => $end,
        ]);
    }
}
