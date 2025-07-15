<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OutletModel;
use App\Models\KasOutletModel;
use App\Models\JualModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $user = user();

        // Tentukan role
        if (in_groups('admin')) {
            $role = 'admin';
        } elseif (in_groups('penjualan')) {
            $role = 'penjualan';
        } elseif (in_groups('produksi')) {
            $role = 'produksi';
        } elseif (in_groups('keuangan')) {
            $role = 'keuangan';
        } else {
            $role = 'unknown';
        }

        // Ambil tanggal filter dari GET, default awal-akhir bulan ini
        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end') ?? date('Y-m-t');

        $data = [
            'tittle' => 'Dashboard',
            'role'   => $role,
            'start'  => $start,
            'end'    => $end,
        ];

        // Untuk role admin: lihat total penjualan semua outlet
        if ($role === 'admin') {
            $jualModel = new JualModel();
            $outletModel = new OutletModel();

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
                    'total' => $totalOutlet
                ];

                $totalSeluruhOutlet += $totalOutlet;
            }

            $data['penjualanPerOutlet'] = $penjualanPerOutlet;
            $data['totalSeluruhOutlet'] = $totalSeluruhOutlet;
        }

        // Untuk role penjualan: tampilkan total outlet yang sedang login
        if ($role === 'penjualan') {
            $outlet_id = $user->outlet_id ?? null;
            $outletModel = new OutletModel();
            $outlet = $outletModel->find($outlet_id);

            $data['outlet_id'] = $outlet_id;
            $data['nama_outlet'] = $outlet['nama_outlet'] ?? 'Outlet Tidak Diketahui';

            $jualModel = new JualModel();
            $total = $jualModel
                ->where('outlet_id', $outlet_id)
                ->where('tgl_jual >=', $start)
                ->where('tgl_jual <=', $end)
                ->selectSum('grand_total')
                ->first();

            $data['total_penjualan'] = $total['grand_total'] ?? 0;
        }

        // Untuk keuangan: tampilkan saldo kas outlet
        if ($role === 'keuangan') {
            $kasModel = new KasOutletModel();
            $data['kas_outlet'] = $kasModel->getKasWithOutlet();
        }

        return view('dashboard/index', $data);
    }
}
