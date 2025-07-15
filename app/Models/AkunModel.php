<?php

namespace App\Models;

use CodeIgniter\Model;

class AkunModel extends Model
{
    protected $table = 'akun';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_akun',
        'nama_akun',
        'jenis_akun',
        'tipe',
        'saldo_awal',
        'kas_outlet_id',
    ];

    // ✅ Ambil semua akun dan urutkan berdasarkan kode akun (secara numerik)
    public function getAllOrdered()
    {
        return $this->orderBy('kode_akun', 'ASC');
    }

    // ✅ Ambil daftar akun pendapatan (untuk tampilan detail)
    public function getPendapatan()
    {
        return $this->select('nama_akun, saldo_awal as jumlah')
            ->where("kode_akun BETWEEN '400' AND '499'")
            ->orderBy('CAST(kode_akun AS UNSIGNED)', 'ASC')
            ->findAll();
    }

    public function getPendapatanByPeriode($bulan, $tahun)
    {
        return $this->db->table('jurnal_umum')
            ->select('akun.nama_akun, SUM(jurnal_umum.kredit - jurnal_umum.debit) AS jumlah')
            ->join('akun', 'akun.id = jurnal_umum.akun_id')
            ->where('akun.jenis_akun', 'Pendapatan')
            ->where('MONTH(jurnal_umum.tanggal)', $bulan)
            ->where('YEAR(jurnal_umum.tanggal)', $tahun)
            ->groupBy('akun.id')
            ->get()->getResultArray();
    }

    public function getBebanByPeriode($bulan, $tahun)
    {
        return $this->db->table('jurnal_umum')
            ->select('akun.nama_akun, SUM(jurnal_umum.debit - jurnal_umum.kredit) AS jumlah')
            ->join('akun', 'akun.id = jurnal_umum.akun_id')
            ->where('akun.jenis_akun', 'beban')
            ->where('MONTH(jurnal_umum.tanggal)', $bulan)
            ->where('YEAR(jurnal_umum.tanggal)', $tahun)
            ->groupBy('akun.id')
            ->get()->getResultArray();
    }

    public function getTotalPendapatanByPeriode($bulan, $tahun)
    {
        return $this->db->table('jurnal_umum')
            ->select('SUM(jurnal_umum.kredit - jurnal_umum.debit) AS total')
            ->join('akun', 'akun.id = jurnal_umum.akun_id')
            ->where('akun.jenis_akun', 'Pendapatan')
            ->where('MONTH(jurnal_umum.tanggal)', $bulan)
            ->where('YEAR(jurnal_umum.tanggal)', $tahun)
            ->get()->getRow()->total ?? 0;
    }

    public function getTotalBebanByPeriode($bulan, $tahun)
    {
        return $this->db->table('jurnal_umum')
            ->select('SUM(jurnal_umum.debit - jurnal_umum.kredit) AS total')
            ->join('akun', 'akun.id = jurnal_umum.akun_id')
            ->where('akun.jenis_akun', 'beban')
            ->where('MONTH(jurnal_umum.tanggal)', $bulan)
            ->where('YEAR(jurnal_umum.tanggal)', $tahun)
            ->get()->getRow()->total ?? 0;
    }

    public function getPriveByPeriode($bulan, $tahun)
    {
        return $this->db->table('jurnal_umum')
            ->selectSum('debit')
            ->join('akun', 'akun.id = jurnal_umum.akun_id')
            ->where('akun.nama_akun', 'Prive')
            ->where('MONTH(tanggal)', $bulan)
            ->where('YEAR(tanggal)', $tahun)
            ->get()->getRow('debit') ?? 0;
    }

    // ✅ Total saldo semua akun pendapatan (untuk perhitungan laba)
    public function getTotalPendapatan()
    {
        return $this->selectSum('saldo_awal')
            ->where('kode_akun >=', 400)
            ->where('kode_akun <', 500)
            ->first()['saldo_awal'] ?? 0;
    }

    // ✅ Ambil daftar akun beban (untuk tampilan detail)
    public function getBeban()
    {
        return $this->select('nama_akun, saldo_awal as jumlah')
            ->where("kode_akun BETWEEN '500' AND '599'")
            ->orderBy('CAST(kode_akun AS UNSIGNED)', 'ASC')
            ->findAll();
    }

    // ✅ Total saldo semua akun beban (untuk perhitungan laba)
    public function getTotalBeban()
    {
        return $this->selectSum('saldo_awal')
            ->where('kode_akun >=', 500)
            ->where('kode_akun <', 600)
            ->first()['saldo_awal'] ?? 0;
    }

    // ✅ Modal awal: ambil saldo dari akun dengan kode 3xx (modal)
    public function getModalAwal()
    {
        return $this->selectSum('saldo_awal')
            ->where("kode_akun BETWEEN '300' AND '399'")
            ->first()['saldo_awal'] ?? 0;
    }

    // ✅ Total prive: cari akun yang mengandung nama "prive"
    public function getPrive()
    {
        return $this->selectSum('saldo_awal')
            ->like('LOWER(nama_akun)', 'prive')
            ->first()['saldo_awal'] ?? 0;
    }

    public function getPendapatanRange($awal, $akhir)
    {
        return $this->select('nama_akun, SUM(kredit - debit) as jumlah')
            ->join('jurnal_umum', 'akun.id = jurnal_umum.akun_id')
            ->where('jenis_akun', 'Pendapatan')
            ->where('tanggal >=', $awal)
            ->where('tanggal <=', $akhir)
            ->groupBy('akun.id')
            ->findAll();
    }

    public function getBebanRange($awal, $akhir)
    {
        return $this->select('nama_akun, SUM(debit - kredit) as jumlah')
            ->join('jurnal_umum', 'akun.id = jurnal_umum.akun_id')
            ->where('jenis_akun', 'Beban')
            ->where('tanggal >=', $awal)
            ->where('tanggal <=', $akhir)
            ->groupBy('akun.id')
            ->findAll();
    }


    private function getByJenisRange($jenis, $awal, $akhir)
    {
        $db = \Config\Database::connect();
        $akunList = $this->where('jenis_akun', $jenis)->findAll();
        $result = [];

        foreach ($akunList as $akun) {
            $mutasi = $db->table('jurnal_umum')
                ->selectSum('debit', 'debit')
                ->selectSum('kredit', 'kredit')
                ->where('akun_id', $akun['id'])
                ->where('tanggal >=', $awal)
                ->where('tanggal <=', $akhir)
                ->get()->getRowArray();

            $debit = $mutasi['debit'] ?? 0;
            $kredit = $mutasi['kredit'] ?? 0;
            $saldo = ($akun['tipe'] === 'debit') ? $debit - $kredit : $kredit - $debit;

            if ($saldo == 0) continue;

            $result[] = [
                'nama_akun' => $akun['nama_akun'],
                'jumlah' => $saldo
            ];
        }

        return $result;
    }

    public function getTotalPendapatanRange($awal, $akhir)
    {
        return $this->db->table('jurnal_umum')
            ->selectSum('kredit')
            ->join('akun', 'akun.id = jurnal_umum.akun_id')
            ->where('akun.jenis_akun', 'Pendapatan')
            ->where('tanggal >=', $awal)
            ->where('tanggal <=', $akhir)
            ->get()->getRow()->kredit ?? 0;
    }

    public function getTotalBebanRange($awal, $akhir)
    {
        return $this->db->table('jurnal_umum')
            ->selectSum('debit')
            ->join('akun', 'akun.id = jurnal_umum.akun_id')
            ->where('akun.jenis_akun', 'Beban')
            ->where('tanggal >=', $awal)
            ->where('tanggal <=', $akhir)
            ->get()->getRow()->debit ?? 0;
    }

    public function getPriveRange($awal, $akhir)
    {
        return $this->db->table('jurnal_umum')
            ->selectSum('debit')
            ->join('akun', 'akun.id = jurnal_umum.akun_id')
            ->where('akun.nama_akun', 'Prive')
            ->where('tanggal >=', $awal)
            ->where('tanggal <=', $akhir)
            ->get()->getRow()->debit ?? 0;
    }

    public function getModalAkhirSebelumPeriode($awalPeriode)
    {
        // Ambil tanggal akhir sebelum periode ini
        $tanggalAkhir = date('Y-m-d', strtotime($awalPeriode . ' -1 day'));

        if (!$tanggalAkhir || $tanggalAkhir < '2000-01-01') {
            return 0;
        }

        // Ambil saldo awal dari akun Modal
        $modalAwal = $this->where('nama_akun', 'Modal')->first()['saldo_awal'] ?? 0;

        // Hitung pendapatan, beban, prive dari awal tahun hingga sebelum periode ini
        $awalTahun = date('Y', strtotime($tanggalAkhir)) . '-01-01';

        $totalPendapatan = $this->getTotalPendapatanRange($awalTahun, $tanggalAkhir);
        $totalBeban = $this->getTotalBebanRange($awalTahun, $tanggalAkhir);
        $totalPrive = $this->getPriveRange($awalTahun, $tanggalAkhir);

        // Modal akhir = modal awal + laba - prive
        $modalAkhir = $modalAwal + ($totalPendapatan - $totalBeban) - $totalPrive;

        return $modalAkhir;
    }


    // app/Models/AkunModel.php

    public function getSaldoAkunNeracaGabungan()
    {
        return $this->select('akun.id, akun.nama_akun, akun.jenis_akun, akun.tipe, akun.saldo_awal')
            ->selectSum('jurnal_umum.debit', 'total_debit')
            ->selectSum('jurnal_umum.kredit', 'total_kredit')
            ->join('jurnal_umum', 'jurnal_umum.akun_id = akun.id', 'left')
            ->groupBy('akun.id')
            ->orderBy('CAST(kode_akun AS UNSIGNED)', 'ASC')
            ->findAll();
    }

    public function getSaldoAkunBulanIni()
    {
        $builder = $this->db->table('akun');
        $builder->select('akun.id, akun.kode_akun, akun.nama_akun, akun.jenis_akun, akun.tipe, 
                      SUM(jurnal_umum.debit) as total_debit, 
                      SUM(jurnal_umum.kredit) as total_kredit');
        $builder->join('jurnal_umum', 'jurnal_umum.akun_id = akun.id', 'left');
        $builder->where('MONTH(jurnal_umum.tanggal)', date('m'));
        $builder->where('YEAR(jurnal_umum.tanggal)', date('Y'));
        $builder->groupBy('akun.id');

        return $builder->get()->getResultArray();
    }

    public function getSaldoAkunSampaiBulan($akunId, $bulan, $tahun)
    {
        $builder = $this->db->table('jurnal_umum');
        $builder->selectSum('debit', 'total_debit');
        $builder->selectSum('kredit', 'total_kredit');
        $builder->where('akun_id', $akunId);
        $builder->where("tanggal <", date('Y-m-01', strtotime("$tahun-$bulan-01")));
        $result = $builder->get()->getRowArray();

        $akun = $this->find($akunId);
        $saldoAwal = $akun['saldo_awal'] ?? 0;
        $tipe = $akun['tipe'];

        return ($tipe == 'debit')
            ? $saldoAwal + ($result['total_debit'] ?? 0) - ($result['total_kredit'] ?? 0)
            : $saldoAwal - ($result['total_debit'] ?? 0) + ($result['total_kredit'] ?? 0);
    }

    public function getModalAkhirBulanSebelumnya($bulan, $tahun)
    {
        // Ambil bulan dan tahun sebelumnya
        $bulanSebelumnya = $bulan - 1;
        $tahunSebelumnya = $tahun;
        if ($bulanSebelumnya < 1) {
            $bulanSebelumnya = 12;
            $tahunSebelumnya--;
        }

        $modalAwal   = $this->getModalAwal();
        $pendapatan  = $this->getTotalPendapatanByPeriode($bulanSebelumnya, $tahunSebelumnya);
        $beban       = $this->getTotalBebanByPeriode($bulanSebelumnya, $tahunSebelumnya);
        $labaBersih  = $pendapatan - $beban;
        $prive       = $this->getPriveByPeriode($bulanSebelumnya, $tahunSebelumnya);

        return $modalAwal + $labaBersih - $prive;
    }

    public function getSaldoAkunSampaiTanggal($akunId, $tanggal)
    {
        $builder = $this->db->table('jurnal_umum');
        $builder->selectSum('debit', 'total_debit');
        $builder->selectSum('kredit', 'total_kredit');
        $builder->where('akun_id', $akunId);
        $builder->where('tanggal <=', $tanggal);

        $result = $builder->get()->getRowArray();
        $debit  = $result['total_debit'] ?? 0;
        $kredit = $result['total_kredit'] ?? 0;

        $akun = $this->find($akunId);
        $saldo_awal = $akun['saldo_awal'] ?? 0;

        return ($akun['tipe'] === 'debit')
            ? $saldo_awal + $debit - $kredit
            : $saldo_awal - $debit + $kredit;
    }

    public function getMutasiAkunRange($akunId, $tanggalAwal, $tanggalAkhir)
    {
        $builder = $this->db->table('jurnal_umum');
        $builder->selectSum('debit', 'total_debit');
        $builder->selectSum('kredit', 'total_kredit');
        $builder->where('akun_id', $akunId);
        $builder->where('tanggal >=', $tanggalAwal);
        $builder->where('tanggal <=', $tanggalAkhir);

        return $builder->get()->getRowArray();
    }


    public function getSaldoAkunRange($akunId, $tanggalAwal, $tanggalAkhir)
    {
        $builder = $this->db->table('jurnal_umum')
            ->select('SUM(debit) as total_debit, SUM(kredit) as total_kredit')
            ->where('akun_id', $akunId);

        if (!empty($tanggalAwal)) {
            $builder->where('tanggal >=', $tanggalAwal);
        }

        if (!empty($tanggalAkhir)) {
            $builder->where('tanggal <=', $tanggalAkhir);
        }

        return $builder->get()->getRowArray();
    }
}
