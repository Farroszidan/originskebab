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
    // 1. INDEX
    public function pembelianIndex()
    {
        if (!in_groups('produksi')) return redirect()->to('login');

        $pembelianModel = new \App\Models\PembelianModel();
        $detailPembelianModel = new \App\Models\DetailPembelianModel();

        $pembelian = $pembelianModel->orderBy('tanggal', 'DESC')->findAll();

        // Hitung total pembelian dari detail_pembelian
        foreach ($pembelian as &$p) {
            $details = $detailPembelianModel->where('pembelian_id', $p['id'])->findAll();
            $total = 0;
            foreach ($details as $d) {
                $total += (int) $d['subtotal'];
            }
            $p['total_harga'] = $total;
        }

        return view('produksi/pembelian/index', [
            'tittle' => 'Daftar Pembelian Bahan',
            'pembelian' => $pembelian
        ]);
    }

    // 3. SIMPAN
    public function pembelianSimpan()
    {
        if (!in_groups('produksi')) return redirect()->to('login');

        $pembelianModel       = new \App\Models\PembelianModel();
        $detailPembelianModel = new \App\Models\DetailPembelianModel();
        $bahanModel           = new \App\Models\BahanModel();
        $akunModel            = new \App\Models\AkunModel();
        $kartuModel           = new \App\Models\KartuPersediaanModel();

        $data = $this->request->getPost();
        $tanggal            = $data['tanggal'] ?? date('Y-m-d');
        $perintahKerjaId    = $data['perintah_kerja_id'] ?? null;

        $bahanIds           = $data['bahan_id'] ?? [];
        $jumlahs            = $data['jumlah'] ?? [];
        $hargaSatuans       = $data['harga_satuan'] ?? [];
        $subtotals          = $data['subtotal'] ?? [];
        $namaBahan          = $data['nama_bahan'] ?? [];
        $kategoris          = $data['kategori'] ?? [];
        $satuans            = $data['satuan'] ?? [];
        $pemasokIds         = $data['pemasok_id'] ?? [];
        $tipePembayarans    = $data['tipe_pembayaran'] ?? [];

        // Ambil semua file bukti transaksi per pemasok
        $buktiFiles = $this->request->getFiles();
        $buktiPemasok = [];

        $pemasokUnik = array_unique(array_filter($pemasokIds));
        foreach ($pemasokUnik as $id) {
            $field = 'bukti_transaksi_' . $id;
            if (isset($buktiFiles[$field]) && $buktiFiles[$field]->isValid()) {
                $fileName = $buktiFiles[$field]->getRandomName();
                $buktiFiles[$field]->move('uploads/bukti_transaksi', $fileName);
                $buktiPemasok[$id] = $fileName;
            } else {
                $buktiPemasok[$id] = null;
            }
        }

        // Hitung total harga pembelian
        $totalHarga = 0;
        foreach ($subtotals as $sub) {
            $subClean = (int)preg_replace('/[^\d]/', '', (string)$sub);
            $totalHarga += $subClean;
        }

        // Simpan data utama pembelian
        $pembelianId = $pembelianModel->insert([
            'tanggal'           => $tanggal,
            'perintah_kerja_id' => $perintahKerjaId,
            'total_harga'       => $totalHarga,
            'pemasok_id'        => $pemasokIds[0] ?? null,
            'tipe_pembayaran'   => strtolower($tipePembayarans[0] ?? 'tunai'),
        ]);

        // Simpan detail pembelian + update stok + kartu persediaan
        foreach ($bahanIds as $i => $bahanId) {
            $jumlah       = floatval($jumlahs[$i]);
            $hargaSatuan  = floatval($hargaSatuans[$i]);
            $subtotal     = (int)preg_replace('/[^\d]/', '', (string)($subtotals[$i] ?? 0));
            $satuan       = strtolower($satuans[$i] ?? '');
            $pemasokId    = $pemasokIds[$i] ?? null;
            $tipe         = strtolower($tipePembayarans[$i] ?? 'tunai');
            $bukti        = $buktiPemasok[$pemasokId] ?? null;

            // Simpan ke detail
            $detailPembelianModel->insert([
                'pembelian_id'     => $pembelianId,
                'bahan_id'         => $bahanId,
                'nama_bahan'       => $namaBahan[$i] ?? '',
                'kategori'         => $kategoris[$i] ?? '',
                'jumlah'           => $jumlah,
                'satuan'           => $satuan,
                'harga_satuan'     => $hargaSatuan,
                'subtotal'         => $subtotal,
                'pemasok_id'       => $pemasokId,
                'tipe_pembayaran'  => $tipe,
                'bukti_transaksi'  => $bukti,
            ]);

            // Hitung stok konversi (kg/liter ke gram/ml)
            $jumlahMasuk = ($satuan === 'kg' || $satuan === 'liter') ? $jumlah * 1000 : $jumlah;
            $bahan       = $bahanModel->find($bahanId);
            $stokBaru    = $bahan['stok'] + $jumlahMasuk;
            $saldoBaru   = (($satuan === 'kg' || $satuan === 'liter') ? $stokBaru / 1000 : $stokBaru) * $hargaSatuan;

            // Update stok dan saldo bahan
            $bahanModel->update($bahanId, [
                'stok'         => $stokBaru,
                'saldo'        => $saldoBaru
            ]);

            // Tambah ke kartu persediaan
            $kartuModel->insert([
                'bahan_id'     => $bahanId,
                'tanggal'      => $tanggal,
                'jenis'        => 'masuk',
                'jumlah'       => $jumlahMasuk,
                'harga_satuan' => $hargaSatuan,
                'keterangan'   => 'Pembelian bahan',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        // Update harga satuan bahan menggunakan rata-rata dari kartu persediaan
        $bahanUnik = array_unique($bahanIds);
        foreach ($bahanUnik as $id) {
            $rows = $kartuModel->where('bahan_id', $id)->where('jenis', 'masuk')->findAll();
            $totalQty = 0;
            $totalNilai = 0;
            foreach ($rows as $r) {
                $totalQty += $r['jumlah'];
                $totalNilai += $r['jumlah'] * $r['harga_satuan'];
            }
            $avg = ($totalQty > 0) ? $totalNilai / $totalQty : 0;
            $bahanModel->update($id, ['harga_satuan' => $avg]);
        }

        // Jurnal dan update akun
        $db     = \Config\Database::connect();
        $jurnal = $db->table('jurnal_umum');
        $akunPersediaan = $akunModel->where('kode_akun', 121)->first();
        $akunKas        = $akunModel->where('kode_akun', 101)->first();
        $akunUtang      = $akunModel->where('kode_akun', 201)->first();

        foreach ($bahanIds as $i => $id) {
            $sub = (int)preg_replace('/[^\d]/', '', (string)($subtotals[$i] ?? 0));
            $tipe = strtolower($tipePembayarans[$i] ?? 'tunai');
            $supplierId = $pemasokIds[$i] ?? null;

            if ($akunPersediaan) {
                // Debit persediaan
                $jurnal->insert([
                    'tanggal'     => $tanggal,
                    'akun_id'     => $akunPersediaan['id'],
                    'debit'       => $sub,
                    'kredit'      => 0,
                    'keterangan'  => "Pembelian " . ucfirst($tipe),
                    'supplier_id' => $supplierId
                ]);
                $akunModel->updateSaldo(121, $sub, 'debit');
            }

            if ($tipe === 'tunai' && $akunKas) {
                $jurnal->insert([
                    'tanggal'     => $tanggal,
                    'akun_id'     => $akunKas['id'],
                    'debit'       => 0,
                    'kredit'      => $sub,
                    'keterangan'  => 'Pembelian Tunai',
                    'supplier_id' => $supplierId
                ]);
                $akunModel->updateSaldo(101, $sub, 'kredit');
            } elseif ($tipe === 'kredit' && $akunUtang) {
                $jurnal->insert([
                    'tanggal'     => $tanggal,
                    'akun_id'     => $akunUtang['id'],
                    'debit'       => 0,
                    'kredit'      => $sub,
                    'keterangan'  => 'Pembelian Kredit',
                    'supplier_id' => $supplierId
                ]);
                $akunModel->updateSaldo(201, $sub, 'kredit');
            }
        }

        return redirect()->to('produksi/pembelian')->with('success', 'Data pembelian berhasil disimpan.');
    }


    // 2. INPUT
    public function pembelianInput()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Tidak memiliki akses');
        }

        $perintahKerjaModel = new \App\Models\PerintahKerjaModel();
        $detailPKModel = new \App\Models\DetailPerintahKerjaModel();
        $pemasokModel = new \App\Models\PemasokModel();
        $bahanModel = new \App\Models\BahanModel();

        $perintah_kerja_id = $this->request->getGet('perintah_kerja_id');

        // Ambil hanya perintah_kerja_id yang ada di detail_perintah_kerja (unique)
        $db = \Config\Database::connect();
        $builder = $db->table('detail_perintah_kerja');
        $builder->select('perintah_kerja_id');
        $builder->groupBy('perintah_kerja_id');
        $result = $builder->get()->getResultArray();
        $perintah_kerja_ids = array_column($result, 'perintah_kerja_id');

        // Ambil data perintah kerja hanya yang id-nya ada di detail_perintah_kerja
        $perintah_kerja = [];
        if (!empty($perintah_kerja_ids)) {
            $perintah_kerja = $perintahKerjaModel->whereIn('id', $perintah_kerja_ids)->findAll();
        }

        $data = [
            'tittle' => 'Input Pembelian Bahan Produksi',
            'perintah_kerja' => $perintah_kerja,
            'perintah_kerja_id' => $perintah_kerja_id,
            'pemasok' => $pemasokModel->findAll(),
            'bahan_all' => $bahanModel->findAll(),
            'bahan_dari_perintah' => []
        ];

        if ($perintah_kerja_id) {
            $detail = $detailPKModel->getByPerintahId($perintah_kerja_id);
            // Cek dan cocokkan bahan_id berdasarkan nama (harus cocok)
            $bahanAll = $data['bahan_all'];
            $bahanMap = [];
            foreach ($bahanAll as $b) {
                $bahanMap[$b['nama']] = $b;
            }

            $converted = [];
            foreach ($detail as $d) {
                if (isset($bahanMap[$d['nama']])) {
                    $bahan = $bahanMap[$d['nama']];
                    $converted[] = [
                        'bahan_id' => $bahan['id'],
                        'nama' => $bahan['nama'],
                        'jumlah' => $d['jumlah'],
                        'satuan' => $bahan['satuan'],
                        'kategori' => $bahan['kategori'],
                    ];
                }
            }

            $data['bahan_dari_perintah'] = $converted;
        }

        return view('produksi/pembelian/tambah', $data);
    }

    // 4. DETAIL
    public function pembelianDetail($id)
    {
        if (!in_groups('produksi')) return redirect()->to('login');

        $pembelianModel       = new \App\Models\PembelianModel();
        $detailPembelianModel = new \App\Models\DetailPembelianModel();
        $pemasokModel         = new \App\Models\PemasokModel();

        $pembelian = $pembelianModel->find($id);
        if (!$pembelian) {
            return redirect()->to('produksi/pembelian')->with('error', 'Pembelian tidak ditemukan.');
        }

        // Ambil nama pemasok untuk header jika ada
        if (!empty($pembelian['pemasok_id'])) {
            $pemasok = $pemasokModel->find($pembelian['pemasok_id']);
            $pembelian['nama_pemasok'] = $pemasok['nama'] ?? '-';
        }

        // Pastikan total_harga selalu ada di $pembelian
        if (!isset($pembelian['total_harga'])) {
            // Hitung total dari detail jika belum ada
            $detailTemp = $detailPembelianModel->where('pembelian_id', $id)->findAll();
            $total_harga = 0;
            foreach ($detailTemp as $d) {
                $total_harga += (int) $d['subtotal'];
            }
            $pembelian['total_harga'] = $total_harga;
        }

        $detail = $detailPembelianModel->where('pembelian_id', $id)->findAll();
        // Ambil nama pemasok untuk setiap detail
        foreach ($detail as &$d) {
            if (!empty($d['pemasok_id'])) {
                $pemasok = $pemasokModel->find($d['pemasok_id']);
                $d['nama_pemasok'] = $pemasok['nama'] ?? '-';
            } else {
                $d['nama_pemasok'] = '-';
            }
        }

        return view('produksi/pembelian/detail', [
            'tittle'     => 'Detail Pembelian',
            'pembelian' => $pembelian,
            'detail'    => $detail
        ]);
    }

    // AJAX: Ambil detail bahan dari perintah kerja (untuk form pembelian)
    public function get_detail_perintah_kerja($id)
    {
        if (!in_groups(['admin', 'produksi'])) return $this->response->setStatusCode(403);
        $detailPKModel = new \App\Models\DetailPerintahKerjaModel();
        $bahanModel = new \App\Models\BahanModel();
        $detail = $detailPKModel->where('perintah_kerja_id', $id)->findAll();
        $result = [];
        foreach ($detail as $d) {
            // Cari id bahan dari nama & satuan
            $bahan = $bahanModel->where('nama', $d['nama'])->where('satuan', $d['satuan'])->first();
            $result[] = [
                'id' => $d['id'],
                'perintah_kerja_id' => $d['perintah_kerja_id'],
                'bahan_id' => $bahan['id'] ?? '',
                'nama' => $d['nama'],
                'kategori' => $d['kategori'] ?? ($bahan['kategori'] ?? ''),
                'satuan' => $d['satuan'],
                'jumlah' => $d['jumlah'],
                'pembulatan' => $d['pembulatan'] ?? null,
            ];
        }
        return $this->response->setJSON($result);
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
    public function hapusPembelian($id)
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
        $pembelianModel = new \App\Models\PembelianModel();
        $detailPembelianModel = new \App\Models\DetailPembelianModel();

        // Hapus detail pembelian terlebih dahulu
        $detailPembelianModel->where('pembelian_id', $id)->delete();
        // Hapus pembelian utama
        $pembelianModel->delete($id);

        return redirect()->to('produksi/pembelian')->with('success', 'Data pembelian berhasil dihapus.');
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

        return view('produksi/persediaan/bsj/bsj', $data);
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
        return view('produksi/persediaan/bsj/tambah_bsj', $data);
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
        return view('produksi/persediaan/bsj/edit_bsj', $data);
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

        $data = [
            'tittle'              => 'Input Produksi',
            'bsj'                 => $bsjModel->findAll(),
            'bahan_all'           => $bahanModel->findAll(),
            'komposisi'           => $komposisiModel->findAll(),
            'tenaga_kerja'        => $tenaga_kerja,
            'overhead'            => $overhead,
            'total_tenaga_kerja'  => array_sum(array_column($tenaga_kerja, 'biaya')),
            'total_bop'           => array_sum(array_column($overhead, 'biaya')) / 3,
            'bop_all'             => $overhead,
            'tenaga_kerja_all'    => $tenaga_kerja
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

        $tanggal  = $this->request->getPost('tanggal') ?? date('Y-m-d');
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
            $subtotal = $tk['biaya'] * $jumlah;
            $detailModel->save([
                'produksi_id'   => $produksiId,
                'kategori'      => 'tenaga_kerja',
                'nama_biaya'    => $tk['nama'],
                'jumlah'        => $jumlah,
                'harga_satuan'  => $tk['biaya'],
                'subtotal'      => $subtotal,
            ]);
            $totalTenagaKerja += $subtotal;
        }

        // === BOP: ambil sesuai jumlah produksi ===
        $overhead = $bopModel->findAll();
        $jenisBOP = '';
        if ($jumlah < 500) {
            $jenisBOP = 'sedikit';
        } elseif ($jumlah >= 500 && $jumlah <= 1000) {
            $jenisBOP = 'sedang';
        } elseif ($jumlah > 1000) {
            $jenisBOP = 'banyak';
        }
        $totalBOP = 0;
        foreach ($overhead as $bop) {
            if ($bop['jenis_bsj'] === $jenisBOP) {
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
                        // harga_satuan tidak diupdate di sini
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
                // Hitung HPP per unit
                $hpp_per_unit = ($produksi['jumlah'] > 0) ? ($produksi['total_biaya'] / $produksi['jumlah']) : 0;
                $kartubsjModel->save([
                    'bsj_id'     => $bsj['id'],
                    'tanggal'    => date('Y-m-d'),
                    'jenis'      => 'masuk',
                    'jumlah'     => $produksi['jumlah'],
                    'harga_satuan' => $hpp_per_unit,
                    'keterangan' => 'Hasil Produksi No: ' . $produksi['id']
                ]);
                // Update jurnal umum dan saldo akun
                $akunModel = new \App\Models\AkunModel();
                $db = \Config\Database::connect();
                $jurnal = $db->table('jurnal_umum');
                // Ambil total biaya dari produksi
                $total_biaya = $produksi['total_biaya'];
                // Persediaan BSJ (122) - debit
                $akunBSJ = $akunModel->where('kode_akun', 122)->first();
                // Persediaan bahan baku (121) - kredit
                $akunBahan = $akunModel->where('kode_akun', 121)->first();
                // Utang gaji (202) - kredit
                $akunGaji = $akunModel->where('kode_akun', 202)->first();
                // Beban operasional produksi (607) - kredit
                $akunBOP = $akunModel->where('kode_akun', 607)->first();
                // Hitung komponen biaya
                $detailModel = new \App\Models\DetailProduksiModel();
                $detail = $detailModel->where('produksi_id', $produksi['id'])->findAll();
                $bahanBaku = 0;
                $tenagaKerja = 0;
                $bop = 0;
                foreach ($detail as $d) {
                    if ($d['kategori'] == 'baku') $bahanBaku += $d['subtotal'];
                    if ($d['kategori'] == 'tenaga_kerja') $tenagaKerja += $d['subtotal'];
                    if ($d['kategori'] == 'overhead') $bop += $d['subtotal'];
                }
                // Jurnal: debit persediaan BSJ, kredit bahan baku, utang gaji, beban operasional
                if ($akunBSJ) {
                    $jurnal->insert([
                        'tanggal' => date('Y-m-d'),
                        'akun_id' => $akunBSJ['id'],
                        'debit' => $total_biaya,
                        'kredit' => 0,
                        'keterangan' => 'Produksi BSJ No: ' . $produksi['id'],
                    ]);
                    $akunModel->updateSaldo(122, $total_biaya, 'debit');
                }
                if ($akunBahan && $bahanBaku > 0) {
                    $jurnal->insert([
                        'tanggal' => date('Y-m-d'),
                        'akun_id' => $akunBahan['id'],
                        'debit' => 0,
                        'kredit' => $bahanBaku,
                        'keterangan' => 'Produksi BSJ No: ' . $produksi['id'],
                    ]);
                    $akunModel->updateSaldo(121, $bahanBaku, 'kredit');
                }
                if ($akunGaji && $tenagaKerja > 0) {
                    $jurnal->insert([
                        'tanggal' => date('Y-m-d'),
                        'akun_id' => $akunGaji['id'],
                        'debit' => 0,
                        'kredit' => $tenagaKerja,
                        'keterangan' => 'Produksi BSJ No: ' . $produksi['id'],
                    ]);
                    $akunModel->updateSaldo(202, $tenagaKerja, 'kredit');
                }
                if ($akunBOP && $bop > 0) {
                    $jurnal->insert([
                        'tanggal' => date('Y-m-d'),
                        'akun_id' => $akunBOP['id'],
                        'debit' => 0,
                        'kredit' => $bop,
                        'keterangan' => 'Produksi BSJ No: ' . $produksi['id'],
                    ]);
                    $akunModel->updateSaldo(607, $bop, 'kredit');
                }
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
            return redirect()->to('login');
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
        // Update harga dan saldo di tabel BSJ
        $bsjModel = new \App\Models\BSJModel();
        $bsj = $bsjModel->find($produksi['bsj_id']);
        if ($bsj) {
            $bsjModel->update($bsj['id'], [
                'harga' => $hpp_per_unit,
                'saldo' => $bsj['stok'] * $hpp_per_unit
            ]);
        }
        return redirect()->to(base_url('produksi/hpp/form'))->with('success', 'Data HPP berhasil disimpan dan harga BSJ telah diupdate.');
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
        $perintahPengirimanModel = new \App\Models\PerintahPengirimanModel();
        $perintahPengirimanDetailModel = new \App\Models\PerintahPengirimanDetailModel();
        $data = [
            'tittle' => 'Form Pengiriman Barang',
            'outlets' => $outletModel->findAll(),
            'barang_bsj' => $bsjModel->findAll(),
            'bahan' => $bahanModel->findAll(),
            'perintah_pengiriman' => $perintahPengirimanModel->orderBy('tanggal', 'DESC')->findAll(),
            'perintah_pengiriman_detail' => $perintahPengirimanDetailModel->findAll(),
        ];
        return view('produksi/pengiriman/form_pengiriman', $data);
    }
    // ENDPOINT AJAX: Ambil detail perintah pengiriman (outlet & item)
    public function getPerintahPengirimanDetail($id)
    {
        $outletModel = new \App\Models\OutletModel();
        $perintahPengirimanOutletModel = new \App\Models\PerintahPengirimanOutletModel();
        $perintahPengirimanDetailModel = new \App\Models\PerintahPengirimanDetailModel();
        $bsjModel = new \App\Models\BSJModel();
        $bahanModel = new \App\Models\BahanModel();
        $outletRows = $perintahPengirimanOutletModel->where('perintah_pengiriman_id', $id)->findAll();
        $result = [];
        foreach ($outletRows as $outlet) {
            $items = $perintahPengirimanDetailModel->where('perintah_pengiriman_outlet_id', $outlet['id'])->findAll();
            $itemArr = [];
            foreach ($items as $item) {
                $nama_barang = $item['nama_barang'];
                // Jika nama_barang kosong, ambil dari master
                if (!$nama_barang) {
                    if ($item['tipe'] === 'bsj') {
                        $bsj = $bsjModel->find($item['barang_id']);
                        $nama_barang = $bsj['nama'] ?? '';
                    } elseif ($item['tipe'] === 'bahan') {
                        $bahan = $bahanModel->find($item['barang_id']);
                        $nama_barang = $bahan['nama'] ?? '';
                    }
                }
                $itemArr[] = [
                    'tipe' => $item['tipe'],
                    'barang_id' => $item['barang_id'],
                    'nama_barang' => $nama_barang,
                    'jumlah' => $item['jumlah'],
                    'satuan' => $item['satuan'],
                ];
            }
            $result[] = [
                'outlet_id' => $outlet['outlet_id'],
                'nama_outlet' => $outletModel->find($outlet['outlet_id'])['nama_outlet'] ?? $outlet['outlet_id'],
                'keterangan' => $outlet['keterangan'],
                'items' => $itemArr
            ];
        }
        return $this->response->setJSON(['success' => true, 'data' => $result]);
    }


    public function pengirimanSimpan()
    // DEBUG: log data yang diterima dari form
    // file_put_contents('debug_pengiriman.txt', print_r($outletForm, true));
    {
        $notifikasiModel = new \App\Models\NotifikasiModel();
        $bsjModel = new \App\Models\BSJModel();
        $bahanModel = new \App\Models\BahanModel();
        $kartubsjModel = new \App\Models\KartuPersediaanBSJModel();
        $kartuModel = new \App\Models\KartuPersediaanModel();
        $pengirimanModel = new \App\Models\PengirimanModel();
        $pengirimanDetailModel = new \App\Models\PengirimanDetailModel();


        $tanggal = $this->request->getPost('tanggal');
        $catatan = $this->request->getPost('catatan');
        $perintahPengirimanId = $this->request->getPost('perintah_pengiriman_id');
        $outletForm = $this->request->getPost('outlet'); // array dari form: outlet[x][id_outlet], dst

        // Mapping ulang agar $outlets = [ ['outlet_id'=>, 'keterangan'=>, 'items'=>[...]], ... ]
        $outlets = [];
        if (is_array($outletForm)) {
            foreach ($outletForm as $outletBlock) {
                $outlet_id = $outletBlock['id_outlet'] ?? null;
                $keterangan = $outletBlock['keterangan'] ?? null;
                $items = [];
                if (isset($outletBlock['items']) && is_array($outletBlock['items'])) {
                    foreach ($outletBlock['items'] as $item) {
                        $items[] = [
                            'tipe' => $item['jenis'] ?? null,
                            'barang_id' => $item['id_barang'] ?? null,
                            'jumlah' => $item['jumlah'] ?? null,
                            'satuan' => $item['satuan'] ?? null,
                            'nama_barang' => $item['nama_barang'] ?? null,
                        ];
                    }
                }
                // Validasi: pastikan items tidak kosong
                if (empty($items)) {
                    return redirect()->back()->with('error', 'Data item outlet tidak boleh kosong. Silakan pilih perintah pengiriman yang benar.');
                }
                $outlets[] = [
                    'outlet_id' => $outlet_id,
                    'keterangan' => $keterangan,
                    'items' => $items
                ];
            }
        }

        if (!$tanggal || !$outlets || !is_array($outlets) || count($outlets) == 0) {
            return redirect()->back()->with('error', 'Semua field harus diisi.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // DEBUG: log data outlet dan item sebelum proses
            file_put_contents('debug_pengiriman.txt', "==== POST DATA ===\n" . print_r($_POST, true), FILE_APPEND);
            $outletModel = new \App\Models\OutletModel();
            foreach ($outlets as $outletBlock) {
                $outletId = $outletBlock['outlet_id'] ?? null;
                $keterangan = $outletBlock['keterangan'] ?? null;
                $items = $outletBlock['items'] ?? [];
                if (!$outletId || !is_array($items) || count($items) == 0) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Data outlet atau item tidak valid.');
                }
                // Ambil nama outlet
                $outletRow = $outletModel->find($outletId);
                $namaOutlet = $outletRow['nama_outlet'] ?? '';
                // 1. Insert ke tabel pengiriman (satu pengiriman per outlet)
                $pengirimanData = [
                    'tanggal' => $tanggal,
                    'user_id' => user_id(),
                    'outlet_id' => $outletId,
                    'catatan' => $catatan,
                    'keterangan' => $keterangan,
                    'perintah_pengiriman_id' => $perintahPengirimanId,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                // Simpan nama outlet jika ada kolomnya, jika tidak, tambahkan ke keterangan
                if (array_key_exists('nama_outlet', $pengirimanModel->allowedFields ?? [])) {
                    $pengirimanData['nama_outlet'] = $namaOutlet;
                } else {
                    // Tambahkan ke keterangan jika belum ada
                    if ($namaOutlet && (empty($keterangan) || strpos($keterangan, $namaOutlet) === false)) {
                        $pengirimanData['keterangan'] = trim(($keterangan ? $keterangan . ' ' : '') . '[Outlet: ' . $namaOutlet . ']');
                    }
                }
                $pengirimanModel->insert($pengirimanData);
                $pengirimanId = $pengirimanModel->getInsertID();

                // 2. Insert detail pengiriman dan update stok BSJ/bahan
                foreach ($items as $barang) {
                    $tipe = $barang['tipe'] ?? null;
                    $barangId = $barang['barang_id'] ?? null;
                    $jumlah = $barang['jumlah'] ?? 0;
                    $satuan = $barang['satuan'] ?? '';
                    $namaBarang = $barang['nama_barang'] ?? '-';

                    if ($tipe === 'bsj') {
                        $bsj = $bsjModel->find($barangId);
                        $nama_bsj = $bsj['nama'] ?? '-';
                        $stok_bsj = $bsj['stok'] ?? 0;
                        $satuan_bsj = $bsj['satuan'] ?? '-';
                        // DEBUG: log stok dan jumlah
                        file_put_contents('debug_pengiriman.txt', "BSJ: id=$barangId, nama=$nama_bsj, stok=$stok_bsj, jumlah=$jumlah, satuan=$satuan_bsj\n", FILE_APPEND);
                        if (!$bsj || $stok_bsj < 0 || $jumlah < 0) {
                            $db->transRollback();
                            return redirect()->back()->with('error', 'Stok BSJ tidak valid untuk ' . $nama_bsj);
                        }
                        if ($stok_bsj < $jumlah) {
                            file_put_contents('debug_pengiriman.txt', "ERROR: stok kurang\n", FILE_APPEND);
                            $db->transRollback();
                            return redirect()->back()->with('error', 'Stok BSJ tidak cukup untuk ' . $nama_bsj);
                        }
                        $namaBarang = $nama_bsj;
                        $bsjModel->update($barangId, [
                            'stok' => $stok_bsj - $jumlah
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
                        $nama_bahan = $bahan['nama'] ?? '-';
                        $stok_bahan = $bahan['stok'] ?? 0;
                        $satuan_bahan = strtolower($bahan['satuan'] ?? '-');
                        $hargaSatuan = $bahan['harga_satuan'] ?? 0;
                        // Konversi jumlah ke satuan simpan (gram/ml) jika input dari form dalam kg/liter
                        $jumlah_db = $jumlah;
                        if ($satuan_bahan === 'kg' || $satuan_bahan === 'liter') {
                            // Jika input jumlah dari form dalam kg/liter, konversi ke gram/ml
                            $jumlah_db = $jumlah * 1000;
                        }
                        // DEBUG: log stok dan jumlah
                        file_put_contents('debug_pengiriman.txt', "Bahan: id=$barangId, nama=$nama_bahan, stok=$stok_bahan, jumlah=$jumlah, jumlah_db=$jumlah_db, satuan=$satuan_bahan\n", FILE_APPEND);
                        if (!$bahan || $stok_bahan < 0 || $jumlah_db < 0) {
                            $db->transRollback();
                            return redirect()->back()->with('error', 'Stok bahan tidak valid untuk ' . $nama_bahan);
                        }
                        if ($stok_bahan < $jumlah_db) {
                            file_put_contents('debug_pengiriman.txt', "ERROR: stok kurang\n", FILE_APPEND);
                            $db->transRollback();
                            return redirect()->back()->with('error', 'Stok bahan tidak cukup untuk ' . $nama_bahan);
                        }
                        $namaBarang = $nama_bahan;
                        // Hitung stok baru
                        $stokBaru = $stok_bahan - $jumlah_db;
                        // Hitung saldo baru dengan konversi tampilan
                        $stokBaruTampil = ($satuan_bahan === 'kg' || $satuan_bahan === 'liter') ? $stokBaru / 1000 : $stokBaru;
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
                            'jumlah'     => $jumlah_db,
                            'keterangan' => 'Pengiriman No: ' . $pengirimanId
                        ]);
                    } else {
                        $db->transRollback();
                        return redirect()->back()->with('error', 'Tipe barang tidak valid.');
                    }
                    $pengirimanDetailModel->insert([
                        'pengiriman_id' => $pengirimanId,
                        'barang_id' => $barangId,
                        'tipe' => $tipe,
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
            }

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
                'tipe'   => $d['tipe'] ?? '-',
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
        $detailPembelianModel = new \App\Models\DetailPembelianModel();

        $bahan = $bahanModel->findAll();
        $bahanId = $this->request->getGet('bahan_id');
        $rawDataQuery = $kartuModel
            ->where('bahan_id', $bahanId)
            ->orderBy('tanggal', 'ASC');
        // Jika ada kolom created_at, urutkan juga berdasarkan waktu input
        $fields = $kartuModel->db->getFieldNames($kartuModel->table);
        if (in_array('created_at', $fields)) {
            $rawDataQuery = $rawDataQuery->orderBy('created_at', 'ASC');
        }
        $rawData = $rawDataQuery->orderBy('id', 'ASC')->findAll();

        $saldo_qty = 0;
        $saldo_harga = 0;
        $saldo_awal_qty = 0;
        $saldo_awal_harga = 0;
        $satuan_awal = '';
        $kartu = [];
        $satuan = '';
        $bahanData = null;
        if ($bahanId) {
            $bahanData = $bahanModel->find($bahanId);
            $satuan = strtolower($bahanData['satuan'] ?? '');
            $satuan_awal = $satuan;
            // Saldo awal diambil dari stok dan saldo di tabel bahan
            if ($bahanData) {
                if (in_array($satuan, ['kg', 'liter'])) {
                    $saldo_awal_qty = $bahanData['stok'] / 1000;
                } else {
                    $saldo_awal_qty = $bahanData['stok'];
                }
                $saldo_awal_harga = $bahanData['saldo'];
            }
            // Set saldo_qty dan saldo_harga awal
            $saldo_qty = $saldo_awal_qty;
            $saldo_harga = $saldo_awal_harga;
        }

        foreach ($rawData as $item) {
            if (in_array($satuan, ['kg', 'liter'])) {
                $jumlah_db = $item['jumlah'] / 1000;
            } else {
                $jumlah_db = $item['jumlah'];
            }
            $masuk_qty = $item['jenis'] === 'masuk' ? $jumlah_db : 0;
            $keluar_qty = $item['jenis'] === 'keluar' ? $jumlah_db : 0;

            $harga = 0;
            if ($item['jenis'] === 'masuk') {
                // Cari detail pembelian berdasarkan pembelian_id jika ada di kartu persediaan
                $detail = null;
                if (isset($item['pembelian_id'])) {
                    $detail = $detailPembelianModel
                        ->where('pembelian_id', $item['pembelian_id'])
                        ->where('bahan_id', $bahanId)
                        ->where('jumlah', $item['jumlah'])
                        ->orderBy('id', 'ASC')
                        ->first();
                }
                // Jika tidak ada pembelian_id, fallback ke pencocokan bahan_id dan jumlah saja (kurang akurat)
                if (!$detail) {
                    $detail = $detailPembelianModel
                        ->where('bahan_id', $bahanId)
                        ->where('jumlah', $item['jumlah'])
                        ->orderBy('id', 'ASC')
                        ->first();
                }
                if ($detail && isset($detail['harga_satuan'])) {
                    $harga = $detail['harga_satuan'];
                } else {
                    $harga = isset($item['harga_satuan']) ? $item['harga_satuan'] : 0;
                }
                $saldo_qty += $masuk_qty;
                $saldo_harga += $masuk_qty * $harga;
            } else {
                $harga = $bahanData['harga_satuan'] ?? 0;
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
            'bahanId' => $bahanId,
            'saldo_awal_qty' => $saldo_awal_qty,
            'saldo_awal_harga' => $saldo_awal_harga,
            'satuan_awal' => $satuan_awal
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
            // Urutkan: tanggal ASC, jenis ASC (masuk dulu), created_at ASC, id ASC
            $kartu = $kartuModel
                ->select('kartu_persediaan_bsj.*, produksi.total_biaya, produksi.jumlah as jumlah_produksi')
                ->join(
                    'produksi',
                    'produksi.bsj_id = kartu_persediaan_bsj.bsj_id 
                 AND kartu_persediaan_bsj.jenis = "masuk" 
                 AND produksi.tanggal = kartu_persediaan_bsj.tanggal',
                    'left'
                )
                ->where('kartu_persediaan_bsj.bsj_id', $bsjId)
                ->where('kartu_persediaan_bsj.tanggal >=', $tanggalMulai)
                ->where('kartu_persediaan_bsj.tanggal <=', $tanggalSelesai)
                // Jenis ASC: 'keluar' > 'masuk', jadi pakai FIELD agar 'masuk' dulu
                ->orderBy('kartu_persediaan_bsj.tanggal', 'ASC')
                ->orderBy('FIELD(kartu_persediaan_bsj.jenis, "masuk", "keluar")', '', false)
                ->orderBy('kartu_persediaan_bsj.created_at', 'ASC')
                ->orderBy('kartu_persediaan_bsj.id', 'ASC')
                ->findAll();

            $saldo = 0;
            foreach ($kartu as &$row) {
                $row['masuk_qty']  = ($row['jenis'] === 'masuk') ? $row['jumlah'] : 0;
                $row['keluar_qty'] = ($row['jenis'] === 'keluar') ? $row['jumlah'] : 0;
                $row['saldo_qty']  = $saldo = $saldo + $row['masuk_qty'] - $row['keluar_qty'];

                // Hitung harga satuan hanya jika masuk
                if ($row['jenis'] === 'masuk') {
                    $hargaSatuan = ($row['jumlah_produksi'] ?? 0) > 0 ? ($row['total_biaya'] / $row['jumlah_produksi']) : 0;
                } else {
                    $hargaSatuan = 0;
                }
                $row['harga_satuan'] = $hargaSatuan;
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
        $detailPembelianModel = new \App\Models\DetailPembelianModel();

        $dataPembelian = $pembelianModel
            ->select('pembelian.*, pemasok.nama as nama_pemasok')
            ->join('pemasok', 'pemasok.id = pembelian.pemasok_id', 'left')
            ->where('pembelian.tanggal >=', $start)
            ->where('pembelian.tanggal <=', $end)
            ->orderBy('pembelian.tanggal', 'ASC')
            ->findAll();

        // Gabungkan detail pembelian ke setiap pembelian
        $pembelian = [];
        foreach ($dataPembelian as $row) {
            $row['jenis_pembayaran'] = $row['jenis_pembelian'] ?? '-';
            $detailArr = $detailPembelianModel->where('pembelian_id', $row['id'])->findAll();
            // Ambil nama pemasok dari tabel pemasok untuk setiap detail
            foreach ($detailArr as &$d) {
                if (isset($d['pemasok_id']) && $d['pemasok_id']) {
                    $pemasok = $pemasokModel->find($d['pemasok_id']);
                    $d['nama_pemasok'] = $pemasok['nama'] ?? '-';
                } else {
                    $d['nama_pemasok'] = '-';
                }
            }
            $row['detail'] = $detailArr;
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

        // Hitung harga rata-rata (average) untuk setiap bahan
        $harga_avg = [];
        foreach ($bahan as $b) {
            $saldo_qty = 0;
            $saldo_harga = 0;
            foreach ($kartu as $k) {
                if ($k['bahan_id'] == $b['id']) {
                    // Konversi jumlah jika satuan kg/liter
                    $jumlah = (strtolower($b['satuan']) == 'kg' || strtolower($b['satuan']) == 'liter') ? $k['jumlah'] / 1000 : $k['jumlah'];
                    if ($k['jenis'] == 'masuk') {
                        $saldo_qty += $jumlah;
                        $saldo_harga += $jumlah * ($k['harga_satuan'] ?? 0);
                    } elseif ($k['jenis'] == 'keluar') {
                        $saldo_qty -= $jumlah;
                        $saldo_harga -= $jumlah * ($k['harga_satuan'] ?? 0);
                    }
                }
            }
            $harga_avg[$b['id']] = ($saldo_qty > 0) ? ($saldo_harga / $saldo_qty) : 0;
        }
        return view('produksi/laporan/cetak_persediaan_bahan', [
            'tittle' => 'Laporan Persediaan Bahan',
            'start' => $start,
            'end' => $end,
            'bahan' => $bahan,
            'kartu' => $kartu,
            'harga_avg' => $harga_avg,
        ]);
    }
    public function formCetakPersediaanBSJ()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->to('login');
        }
        return view('produksi/laporan/form_filter_persediaan_bsj', [
            'tittle' => 'Form Cetak Laporan Persediaan BSJ'
        ]);
    }
    public function cetakPersediaanBSJ()
    {
        if (!in_groups(['admin', 'produksi'])) {
            return redirect()->to('login');
        }

        $start = $this->request->getGet('tanggal_awal') ?? date('Y-m-01');
        $end   = $this->request->getGet('tanggal_akhir') ?? date('Y-m-d');

        $bsjModel = new \App\Models\BSJModel();
        $kartuModel = new \App\Models\KartuPersediaanBSJModel();

        $bsj = $bsjModel->findAll();

        // Ambil semua transaksi kartu persediaan BSJ dalam rentang waktu
        $kartu = $kartuModel
            ->where('tanggal >=', $start)
            ->where('tanggal <=', $end)
            ->orderBy('tanggal', 'ASC')
            ->findAll();

        // Hitung saldo dan harga rata-rata per BSJ
        $bsjData = [];
        foreach ($bsj as $b) {
            $stok_awal = 0;
            $masuk = 0;
            $keluar = 0;
            $totalHargaMasuk = 0;
            $totalQtyMasuk = 0;
            $saldo_akhir = 0;
            foreach ($kartu as $k) {
                if ($k['bsj_id'] == $b['id']) {
                    if (strtotime($k['tanggal']) < strtotime($start)) {
                        if ($k['jenis'] == 'masuk') {
                            $stok_awal += $k['jumlah'];
                        } elseif ($k['jenis'] == 'keluar') {
                            $stok_awal -= $k['jumlah'];
                        }
                    } else {
                        if ($k['jenis'] == 'masuk') {
                            $masuk += $k['jumlah'];
                            // Jika ada harga, hitung total harga masuk
                            if (isset($k['harga_satuan'])) {
                                $totalHargaMasuk += $k['jumlah'] * $k['harga_satuan'];
                                $totalQtyMasuk += $k['jumlah'];
                            }
                        } elseif ($k['jenis'] == 'keluar') {
                            $keluar += $k['jumlah'];
                        }
                    }
                }
            }
            $stok_akhir = $stok_awal + $masuk - $keluar;
            $harga_avg = $totalQtyMasuk > 0 ? ($totalHargaMasuk / $totalQtyMasuk) : 0;
            $saldo_akhir = $stok_akhir * $harga_avg;
            $bsjData[] = [
                'id' => $b['id'],
                'nama' => $b['nama'],
                'satuan' => $b['satuan'],
                'stok_awal' => $stok_awal,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'stok_akhir' => $stok_akhir,
                'harga_avg' => $harga_avg,
                'saldo_akhir' => $saldo_akhir,
            ];
        }

        return view('produksi/laporan/cetak_persediaan_bsj', [
            'tittle' => 'Laporan Persediaan BSJ',
            'start' => $start,
            'end' => $end,
            'bsjData' => $bsjData,
        ]);
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
