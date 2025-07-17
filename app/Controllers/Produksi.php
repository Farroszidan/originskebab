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
                $bahanModel->update($bahanId, [
                    'stok' => $bahan['stok'] + $jumlah_db
                ]);
            }

            $total += $subtotal;
        }

        // Update total di header pembelian
        $pembelianModel->update($pembelianId, [
            'total' => $total
        ]);

        // Jurnal otomatis ke neraca saldo
        $neracaSaldoModel = model('NeracaSaldoModel');
        if ($jenisPembelian === 'tunai') {
            // Kas (101) berkurang di debet, Persediaan bahan penolong (108) bertambah di debet
            $kas = $neracaSaldoModel->where('kode_akun', '101')->first();
            if ($kas) {
                $kasDebet = is_null($kas['debet']) ? 0 : $kas['debet'];
                $neracaSaldoModel->update($kas['id'], [
                    'debet' => $kasDebet - $total
                ]);
                // Update saldo_awal di tabel akun (kas)
                $akunKas = $akunModel->where('kode_akun', '101')->first();
                if ($akunKas) {
                    $saldoKas = is_null($akunKas['saldo_awal']) ? 0 : $akunKas['saldo_awal'];
                    $akunModel->update($akunKas['id'], [
                        'saldo_awal' => $saldoKas - $total
                    ]);
                }
            }
            $penolong = $neracaSaldoModel->where('kode_akun', '108')->first();
            if ($penolong) {
                $penolongDebet = is_null($penolong['debet']) ? 0 : $penolong['debet'];
                $neracaSaldoModel->update($penolong['id'], [
                    'debet' => $penolongDebet + $total
                ]);
                // Update saldo_awal di tabel akun (persediaan bahan penolong)
                $akunPenolong = $akunModel->where('kode_akun', '108')->first();
                if ($akunPenolong) {
                    $saldoPenolong = is_null($akunPenolong['saldo_awal']) ? 0 : $akunPenolong['saldo_awal'];
                    $akunModel->update($akunPenolong['id'], [
                        'saldo_awal' => $saldoPenolong + $total
                    ]);
                }
            }
        } elseif ($jenisPembelian === 'kredit') {
            $baku = $neracaSaldoModel->where('kode_akun', '107')->first();
            if ($baku) {
                $bakuDebet = is_null($baku['debet']) ? 0 : $baku['debet'];
                $neracaSaldoModel->update($baku['id'], [
                    'debet' => $bakuDebet + $total
                ]);
                // Update saldo_awal di tabel akun (persediaan bahan baku)
                $akunBaku = $akunModel->where('kode_akun', '107')->first();
                if ($akunBaku) {
                    $saldoBaku = is_null($akunBaku['saldo_awal']) ? 0 : $akunBaku['saldo_awal'];
                    $akunModel->update($akunBaku['id'], [
                        'saldo_awal' => $saldoBaku + $total
                    ]);
                }
            }
            $utang = $neracaSaldoModel->where('kode_akun', '201')->first();
            if ($utang) {
                $utangKredit = is_null($utang['kredit']) ? 0 : $utang['kredit'];
                $neracaSaldoModel->update($utang['id'], [
                    'kredit' => $utangKredit + $total
                ]);
                // Update saldo_awal di tabel akun (utang usaha)
                $akunUtang = $akunModel->where('kode_akun', '201')->first();
                if ($akunUtang) {
                    $saldoUtang = is_null($akunUtang['saldo_awal']) ? 0 : $akunUtang['saldo_awal'];
                    $akunModel->update($akunUtang['id'], [
                        'saldo_awal' => $saldoUtang + $total
                    ]);
                }
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
                    // Tambah stok bahan
                    $bahanModel->update($bahan['id'], [
                        'stok' => $bahan['stok'] + $d['jumlah']
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

        $satuan = strtolower($this->request->getPost('satuan'));
        $stok = $this->request->getPost('stok');
        // Konversi stok sesuai satuan
        if ($satuan === 'kg') {
            $stok = $stok * 1000; // simpan dalam gram
        } elseif ($satuan === 'liter') {
            $stok = $stok * 1000; // simpan dalam ml
        } // pcs dan meter tetap

        $bahanModel->save([
            'kode' => $this->request->getPost('kode'),
            'nama' => $this->request->getPost('nama'),
            'kategori' => $this->request->getPost('kategori'),
            'jenis' => $this->request->getPost('jenis'),
            'stok' => $stok,
            'satuan' => $this->request->getPost('satuan'),
            'harga_satuan' => $this->request->getPost('harga_satuan'),
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
        $kode       = $this->request->getPost('kode');

        // Cek apakah kode sudah digunakan oleh bahan lain
        if ($bahanModel->where('kode', $kode)->where('id !=', $id)->first()) {
            return redirect()->back()->withInput()->with('error', 'Kode sudah digunakan oleh bahan lain.');
        }

        $satuan = strtolower($this->request->getPost('satuan'));
        $stok = $this->request->getPost('stok');
        // Konversi stok sesuai satuan
        if ($satuan === 'kg') {
            $stok = $stok * 1000;
        } elseif ($satuan === 'liter') {
            $stok = $stok * 1000;
        } // pcs dan meter tetap

        $bahanModel->update($id, [
            'kode' => $this->request->getPost('kode'),
            'nama' => $this->request->getPost('nama'),
            'kategori' => $this->request->getPost('kategori'),
            'jenis' => $this->request->getPost('jenis'),
            'stok' => $stok,
            'satuan' => $this->request->getPost('satuan'),
            'harga_satuan' => $this->request->getPost('harga_satuan'),
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

        $produksi = $produksiModel->find($id);
        if (!$produksi) {
            return redirect()->back()->with('error', 'Data produksi tidak ditemukan.');
        }


        // Jika status draft -> proses: kurangi stok bahan
        if ($status == 'proses' && $produksi['status'] == 'draft') {
            $details = $detailModel->where('produksi_id', $id)->where('kategori', 'baku')->findAll();
            foreach ($details as $d) {
                $bahan = $bahanModel->find($d['bahan_id']);
                if ($bahan && $bahan['stok'] >= $d['jumlah']) {
                    $bahanModel->update($d['bahan_id'], [
                        'stok' => $bahan['stok'] - $d['jumlah']
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
                } elseif ($tipe === 'bahan') {
                    $bahan = $bahanModel->find($barangId);
                    if (!$bahan || $bahan['stok'] < $jumlah) {
                        $db->transRollback();
                        return redirect()->back()->with('error', 'Stok bahan tidak cukup.');
                    }
                    $namaBarang = $bahan['nama'];
                    $bahanModel->update($barangId, [
                        'stok' => $bahan['stok'] - $jumlah
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
    // ========== LAPORAN ==========
    public function laporanIndex()
    {
        if (!in_groups('produksi')) {
            return redirect()->to('login');
        }
        $data['tittle'] = 'Laporan Produksi';
        return view('produksi/laporan/index', $data);
    }

    public function cetakPembelian()
    {
        // TODO: Implementasi cetak laporan pembelian
        $tittle = 'Laporan Pembelian';
        return view('produksi/laporan/cetak_pembelian', compact('tittle'));
    }

    public function cetakProduksi()
    {
        // TODO: Implementasi cetak laporan produksi
        $tittle = 'Laporan Produksi';
        return view('produksi/laporan/cetak_produksi', compact('tittle'));
    }

    public function cetakPersediaanBahan()
    {
        // TODO: Implementasi cetak laporan persediaan bahan
        $tittle = 'Laporan Persediaan Bahan';
        return view('produksi/laporan/cetak_persediaan_bahan', compact('tittle'));
    }

    public function cetakPersediaanBSJ()
    {
        // TODO: Implementasi cetak laporan persediaan BSJ
        $tittle = 'Laporan Persediaan BSJ';
        return view('produksi/laporan/cetak_persediaan_bsj', compact('tittle'));
    }

    public function cetakPengiriman()
    {
        // TODO: Implementasi cetak laporan pengiriman
        $tittle = 'Laporan Pengiriman';
        return view('produksi/laporan/cetak_pengiriman', compact('tittle'));
    }
}
