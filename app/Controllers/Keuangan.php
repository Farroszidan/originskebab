<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use config\Database;
use App\Models\AkunModel;
use App\Models\NeracaSaldoModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class Keuangan extends BaseController
{

    public function isiKas()
    {
        $db = \Config\Database::connect();
        $outlet = $db->table('outlet')->get()->getResult();

        $data = [
            'tittle' => 'Isi Kas Outlet',
            'kas_outlet' => $outlet // <-- tetap pakai key 'kas_outlet' agar tidak perlu ubah view
        ];

        return view('keuangan/isi_kas', $data);
    }

    public function simpanIsiKas()
    {
        $outlet_id = $this->request->getPost('kas_outlet_id');
        $jumlah = $this->request->getPost('jumlah');

        $db = \Config\Database::connect();

        // Ambil akun kas utama (kode 101)
        $akunKasUtama = $db->table('akun')->where('kode_akun', '101')->get()->getRow();

        // Ambil akun kas outlet berdasarkan outlet_id
        $akunKasOutlet = $db->table('akun')->where('kas_outlet_id', $outlet_id)->get()->getRow();

        if (!$akunKasUtama || !$akunKasOutlet) {
            return redirect()->back()->with('error', 'Akun kas tidak ditemukan.');
        }

        // Simpan ke jurnal umum sebagai transaksi double-entry
        $db->table('jurnal_umum')->insertBatch([
            [
                'tanggal' => date('Y-m-d'),
                'keterangan' => 'Isi Kas Outlet',
                'akun_id' => $akunKasOutlet->id,
                'debit' => $jumlah,
                'kredit' => 0,
            ],
            [
                'tanggal' => date('Y-m-d'),
                'keterangan' => 'Isi Kas Outlet', // <- disamakan
                'akun_id' => $akunKasUtama->id,
                'debit' => 0,
                'kredit' => $jumlah,
            ]
        ]);

        // 3. Update saldo akun
        $db->query("UPDATE akun SET saldo = saldo + ? WHERE kode_akun = ?", [$jumlah, $akunKasOutlet->kode_akun]);
        $db->query("UPDATE akun SET saldo = saldo - ? WHERE kode_akun = ?", [$jumlah, $akunKasUtama->kode_akun]);

        $db->transComplete(); // Selesai transaksi

        return redirect()->to('/dashboard')->with('success', 'Kas outlet berhasil diisi.');
    }


    public function index_jurnal()
    {
        if (!in_groups('keuangan')) {
            return redirect()->to('login');
        }

        $start_date = $this->request->getGet('start_date');
        $end_date = $this->request->getGet('end_date');

        $db = \Config\Database::connect();
        $builder = $db->table('jurnal_umum');
        $builder->select('jurnal_umum.*, akun.nama_akun');
        $builder->join('akun', 'akun.id = jurnal_umum.akun_id', 'left');

        // Filter berdasarkan tanggal jika ada
        if ($start_date && $end_date) {
            $builder->where('tanggal >=', $start_date);
            $builder->where('tanggal <=', $end_date);
        }

        $builder->orderBy('tanggal', 'DESC');
        $result = $builder->get()->getResultArray();

        // Kelompokkan berdasarkan (tanggal + keterangan)
        $jurnal_grouped = [];
        foreach ($result as $row) {
            $key = $row['tanggal'] . '|' . $row['keterangan'];
            if (!isset($jurnal_grouped[$key])) {
                $jurnal_grouped[$key] = [
                    'tanggal' => $row['tanggal'],
                    'keterangan' => $row['keterangan'],
                    'detail' => []
                ];
            }
            $jurnal_grouped[$key]['detail'][] = [
                'nama_akun' => $row['nama_akun'],
                'debit' => $row['debit'],
                'kredit' => $row['kredit']
            ];
        }

        $data = [
            'tittle' => 'SIOK | Jurnal Umum',
            'jurnal' => $jurnal_grouped
        ];

        return view('keuangan/index_jurnal', $data);
    }

    public function create_jurnal()
    {
        if (!in_groups('keuangan')) {
            return redirect()->to('login');
        }

        // Ambil semua akun dari tabel akun
        $db = \Config\Database::connect();
        $akun = $db->table('akun')->get()->getResultArray();
        $suppliers = $db->table('pemasok')->get()->getResultArray();

        $data = [
            'tittle' => 'SIOK | Jurnal Umum',
            'akun' => $akun,
            'suppliers' => $suppliers,
        ];
        return view('keuangan/create_jurnal', $data);
    }

    public function simpan_jurnal()
    {
        if (!in_groups('keuangan')) {
            return redirect()->to('login');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('jurnal_umum');
        $akunBuilder = $db->table('akun');

        $tanggal = $this->request->getPost('tanggal');
        $akun_debit = $this->request->getPost('akun_debit');
        $akun_kredit = $this->request->getPost('akun_kredit');
        $nominal = (float) $this->request->getPost('nominal');
        $keterangan = $this->request->getPost('keterangan');
        $supplier_id = $this->request->getPost('supplier_id');

        // Simpan ke jurnal umum
        $data = [
            [
                'tanggal' => $tanggal,
                'akun_id' => $akun_debit,
                'debit' => $nominal,
                'kredit' => 0,
                'keterangan' => $keterangan,
                'supplier_id' => null,
            ],
            [
                'tanggal' => $tanggal,
                'akun_id' => $akun_kredit,
                'debit' => 0,
                'kredit' => $nominal,
                'keterangan' => $keterangan,
                'supplier_id' => $supplier_id ?: null,
            ]
        ];
        $builder->insertBatch($data);

        // Ambil data akun debit
        $akunDebit = $akunBuilder->where('id', $akun_debit)->get()->getRow();
        if ($akunDebit->tipe == 'debit') {
            $akunBuilder->set('saldo', 'saldo + ' . $nominal, false)
                ->where('id', $akun_debit)
                ->update();
        } else {
            $akunBuilder->set('saldo', 'saldo - ' . $nominal, false)
                ->where('id', $akun_debit)
                ->update();
        }

        // Ambil data akun kredit
        $akunKredit = $akunBuilder->where('id', $akun_kredit)->get()->getRow();
        if ($akunKredit->tipe == 'kredit') {
            $akunBuilder->set('saldo', 'saldo + ' . $nominal, false)
                ->where('id', $akun_kredit)
                ->update();
        } else {
            $akunBuilder->set('saldo', 'saldo - ' . $nominal, false)
                ->where('id', $akun_kredit)
                ->update();
        }

        return redirect()->to('/keuangan/index')->with('success', 'Jurnal berhasil disimpan dan saldo akun diperbarui.');
    }

    public function create_akun()
    {

        if (!in_groups('keuangan')) {
            return redirect()->to('login');
        }
        $data['tittle'] = 'SIOK | Tambah Akun';
        return view('keuangan/create_akun', $data);
    }

    public function save_akun()
    {
        $akunModel = new \App\Models\AkunModel();

        $akunModel->save([
            'kode_akun'  => $this->request->getPost('kode_akun'),
            'nama_akun'  => $this->request->getPost('nama_akun'),
            'jenis_akun' => $this->request->getPost('jenis_akun'),
            'tipe'       => $this->request->getPost('tipe'), // ✅ disesuaikan dari 'tipe_saldo' jadi 'tipe'
            'saldo' => $this->request->getPost('saldo'),
        ]);

        return redirect()->to(base_url('keuangan/create_akun'));
    }

    public function edit_akun($id)
    {
        if (!in_groups('keuangan')) {
            return redirect()->to('login');
        }

        $akunModel = new \App\Models\AkunModel();
        $data = [
            'tittle' => 'Edit Akun',
            'akun' => $akunModel->find($id),
        ];

        return view('keuangan/edit_akun', $data);
    }

    public function update_akun($id)
    {
        $akunModel = new \App\Models\AkunModel();

        $akunModel->update($id, [
            'kode_akun'  => $this->request->getPost('kode_akun'),
            'nama_akun'  => $this->request->getPost('nama_akun'),
            'jenis_akun' => $this->request->getPost('jenis_akun'),
            'tipe'       => $this->request->getPost('tipe'),
            'saldo' => $this->request->getPost('saldo'),
        ]);

        session()->setFlashdata('message', 'Akun berhasil diperbarui.');
        return redirect()->to(base_url('keuangan/akun'));
    }

    public function delete_akun($id)
    {
        $akunModel = new \App\Models\AkunModel();
        $akunModel->delete($id);

        session()->setFlashdata('message', 'Akun berhasil dihapus.');
        return redirect()->to(base_url('keuangan/akun'));
    }

    public function daftar_akun()
    {
        $akunModel = new \App\Models\AkunModel();
        $data = [
            'tittle' => 'Daftar Akun',
            'akun'  => $akunModel->orderBy('kode_akun', 'ASC')->findAll() // 🔁 tambahkan orderBy
        ];
        return view('keuangan/index_akun', $data);
    }

    public function neraca_saldo()
    {
        $db = \Config\Database::connect();

        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $builder = $db->table('akun');
        $builder->select('akun.kode_akun, akun.nama_akun, akun.tipe, akun.jenis_akun, 
                      SUM(IF(MONTH(jurnal_umum.tanggal) = ' . $bulan . ' AND YEAR(jurnal_umum.tanggal) = ' . $tahun . ', jurnal_umum.debit, 0)) as total_debit,
                      SUM(IF(MONTH(jurnal_umum.tanggal) = ' . $bulan . ' AND YEAR(jurnal_umum.tanggal) = ' . $tahun . ', jurnal_umum.kredit, 0)) as total_kredit');
        $builder->join('jurnal_umum', 'jurnal_umum.akun_id = akun.id', 'left');
        $builder->groupBy('akun.id');
        $builder->orderBy('akun.kode_akun');

        $query = $builder->get();
        $akun = $query->getResultArray();

        // Hitung saldo dan total
        $total_debet = 0;
        $total_kredit = 0;

        foreach ($akun as &$a) {
            $a['saldo'] = ($a['tipe'] === 'debit') ? ($a['total_debit'] - $a['total_kredit']) : ($a['total_kredit'] - $a['total_debit']);
            $total_debet += ($a['tipe'] === 'debit') ? $a['saldo'] : 0;
            $total_kredit += ($a['tipe'] === 'kredit') ? $a['saldo'] : 0;
        }

        $data = [
            'akun' => $akun,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'total_debet' => $total_debet,
            'total_kredit' => $total_kredit,
            'tittle' => 'Laporan Neraca Saldo'
        ];

        return view('keuangan/neraca_saldo', $data);
    }

    public function laporanUtang()
    {
        $db = \Config\Database::connect();
        $start = $this->request->getGet('start_date') ?? date('Y-m-01');
        $end = $this->request->getGet('end_date') ?? date('Y-m-t');

        $query = $db->query("
        SELECT p.nama AS nama_supplier, a.nama_akun, a.kode_akun,
            SUM(j.kredit - j.debit) AS jumlah
        FROM jurnal_umum j
        JOIN akun a ON a.id = j.akun_id
        JOIN pemasok p ON p.id = j.supplier_id
        WHERE a.jenis_akun = 'kewajiban'
        AND j.tanggal BETWEEN '$start' AND '$end'
        AND j.supplier_id IS NOT NULL
        GROUP BY p.id, a.id
        HAVING jumlah != 0
        ");

        $utang = $query->getResultArray();

        $total_utang = 0;
        foreach ($utang as $row) {
            $total_utang += $row['jumlah'];
        }

        return view('keuangan/laporan_utang', [
            'tittle' => 'Laporan Utang',
            'utang' => $utang,
            'start' => $start,
            'end' => $end,
            'total_utang' => $total_utang
        ]);
    }

    public function formPelunasanUtang($kode_akun)
    {
        $db = \Config\Database::connect();

        $akun = $db->table('akun')
            ->where('kode_akun', $kode_akun)
            ->where('jenis_akun', 'kewajiban')
            ->get()
            ->getRowArray();

        if (!$akun) {
            return redirect()->back()->with('error', 'Akun utang tidak ditemukan.');
        }

        return view('keuangan/form_pelunasan_utang', [
            'tittle' => 'Form Pelunasan Utang',
            'kode_akun' => $akun['kode_akun'],
            'nama_akun' => $akun['nama_akun']
        ]);
    }

    public function simpanPelunasanUtang()
    {
        $db = \Config\Database::connect();

        $kode_akun = $this->request->getPost('kode_akun');
        $tanggal = $this->request->getPost('tanggal');
        $nominal = $this->request->getPost('nominal');
        $keterangan = $this->request->getPost('keterangan');

        if (!$kode_akun || !$tanggal || !$nominal) {
            return redirect()->back()->with('error', 'Semua field wajib diisi.');
        }

        // Ambil data akun utang
        $akunUtang = $db->table('akun')->where('kode_akun', $kode_akun)->get()->getRowArray();
        if (!$akunUtang) {
            return redirect()->back()->with('error', 'Akun utang tidak ditemukan.');
        }

        // Akun kas (pastikan ada dan hardcode ke kode "101")
        $akunKas = $db->table('akun')->where('kode_akun', '101')->get()->getRowArray();
        if (!$akunKas) {
            return redirect()->back()->with('error', 'Akun kas (kode 101) tidak ditemukan.');
        }

        // Simpan 2 entri: kas debit dan utang kredit
        $db->table('jurnal_umum')->insertBatch([
            [
                'tanggal' => $tanggal,
                'akun_id' => $akunUtang['id'],
                'debit' => $nominal,
                'kredit' => 0,
                'keterangan' => $keterangan ?? 'Pelunasan utang',
            ],
            [
                'tanggal' => $tanggal,
                'akun_id' => $akunKas['id'],
                'debit' => 0,
                'kredit' => $nominal,
                'keterangan' => $keterangan ?? 'Pelunasan utang',
            ]
        ]);

        return redirect()->to(base_url('keuangan/laporan_utang'))->with('success', 'Pelunasan utang berhasil disimpan.');
    }

    public function export_pdf_utang()
    {
        $db = \Config\Database::connect();
        $start = $this->request->getGet('start_date') ?? date('Y-m-01');
        $end = $this->request->getGet('end_date') ?? date('Y-m-t');
        $currentTimestamp = date('d M Y, H:i') . ' WIB';


        $query = $db->query("
        SELECT
            p.nama AS nama_supplier,
            a.nama_akun,
            SUM(ju.kredit - ju.debit) AS jumlah
        FROM jurnal_umum ju
        JOIN akun a ON a.id = ju.akun_id
        JOIN pemasok p ON p.id = ju.supplier_id
        WHERE a.jenis_akun = 'kewajiban'
          AND ju.supplier_id IS NOT NULL
          AND ju.tanggal BETWEEN '$start' AND '$end'
        GROUP BY p.id, a.nama_akun
        HAVING jumlah != 0
        ORDER BY a.nama_akun
        ");

        $utang = $query->getResultArray();

        $total = 0;
        foreach ($utang as $row) {
            $total += $row['jumlah'];
        }

        $data = [
            'utang' => $utang,
            'start' => $start,
            'end' => $end,
            'total_utang' => $total,
            'timestamp' => $currentTimestamp
        ];

        $html = view('keuangan/pdf_utang', $data);

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('Laporan_Utang_Origins_Kebab.pdf', ['Attachment' => false]);
    }

    public function laporanPiutang()
    {
        $db = \Config\Database::connect();
        $start = $this->request->getGet('start_date');
        $end = $this->request->getGet('end_date');

        if (!$start || !$end) {
            $start = date('Y-m-01');
            $end = date('Y-m-t');
        }

        // Ambil dari tabel akun saja
        $kode_piutang = ['110', '111', '112', '113'];
        $piutang = $db->table('akun')
            ->select('kode_akun, nama_akun, saldo')
            ->whereIn('kode_akun', $kode_piutang)
            ->where('saldo !=', 0)
            ->orderBy('nama_akun', 'ASC')
            ->get()->getResultArray();

        $total_piutang = 0;
        foreach ($piutang as &$row) {
            $row['jumlah'] = floatval($row['saldo']);
            $total_piutang += $row['jumlah'];
        }

        return view('keuangan/laporan_piutang', [
            'tittle' => 'Laporan Piutang',
            'piutang' => $piutang,
            'start' => $start,
            'end' => $end,
            'total_piutang' => $total_piutang
        ]);
    }

    public function formPelunasanPiutang($kode_akun)
    {
        $db = \Config\Database::connect();
        $akun = $db->table('akun')->where('kode_akun', $kode_akun)->get()->getRow();

        if (!$akun) {
            return redirect()->to(base_url('keuangan/laporan_piutang'))->with('error', 'Akun piutang tidak ditemukan.');
        }

        return view('keuangan/form_pelunasan_piutang', [
            'tittle' => 'Form Pelunasan Piutang',
            'kode_akun' => $akun->kode_akun,
            'nama_akun' => $akun->nama_akun
        ]);
    }

    public function simpanPelunasanPiutang()
    {
        $db = \Config\Database::connect();
        $akunBuilder = $db->table('akun');

        $kode_akun_piutang = $this->request->getPost('kode_akun'); // kode akun piutang
        $tanggal = $this->request->getPost('tanggal');
        $nominal = (float) $this->request->getPost('nominal');
        $keterangan = $this->request->getPost('keterangan');

        if (!$kode_akun_piutang || !$tanggal || !$nominal) {
            return redirect()->back()->with('error', 'Semua field wajib diisi.');
        }

        // Ambil data akun
        $akunPiutang = $akunBuilder->where('kode_akun', $kode_akun_piutang)->get()->getRow();
        $akunKas = $akunBuilder->where('kode_akun', '101')->get()->getRow(); // akun kas

        if (!$akunPiutang || !$akunKas) {
            return redirect()->back()->with('error', 'Akun tidak ditemukan.');
        }

        // Simpan ke jurnal_umum
        $db->table('jurnal_umum')->insertBatch([
            [
                'tanggal' => $tanggal,
                'akun_id' => $akunKas->id,
                'debit' => $nominal,
                'kredit' => 0,
                'keterangan' => $keterangan,
            ],
            [
                'tanggal' => $tanggal,
                'akun_id' => $akunPiutang->id,
                'debit' => 0,
                'kredit' => $nominal,
                'keterangan' => $keterangan,
            ]
        ]);

        // Update saldo akun Kas
        if ($akunKas->tipe == 'debit') {
            $akunBuilder->set('saldo', 'saldo + ' . $nominal, false)
                ->where('id', $akunKas->id)
                ->update();
        } else {
            $akunBuilder->set('saldo', 'saldo - ' . $nominal, false)
                ->where('id', $akunKas->id)
                ->update();
        }

        // Update saldo akun Piutang
        if ($akunPiutang->tipe == 'kredit') {
            $akunBuilder->set('saldo', 'saldo + ' . $nominal, false)
                ->where('id', $akunPiutang->id)
                ->update();
        } else {
            $akunBuilder->set('saldo', 'saldo - ' . $nominal, false)
                ->where('id', $akunPiutang->id)
                ->update();
        }

        return redirect()->to(base_url('keuangan/laporan_piutang'))->with('success', 'Pelunasan piutang berhasil disimpan.');
    }

    public function export_pdf_piutang()
    {
        $db = \Config\Database::connect();
        $start = $this->request->getGet('start_date') ?? date('Y-m-01');
        $end = $this->request->getGet('end_date') ?? date('Y-m-t');
        $currentTimestamp = date('d M Y, H:i') . ' WIB';

        // Ambil data dari tabel akun, sesuai dengan view laporanPiutang()
        $kode_piutang = ['110', '111', '112', '113'];
        $piutang = $db->table('akun')
            ->select('kode_akun, nama_akun, saldo')
            ->whereIn('kode_akun', $kode_piutang)
            ->where('saldo !=', 0)
            ->orderBy('nama_akun', 'ASC')
            ->get()->getResultArray();

        $total_piutang = 0;
        foreach ($piutang as &$row) {
            $row['jumlah'] = floatval($row['saldo']);
            $total_piutang += $row['jumlah'];
        }

        $data = [
            'piutang' => $piutang,
            'start' => $start,
            'end' => $end,
            'total_piutang' => $total_piutang,
            'timestamp' => $currentTimestamp
        ];

        $html = view('keuangan/pdf_piutang', $data);

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('Laporan_Piutang_Origins_Kebab.pdf', ['Attachment' => false]);
    }

    public function laba_rugi()
    {
        if (!in_groups('keuangan')) {
            return redirect()->to('login');
        }

        $db = \Config\Database::connect();
        $akunModel = new \App\Models\AkunModel();

        $filter      = $this->request->getGet('filter') ?? 'bulan';
        $tahun       = $this->request->getGet('tahun') ?? date('Y');
        $bulan       = $this->request->getGet('bulan') ?? date('n');
        $tanggal     = $this->request->getGet('tanggal') ?? date('Y-m-d');
        $triwulan    = $this->request->getGet('triwulan') ?? 1;
        $semester    = $this->request->getGet('semester') ?? 1;
        $start_date  = $this->request->getGet('start_date') ?? '';
        $end_date    = $this->request->getGet('end_date') ?? '';

        // Default range awal-akhir
        $awal = "$tahun-01-01";
        $akhir = "$tahun-12-31";

        // Tentukan range sesuai filter
        switch ($filter) {
            case 'tanggal':
                $awal = $tanggal;
                $akhir = $tanggal;
                break;

            case 'bulan':
                $awal = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01";
                $akhir = date('Y-m-t', strtotime($awal));
                break;

            case 'triwulan':
                $bulanAwal = (($triwulan - 1) * 3) + 1;
                $awal = "$tahun-" . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT) . "-01";
                $akhir = date('Y-m-t', strtotime("+2 months", strtotime($awal)));
                break;

            case 'semester':
                $bulanAwal = $semester == 1 ? 1 : 7;
                $awal = "$tahun-" . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT) . "-01";
                $akhir = date('Y-m-t', strtotime("+5 months", strtotime($awal)));
                break;

            case 'rentang':
                if (!empty($start_date) && !empty($end_date)) {
                    $awal = $start_date;
                    $akhir = $end_date;
                }
                break;

            case 'tahun':
            default:
                $awal = "$tahun-01-01";
                $akhir = "$tahun-12-31";
                break;
        }

        // Ambil akun pendapatan & beban
        $akunList = $akunModel->whereIn('jenis_akun', ['Pendapatan', 'Beban'])->findAll();
        $pendapatan = $beban = [];
        $total_pendapatan = $total_beban = 0;

        foreach ($akunList as $akun) {
            $akunId = $akun['id'];
            $tipe   = $akun['tipe'];

            $mutasi = $db->table('jurnal_umum')
                ->selectSum('debit', 'debit')
                ->selectSum('kredit', 'kredit')
                ->where('akun_id', $akunId)
                ->where('tanggal >=', $awal)
                ->where('tanggal <=', $akhir)
                ->get()->getRowArray();

            $debit = $mutasi['debit'] ?? 0;
            $kredit = $mutasi['kredit'] ?? 0;
            $saldo = ($tipe === 'debit') ? $debit - $kredit : $kredit - $debit;

            if ($saldo == 0) continue;

            if ($akun['jenis_akun'] === 'Pendapatan') {
                $pendapatan[] = [
                    'nama_akun' => $akun['nama_akun'],
                    'jumlah' => $saldo
                ];
                $total_pendapatan += $saldo;
            } elseif ($akun['jenis_akun'] === 'Beban') {
                $beban[] = [
                    'nama_akun' => $akun['nama_akun'],
                    'jumlah' => $saldo
                ];
                $total_beban += $saldo;
            }
        }

        $data = [
            'filter' => $filter,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'triwulan' => $triwulan,
            'semester' => $semester,
            'tanggal' => $tanggal,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'tittle' => 'Laporan Laba Rugi',
            'Pendapatan' => $pendapatan,
            'Beban' => $beban,
            'laba_bersih' => $total_pendapatan - $total_beban,
        ];

        return view('keuangan/laba_rugi', $data);
    }

    public function exportLabaRugiPDF()
    {
        $akunModel = new \App\Models\AkunModel();

        $filter      = $this->request->getGet('filter') ?? 'bulan';
        $bulan       = $this->request->getGet('bulan') ?? date('n');
        $tahun       = $this->request->getGet('tahun') ?? date('Y');
        $tanggal     = $this->request->getGet('tanggal');
        $triwulan    = $this->request->getGet('triwulan');
        $semester    = $this->request->getGet('semester');
        $start_date  = $this->request->getGet('start_date');
        $end_date    = $this->request->getGet('end_date');
        $currentTimestamp = date('d M Y, H:i') . ' WIB';

        // Tentukan range tanggal dan judul
        switch ($filter) {
            case 'tanggal':
                $awal = $tanggal;
                $akhir = $tanggal;
                $judulPeriode = 'Tanggal ' . date('d F Y', strtotime($tanggal));
                break;

            case 'rentang':
                if (empty($start_date) || empty($end_date)) {
                    return redirect()->back()->with('error', 'Tanggal awal dan akhir harus diisi untuk filter rentang.');
                }
                $awal = $start_date;
                $akhir = $end_date;
                $judulPeriode = 'Rentang ' . date('d M Y', strtotime($start_date)) . ' - ' . date('d M Y', strtotime($end_date));
                break;

            case 'triwulan':
                $startMonth = (($triwulan - 1) * 3) + 1;
                $endMonth = $startMonth + 2;
                $awal = "$tahun-" . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . "-01";
                $akhir = date('Y-m-t', strtotime("$tahun-" . str_pad($endMonth, 2, '0', STR_PAD_LEFT) . "-01"));
                $judulPeriode = 'Triwulan ' . $triwulan . ' (' . date('F', mktime(0, 0, 0, $startMonth)) . ' - ' . date('F', mktime(0, 0, 0, $endMonth)) . " $tahun)";
                break;

            case 'semester':
                $awal = ($semester == 1) ? "$tahun-01-01" : "$tahun-07-01";
                $akhir = ($semester == 1) ? "$tahun-06-30" : "$tahun-12-31";
                $judulPeriode = 'Semester ' . $semester . " $tahun";
                break;

            case 'tahun':
                $awal = "$tahun-01-01";
                $akhir = "$tahun-12-31";
                $judulPeriode = "Tahun $tahun";
                break;

            default: // bulan
                $awal = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01";
                $akhir = date('Y-m-t', strtotime($awal));
                $judulPeriode = date('F Y', strtotime($awal));
                break;
        }

        // Ambil data dari model
        $pendapatan = $akunModel->getPendapatanRange($awal, $akhir);
        $beban      = $akunModel->getBebanRange($awal, $akhir);

        $totalPendapatan = array_sum(array_column($pendapatan, 'jumlah'));
        $totalBeban      = array_sum(array_column($beban, 'jumlah'));
        $labaBersih      = $totalPendapatan - $totalBeban;

        $data = [
            'filter'         => $filter,
            'bulan'          => $bulan,
            'tahun'          => $tahun,
            'tanggal'        => $tanggal,
            'triwulan'       => $triwulan,
            'semester'       => $semester,
            'start_date'     => $start_date,
            'end_date'       => $end_date,
            'tittle'         => 'Laporan Laba Rugi',
            'judulPeriode'   => $judulPeriode,
            'pendapatan'     => $pendapatan,
            'beban'          => $beban,
            'totalPendapatan' => $totalPendapatan,
            'totalBeban'     => $totalBeban,
            'labaBersih'     => $labaBersih,
            'timestamp' => $currentTimestamp
        ];

        $html = view('keuangan/laba_rugi_pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("Laporan_Laba_Rugi.pdf", ["Attachment" => true]);
    }

    public function laporanPerubahanEkuitas()
    {
        helper(['form', 'url']);
        $akunModel = new AkunModel();

        // Ambil filter dari request
        $filter     = $this->request->getGet('filter') ?? 'bulan';
        $bulan      = $this->request->getGet('bulan') ?? date('m');
        $tahun      = $this->request->getGet('tahun') ?? date('Y');
        $triwulan   = $this->request->getGet('triwulan') ?? 1;
        $semester   = $this->request->getGet('semester') ?? 1;
        $start_date = null;
        $end_date   = null;

        // Tentukan range tanggal berdasarkan filter
        switch ($filter) {
            case 'rentang':
                $start_date = $this->request->getGet('start_date');
                $end_date   = $this->request->getGet('end_date');
                break;

            case 'bulan':
                $start_date = date('Y-m-01', strtotime("$tahun-$bulan-01"));
                $end_date   = date('Y-m-t', strtotime($start_date));
                break;

            case 'triwulan':
                if ($triwulan == 1) {
                    $start_date = "$tahun-01-01";
                    $end_date   = "$tahun-03-31";
                } elseif ($triwulan == 2) {
                    $start_date = "$tahun-04-01";
                    $end_date   = "$tahun-06-30";
                } elseif ($triwulan == 3) {
                    $start_date = "$tahun-07-01";
                    $end_date   = "$tahun-09-30";
                } elseif ($triwulan == 4) {
                    $start_date = "$tahun-10-01";
                    $end_date   = "$tahun-12-31";
                }
                break;

            case 'semester':
                if ($semester == 1) {
                    $start_date = "$tahun-01-01";
                    $end_date   = "$tahun-06-30";
                } else {
                    $start_date = "$tahun-07-01";
                    $end_date   = "$tahun-12-31";
                }
                break;

            case 'tahun':
                $start_date = "$tahun-01-01";
                $end_date   = "$tahun-12-31";
                break;

            default:
                $start_date = date('Y-m-01');
                $end_date   = date('Y-m-t');
                break;
        }

        // Pastikan semua variabel ada meskipun tidak dipakai di view
        $modal_awal      = $akunModel->getModalAkhirSebelumPeriode($start_date);
        $tambahan_modal  = $akunModel->getTambahanModalRange($start_date, $end_date);
        $laba_bersih     = $akunModel->getTotalPendapatanRange($start_date, $end_date)
            - $akunModel->getTotalBebanRange($start_date, $end_date);
        $prive           = $akunModel->getPriveRange($start_date, $end_date);
        $modal_akhir     = $modal_awal + $tambahan_modal + $laba_bersih - $prive;

        return view('keuangan/laporan_perubahan_ekuitas', [
            'tittle' => 'Laporan Perubahan Ekuitas',
            'filter'          => $filter,
            'bulan'           => $bulan,
            'tahun'           => $tahun,
            'triwulan'        => $triwulan,
            'semester'        => $semester,
            'start_date'      => $start_date,
            'end_date'        => $end_date,
            'modal_awal'      => $modal_awal,
            'tambahan_modal'  => $tambahan_modal,
            'laba_bersih'     => $laba_bersih,
            'prive'           => $prive,
            'modal_akhir'     => $modal_akhir
        ]);
    }

    public function exportPerubahanEkuitasPDF()
    {
        $akunModel = new \App\Models\AkunModel();

        $filter     = $this->request->getGet('filter') ?? 'bulan';
        $bulan      = $this->request->getGet('bulan') ?? date('n');
        $tahun      = $this->request->getGet('tahun') ?? date('Y');
        $triwulan   = $this->request->getGet('triwulan');
        $semester   = $this->request->getGet('semester');
        $start_date = $this->request->getGet('start_date');
        $end_date   = $this->request->getGet('end_date');

        // Tentukan awal & akhir periode + judulnya
        switch ($filter) {
            case 'tanggal':
                $awal = $start_date;
                $akhir = $start_date;
                $judulPeriode = 'Tanggal ' . date('d M Y', strtotime($awal));
                break;

            case 'rentang':
                $awal = $start_date;
                $akhir = $end_date;
                $judulPeriode = 'Rentang ' . date('d M Y', strtotime($awal)) . ' - ' . date('d M Y', strtotime($akhir));
                break;

            case 'triwulan':
                $startMonth = (($triwulan - 1) * 3) + 1;
                $endMonth = $startMonth + 2;
                $awal = "$tahun-" . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . "-01";
                $akhir = date('Y-m-t', strtotime("$tahun-" . str_pad($endMonth, 2, '0', STR_PAD_LEFT) . "-01"));
                $judulPeriode = "Triwulan $triwulan (" . date('F', mktime(0, 0, 0, $startMonth)) . " - " . date('F', mktime(0, 0, 0, $endMonth)) . " $tahun)";
                break;

            case 'semester':
                $awal = ($semester == 1) ? "$tahun-01-01" : "$tahun-07-01";
                $akhir = ($semester == 1) ? "$tahun-06-30" : "$tahun-12-31";
                $judulPeriode = ($semester == 1) ? "Semester 1 (Jan - Jun) $tahun" : "Semester 2 (Jul - Des) $tahun";
                break;

            case 'tahun':
                $awal = "$tahun-01-01";
                $akhir = "$tahun-12-31";
                $judulPeriode = "Tahun $tahun";
                break;

            default: // bulan
                $awal = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01";
                $akhir = date('Y-m-t', strtotime($awal));
                $judulPeriode = date('F Y', strtotime($awal));
                break;
        }

        // 🔴 Ini bagian penting yang belum ada sebelumnya
        $modalAwal      = $akunModel->getModalAkhirSebelumPeriode($awal);
        $tambahanModal  = $akunModel->getTambahanModalRange($awal, $akhir); // ✅ DITAMBAHKAN
        $pendapatan     = $akunModel->getTotalPendapatanRange($awal, $akhir);
        $beban          = $akunModel->getTotalBebanRange($awal, $akhir);
        $labaBersih     = $pendapatan - $beban;
        $prive          = $akunModel->getPriveRange($awal, $akhir);
        $modalAkhir     = $modalAwal + $tambahanModal + $labaBersih - $prive;

        // Waktu cetak
        date_default_timezone_set('Asia/Jakarta');
        $timestamp = date('d-m-Y H:i:s') . ' WIB';

        $data = [
            'judulPeriode'   => $judulPeriode,
            'modalAwal'      => $modalAwal,
            'tambahanModal'  => $tambahanModal, // ✅ DITAMBAHKAN
            'labaBersih'     => $labaBersih,
            'prive'          => $prive,
            'modalAkhir'     => $modalAkhir,
            'timestamp'      => $timestamp,
            'filter'         => $filter,
            'bulan'          => $bulan,
            'tahun'          => $tahun,
            'triwulan'       => $triwulan,
            'semester'       => $semester,
            'start_date'     => $start_date,
            'end_date'       => $end_date
        ];

        $html = view('keuangan/perubahan_ekuitas_pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Laporan_Perubahan_Ekuitas.pdf", ["Attachment" => true]);
    }

    public function laporanNeraca()
    {
        $akunModel = new \App\Models\AkunModel();

        // Filter waktu tetap dipertahankan agar bisa ditampilkan di view
        $filter = $this->request->getGet('filter') ?? 'bulan';
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $triwulan = $this->request->getGet('triwulan') ?? 1;
        $semester = $this->request->getGet('semester') ?? 1;
        $start_date = $this->request->getGet('start_date');
        $end_date = $this->request->getGet('end_date');

        // Penentuan judul periode
        // Penentuan judul periode
        switch ($filter) {
            case 'triwulan':
                $bulanAwal = (($triwulan - 1) * 3) + 1;
                $awal = "$tahun-" . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT) . "-01";
                $akhirBulan = $bulanAwal + 2;
                $end_date = date('Y-m-t', strtotime("$tahun-" . str_pad($akhirBulan, 2, '0', STR_PAD_LEFT) . "-01"));
                $judulPeriode = "Triwulan $triwulan $tahun";
                break;

            case 'semester':
                $bulanAwal = $semester == 1 ? 1 : 7;
                $awal = "$tahun-" . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT) . "-01";
                $akhirBulan = $semester == 1 ? 6 : 12;
                $end_date = date('Y-m-t', strtotime("$tahun-" . str_pad($akhirBulan, 2, '0', STR_PAD_LEFT) . "-01"));
                $judulPeriode = "Semester $semester $tahun";
                break;

            case 'tahun':
                $awal = "$tahun-01-01";
                $end_date = "$tahun-12-31";
                $judulPeriode = "Tahun $tahun";
                break;

            case 'rentang':
                if (!$start_date || !$end_date) {
                    return redirect()->back()->with('error', 'Tanggal awal dan akhir harus diisi.');
                }
                $awal = $start_date;
                // $end_date sudah diambil dari input
                $judulPeriode = "Periode " . date('d M Y', strtotime($start_date)) . " - " . date('d M Y', strtotime($end_date));
                break;

            default: // Bulanan
                $awal = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01";
                $end_date = date('Y-m-t', strtotime($awal));
                $judulPeriode = date('F Y', strtotime($awal));
                break;
        }


        // Tambahkan baris ini setelah switch-case
        if (!$end_date) {
            if ($filter == 'rentang') {
                // sudah aman
            } else {
                $end_date = date('Y-m-t', strtotime($awal)); // akhir bulan
            }
        }

        $akunList = $akunModel->findAll();
        $akunJurnal = $akunModel->getSaldoAkunBerdasarkanJurnal($awal, $end_date ?? date('Y-m-t'));

        $aset = $kewajiban = $ekuitas = [];
        $total_aset = $total_kewajiban = $total_ekuitas = 0;

        foreach ($akunJurnal as $akun) {
            $saldo = $akun['total_debit'] - $akun['total_kredit'];
            if (strtolower($akun['tipe']) === 'kredit') {
                $saldo = $akun['total_kredit'] - $akun['total_debit'];
            }

            // Abaikan jika saldo 0
            if ($saldo == 0) continue;

            $akunRow = [
                'keterangan' => $akun['nama_akun'],
                'jumlah' => $saldo
            ];

            switch (strtolower($akun['jenis_akun'])) {
                case 'aset':
                    $aset[] = $akunRow;
                    $total_aset += $saldo;
                    break;
                case 'kewajiban':
                    $kewajiban[] = $akunRow;
                    $total_kewajiban += $saldo;
                    break;
                case 'ekuitas':
                case 'modal':
                    if (strtolower($akun['nama_akun']) === 'prive') {
                        $ekuitas[] = ['keterangan' => $akun['nama_akun'], 'jumlah' => -$saldo];
                        $total_ekuitas -= $saldo;
                    } else {
                        $ekuitas[] = $akunRow;
                        $total_ekuitas += $saldo;
                    }
                    break;
            }
        }

        return view('keuangan/laporan_neraca', [
            'tittle' => 'Laporan Neraca',
            'filter' => $filter,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'triwulan' => $triwulan,
            'semester' => $semester,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'judulPeriode' => $judulPeriode,
            'aset' => $aset,
            'kewajiban' => $kewajiban,
            'ekuitas' => $ekuitas,
            'total_aset' => $total_aset,
            'total_kewajiban' => $total_kewajiban,
            'total_ekuitas' => $total_ekuitas
        ]);
    }

    public function exportNeracaPDF()
    {
        $akunModel = new \App\Models\AkunModel();

        $filter     = $this->request->getGet('filter') ?? 'bulan';
        $bulan      = $this->request->getGet('bulan') ?? date('n');
        $tahun      = $this->request->getGet('tahun') ?? date('Y');
        $triwulan   = $this->request->getGet('triwulan') ?? 1;
        $semester   = $this->request->getGet('semester') ?? 1;
        $start_date = $this->request->getGet('tanggal_awal');
        $end_date   = $this->request->getGet('tanggal_akhir');

        // Tentukan periode
        switch ($filter) {
            case 'triwulan':
                $bulanAwal = (($triwulan - 1) * 3) + 1;
                $tanggal_awal = "$tahun-" . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT) . "-01";
                $tanggal_akhir = date('Y-m-t', strtotime("+2 months", strtotime($tanggal_awal)));
                $judulPeriode = "Triwulan $triwulan $tahun";
                break;

            case 'semester':
                $bulanAwal = $semester == 1 ? 1 : 7;
                $tanggal_awal = "$tahun-" . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT) . "-01";
                $tanggal_akhir = date('Y-m-t', strtotime("+5 months", strtotime($tanggal_awal)));
                $judulPeriode = "Semester $semester $tahun";
                break;

            case 'tahun':
                $tanggal_awal = "$tahun-01-01";
                $tanggal_akhir = "$tahun-12-31";
                $judulPeriode = "Tahun $tahun";
                break;

            case 'rentang':
                $tanggal_awal  = $start_date;
                $tanggal_akhir = $end_date;
                $judulPeriode  = "Periode " . date('d/m/Y', strtotime($tanggal_awal)) . " s.d " . date('d/m/Y', strtotime($tanggal_akhir));
                break;

            default: // filter 'bulan'
                $tanggal_awal = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01";
                $tanggal_akhir = date('Y-m-t', strtotime($tanggal_awal));
                $judulPeriode = date('F Y', strtotime($tanggal_awal));
                break;
        }

        $akunList = $akunModel->findAll();

        $aset = $kewajiban = $ekuitas = [];
        $total_aset = $total_kewajiban = $total_ekuitas = 0;

        foreach ($akunList as $akun) {
            $akunId = $akun['id'];

            $saldoAwal = $akunModel->getSaldoAkunSampaiTanggal($akunId, date('Y-m-d', strtotime($tanggal_awal . ' -1 day')));
            $mutasi    = $akunModel->getMutasiAkunRange($akunId, $tanggal_awal, $tanggal_akhir);

            $debit  = $mutasi['total_debit'] ?? 0;
            $kredit = $mutasi['total_kredit'] ?? 0;

            $saldoAkhir = ($akun['tipe'] === 'debit')
                ? $saldoAwal + $debit - $kredit
                : $saldoAwal - $debit + $kredit;

            if ($saldoAkhir == 0) continue;

            $akunRow = ['keterangan' => $akun['nama_akun'], 'jumlah' => $saldoAkhir];

            switch (strtolower($akun['jenis_akun'])) {
                case 'aset':
                    $aset[] = $akunRow;
                    $total_aset += $saldoAkhir;
                    break;
                case 'kewajiban':
                    $kewajiban[] = $akunRow;
                    $total_kewajiban += $saldoAkhir;
                    break;
                case 'ekuitas':
                    $ekuitas[] = $akunRow;
                    $total_ekuitas += $saldoAkhir;
                    break;
            }
        }

        $data = [
            'tittle'           => 'Laporan Neraca',
            'tanggal_awal'     => $tanggal_awal,
            'tanggal_akhir'    => $tanggal_akhir,
            'judul_periode'    => $judulPeriode,
            'aset'             => $aset,
            'kewajiban'        => $kewajiban,
            'ekuitas'          => $ekuitas,
            'total_aset'       => $total_aset,
            'total_kewajiban'  => $total_kewajiban,
            'total_ekuitas'    => $total_ekuitas,
            'total_pasiva'     => $total_kewajiban + $total_ekuitas,
            'timestamp'        => date('d-m-Y H:i:s') . ' WIB'
        ];

        $html = view('keuangan/laporan_neraca_pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Laporan_Neraca_' . date('Ymd', strtotime($tanggal_akhir)) . '.pdf';
        $dompdf->stream($filename, ["Attachment" => true]);
    }

    public function laporanArusKas()
    {
        if (!in_groups('keuangan')) {
            return redirect()->to('login');
        }

        $db = \Config\Database::connect();
        $akunModel = new \App\Models\AkunModel();

        $filter = $this->request->getGet('filter') ?? 'bulan';

        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $triwulan = $this->request->getGet('triwulan') ?? 1;
        $semester = $this->request->getGet('semester') ?? 1;
        $start_date = $this->request->getGet('start_date');
        $end_date = $this->request->getGet('end_date');

        switch ($filter) {
            case 'triwulan':
                $bulanAwal = (($triwulan - 1) * 3) + 1;
                $awal = "$tahun-" . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT) . "-01";
                $akhir = date('Y-m-t', strtotime("+2 months", strtotime($awal)));
                break;

            case 'semester':
                $bulanAwal = $semester == 1 ? 1 : 7;
                $awal = "$tahun-" . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT) . "-01";
                $akhir = date('Y-m-t', strtotime("+5 months", strtotime($awal)));
                break;

            case 'tahun':
                $awal = "$tahun-01-01";
                $akhir = "$tahun-12-31";
                break;

            case 'rentang':
                if ($filter === 'rentang') {
                    if (!$start_date || !$end_date) {
                        $arusKas = ['operasi' => [], 'investasi' => [], 'pendanaan' => [], 'total' => 0];
                        return view('keuangan/arus_kas', compact(
                            'tittle',
                            'filter',
                            'bulan',
                            'tahun',
                            'triwulan',
                            'semester',
                            'start_date',
                            'end_date',
                            'arusKas'
                        ));
                    }
                }

                $awal = $start_date;
                $akhir = $end_date;
                break;

            default: // bulan
                $awal = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01";
                $akhir = date('Y-m-t', strtotime($awal));
                break;
        }


        $kategori = [
            'operasi'   => ['Pendapatan', 'Beban'],
            'investasi' => ['Aset Tetap'],
            'pendanaan' => ['Ekuitas', 'Prive']
        ];

        $arusKas = [
            'operasi' => [],
            'investasi' => [],
            'pendanaan' => [],
            'total' => 0
        ];

        $akunList = $akunModel->findAll();
        // Daftar kode akun kas/setara kas
        $akunKas = ['101', '102', '103', '104', '105', '106']; // atau berdasarkan jenis_akun == 'Kas'

        foreach ($akunList as $akun) {
            $akunId = $akun['id'];
            $kode = $akun['kode_akun'];
            $jenis = $akun['jenis_akun'];
            $tipe = $akun['tipe'];

            if (in_array($kode, $akunKas)) continue;

            $mutasi = $db->table('jurnal_umum')
                ->selectSum('debit')
                ->selectSum('kredit')
                ->where('akun_id', $akunId)
                ->where('tanggal >=', $awal)
                ->where('tanggal <=', $akhir)
                ->get()->getRowArray();

            $debit = $mutasi['debit'] ?? 0;
            $kredit = $mutasi['kredit'] ?? 0;

            /// Perhitungan arus kas
            if (in_array($jenis, $kategori['pendanaan']) || in_array($jenis, $kategori['investasi'])) {
                // Untuk pendanaan & investasi, arus kas positif jika ada penerimaan (kredit > debit)
                $netto = $kredit - $debit;
            } else {
                // Untuk operasi, sesuaikan berdasarkan tipe akun
                $netto = ($tipe === 'debit') ? ($kredit - $debit) : ($debit - $kredit);
            }

            // Lewati jika tidak ada arus
            if ($netto == 0) continue;

            // Kelompokkan berdasarkan jenis aktivitas
            if (in_array($jenis, $kategori['operasi'])) {
                $arusKas['operasi'][] = ['akun' => $akun['nama_akun'], 'jumlah' => $netto];
            } elseif (in_array($jenis, $kategori['investasi'])) {
                $arusKas['investasi'][] = ['akun' => $akun['nama_akun'], 'jumlah' => $netto];
            } elseif (in_array($jenis, $kategori['pendanaan'])) {
                $arusKas['pendanaan'][] = ['akun' => $akun['nama_akun'], 'jumlah' => $netto];
            }

            // Tambahkan ke total kas bersih
            $arusKas['total'] += $netto;
        }

        return view('keuangan/arus_kas', [
            'tittle' => 'Laporan Arus Kas',
            'filter' => $filter,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'triwulan' => $triwulan,
            'semester' => $semester,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'arusKas' => $arusKas
        ]);
    }

    public function arusKasPdf()
    {
        if (!in_groups('keuangan')) {
            return redirect()->to('login');
        }

        $db = \Config\Database::connect();
        $akunModel = new \App\Models\AkunModel();

        $filter = $this->request->getGet('filter') ?? 'bulan';
        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $triwulan = $this->request->getGet('triwulan') ?? 1;
        $semester = $this->request->getGet('semester') ?? 1;
        $start_date = $this->request->getGet('start_date');
        $end_date = $this->request->getGet('end_date');
        $currentTimestamp = date('d M Y, H:i') . ' WIB';


        switch ($filter) {
            case 'triwulan':
                $bulanAwal = (($triwulan - 1) * 3) + 1;
                $awal = "$tahun-" . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT) . "-01";
                $akhir = date('Y-m-t', strtotime("+2 months", strtotime($awal)));
                $periodeText = "Triwulan $triwulan $tahun";
                break;

            case 'semester':
                $bulanAwal = $semester == 1 ? 1 : 7;
                $awal = "$tahun-" . str_pad($bulanAwal, 2, '0', STR_PAD_LEFT) . "-01";
                $akhir = date('Y-m-t', strtotime("+5 months", strtotime($awal)));
                $periodeText = "Semester $semester $tahun";
                break;

            case 'tahun':
                $awal = "$tahun-01-01";
                $akhir = "$tahun-12-31";
                $periodeText = "Tahun $tahun";
                break;

            case 'rentang':
                if (!$start_date || !$end_date) {
                    return redirect()->back()->with('error', 'Tanggal awal dan akhir harus diisi.');
                }
                $awal = $start_date;
                $akhir = $end_date;
                $periodeText = "Periode: " . date('d M Y', strtotime($awal)) . " s.d. " . date('d M Y', strtotime($akhir));
                break;

            default: // bulan
                $awal = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01";
                $akhir = date('Y-m-t', strtotime($awal));
                $periodeText = date('F Y', strtotime($awal));
                break;
        }

        $kategori = [
            'operasi' => ['Pendapatan', 'Beban'],
            'investasi' => ['Aset Tetap'],
            'pendanaan' => ['Ekuitas', 'Prive']
        ];

        $arusKas = [
            'operasi' => [],
            'investasi' => [],
            'pendanaan' => [],
            'total' => 0
        ];

        $akunKas = ['101', '102', '103', '104', '105', '106'];
        $akunList = $akunModel->findAll();

        foreach ($akunList as $akun) {
            $akunId = $akun['id'];
            $kode = $akun['kode_akun'];
            $jenis = $akun['jenis_akun'];
            $tipe = $akun['tipe'];

            if (in_array($kode, $akunKas)) continue;

            $mutasi = $db->table('jurnal_umum')
                ->selectSum('debit')
                ->selectSum('kredit')
                ->where('akun_id', $akunId)
                ->where('tanggal >=', $awal)
                ->where('tanggal <=', $akhir)
                ->get()->getRowArray();

            $debit = $mutasi['debit'] ?? 0;
            $kredit = $mutasi['kredit'] ?? 0;

            if (in_array($jenis, $kategori['pendanaan']) || in_array($jenis, $kategori['investasi'])) {
                $netto = $kredit - $debit;
            } else {
                $netto = ($tipe === 'debit') ? ($kredit - $debit) : ($debit - $kredit);
            }

            if ($netto == 0) continue;

            if (in_array($jenis, $kategori['operasi'])) {
                $arusKas['operasi'][] = ['akun' => $akun['nama_akun'], 'jumlah' => $netto];
            } elseif (in_array($jenis, $kategori['investasi'])) {
                $arusKas['investasi'][] = ['akun' => $akun['nama_akun'], 'jumlah' => $netto];
            } elseif (in_array($jenis, $kategori['pendanaan'])) {
                $arusKas['pendanaan'][] = ['akun' => $akun['nama_akun'], 'jumlah' => $netto];
            }

            $arusKas['total'] += $netto;
        }

        // Generate PDF
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);

        $html = view('keuangan/arus_kas_pdf', [
            'tittle' => 'Laporan Arus Kas',
            'periodeText' => $periodeText,
            'arusKas' => $arusKas,
            'timestamp' => $currentTimestamp

        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Laporan_Arus_Kas.pdf", ["Attachment" => true]);
    }

    public function exportNeracaSaldoPDF()
    {
        if (!in_groups('keuangan')) {
            return redirect()->to('login');
        }

        date_default_timezone_set('Asia/Jakarta');
        $db = \Config\Database::connect();

        $bulan = $this->request->getGet('bulan') ?? date('n');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $currentTimestamp = date('d M Y, H:i') . ' WIB';

        $akun = $db->table('akun')
            ->orderBy('kode_akun', 'ASC')
            ->get()
            ->getResultArray();

        $akun_saldo = [];
        $total_debet = 0;
        $total_kredit = 0;

        $awalBulan = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01";
        $awalBulanBerikut = date('Y-m-d', strtotime('+1 month', strtotime($awalBulan)));

        $bulanSebelumnya = $bulan - 1;
        $tahunSebelumnya = $tahun;
        if ($bulanSebelumnya == 0) {
            $bulanSebelumnya = 12;
            $tahunSebelumnya--;
        }

        $akhirBulanSebelumnya = date('Y-m-t', strtotime("$tahunSebelumnya-$bulanSebelumnya-01"));

        foreach ($akun as $a) {
            // Saldo akhir hingga sebelum bulan berjalan
            $jurnalSebelum = $db->table('jurnal_umum')
                ->selectSum('debit')
                ->selectSum('kredit')
                ->where('akun_id', $a['id'])
                ->where('tanggal <', $awalBulan)
                ->get()->getRow();

            $debitSebelum = $jurnalSebelum->debit ?? 0;
            $kreditSebelum = $jurnalSebelum->kredit ?? 0;

            if ($a['tipe'] === 'debit') {
                $saldo_awal_periode = $debitSebelum - $kreditSebelum;
            } else {
                $saldo_awal_periode = $kreditSebelum - $debitSebelum;
            }

            // Mutasi bulan ini
            $jurnalBulanIni = $db->table('jurnal_umum')
                ->selectSum('debit')
                ->selectSum('kredit')
                ->where('akun_id', $a['id'])
                ->where('tanggal >=', $awalBulan)
                ->where('tanggal <', $awalBulanBerikut)
                ->get()->getRow();

            $debitBulanIni = $jurnalBulanIni->debit ?? 0;
            $kreditBulanIni = $jurnalBulanIni->kredit ?? 0;

            if ($a['tipe'] === 'debit') {
                $saldo = $saldo_awal_periode + $debitBulanIni - $kreditBulanIni;
                $total_debet += $saldo;
            } else {
                $saldo = $saldo_awal_periode - $debitBulanIni + $kreditBulanIni;
                $total_kredit += $saldo;
            }

            $akun_saldo[] = [
                'kode_akun' => $a['kode_akun'],
                'nama_akun' => $a['nama_akun'],
                'tipe'      => $a['tipe'],
                'saldo'     => $saldo
            ];
        }

        $data = [
            'tittle'         => 'Neraca Saldo',
            'akun'           => $akun_saldo,
            'total_debet'    => $total_debet,
            'total_kredit'   => $total_kredit,
            'bulan'          => $bulan,
            'tahun'          => $tahun,
            'timestamp'      => $currentTimestamp,
        ];

        $html = view('keuangan/neraca_saldo_pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $namaBulan = date('F', mktime(0, 0, 0, $bulan, 1));
        $dompdf->stream("Neraca_Saldo_{$namaBulan}_{$tahun}.pdf", ["Attachment" => true]);
    }
}
