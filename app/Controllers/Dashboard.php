<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OutletModel;
use App\Models\KasOutletModel;
use App\Models\JualModel;
use App\Models\AkunModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $user = user();

        if (in_groups('admin')) {
            $role = 'admin';
        } elseif (in_groups('penjualan')) {
            $role = 'penjualan';
        } elseif (in_groups('keuangan')) {
            $role = 'keuangan';
        } elseif (in_groups('produksi')) {
            $role = 'produksi';
        } else {
            $role = 'unknown';
        }

        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end') ?? date('Y-m-t');

        $data = [
            'tittle' => 'Dashboard',
            'role'   => $role,
            'start'  => $start,
            'end'    => $end,
            'kas_outlet' => []
        ];

        $jualModel = new JualModel();
        $outletModel = new OutletModel();
        $akunModel = new AkunModel();

        // Role admin
        if ($role === 'admin') {
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

                $penjualanPerOutlet[] = [
                    'nama_outlet' => $outlet['nama_outlet'],
                    'total'       => $total['grand_total'] ?? 0
                ];

                $totalSeluruhOutlet += $total['grand_total'] ?? 0;
            }

            // Ambil saldo kas (uang laci) semua outlet dari tabel akun
            $kas_outlet_admin = $akunModel
                ->select('akun.saldo_awal, akun.nama_akun, outlet.nama_outlet')
                ->join('outlet', 'outlet.id = akun.kas_outlet_id', 'left')
                ->where('akun.jenis_akun', 'Aset')
                ->where('akun.kas_outlet_id IS NOT NULL', null, false)
                ->orderBy('akun.kode_akun', 'ASC')
                ->findAll();

            $data['penjualanPerOutlet'] = $penjualanPerOutlet;
            $data['totalSeluruhOutlet'] = $totalSeluruhOutlet;
            $data['kas_outlet_admin'] = $kas_outlet_admin;
        }


        // Role penjualan
        if ($role === 'penjualan') {
            $outlet_id = $user->outlet_id ?? null;
            $outlet = $outletModel->find($outlet_id);

            $data['outlet_id'] = $outlet_id;
            $data['nama_outlet'] = $outlet['nama_outlet'] ?? 'Outlet Tidak Diketahui';

            $total = $jualModel
                ->where('outlet_id', $outlet_id)
                ->where('tgl_jual >=', $start)
                ->where('tgl_jual <=', $end)
                ->selectSum('grand_total')
                ->first();

            $kasOutlet = $akunModel
                ->select('akun.saldo_awal, akun.nama_akun, outlet.nama_outlet')
                ->join('outlet', 'outlet.id = akun.kas_outlet_id', 'left')
                ->where('akun.jenis_akun', 'Aset')
                ->where('akun.kas_outlet_id', $outlet_id)
                ->findAll();

            $data['total_penjualan'] = $total['grand_total'] ?? 0;
            $data['kas_outlet'] = $kasOutlet;
        }

        // Role keuangan
        if ($role === 'keuangan') {
            $kas_outlet = $akunModel
                ->select('akun.saldo_awal, akun.kode_akun, akun.nama_akun, outlet.nama_outlet')
                ->join('outlet', 'outlet.id = akun.kas_outlet_id', 'left')
                ->where('akun.jenis_akun', 'Aset')
                ->where('akun.kas_outlet_id IS NOT NULL', null, false)
                ->orderBy('akun.kode_akun', 'ASC')
                ->findAll();

            $data['kas_outlet'] = $kas_outlet;
        }

        // Role produksi
        if ($role === 'produksi') {
            // Asumsi: ada ProduksiModel dan BahanModel
            $produksiModel = new \App\Models\ProduksiModel();
            $bahanModel = new \App\Models\BahanModel();

            // Jumlah batch produksi hari ini
            $today = date('Y-m-d');

            $jumlah_batch = $produksiModel
                ->where('tanggal', $today)
                ->countAllResults();

            // Total produk dihasilkan hari ini (asumsi ada kolom 'jumlah' di tabel produksi)
            $produksi_hari_ini = $produksiModel
                ->where('tanggal', $today)
                ->selectSum('jumlah')
                ->first();

            // Bahan baku menipis (stok <= min_stok)
            $bahan_menipis = $bahanModel
                ->where('stok <= min_stok')
                ->findAll();

            $data['jumlah_batch'] = $jumlah_batch;
            $data['produksi_hari_ini'] = $produksi_hari_ini['jumlah'] ?? 0;
            $data['bahan_menipis'] = $bahan_menipis;
        }

        return view('dashboard/index', $data);
    }
}
