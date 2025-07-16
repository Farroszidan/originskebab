<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Laporan Harian Per Shift</h5>
            <?php if (!empty($laporan)): ?>
                <a href="javascript:window.print()" class="btn btn-sm btn-success">
                    <i class="fas fa-print"></i> Cetak
                </a>
            <?php endif; ?>
        </div>

        <div class="card-body">
            <form method="get" action="<?= base_url('manajemen-penjualan/laporanHarian') ?>" class="form-row">
                <div class="form-group col-md-3">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= esc($tanggal) ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="outlet">Outlet</label>
                    <select name="outlet_id" class="form-control">
                        <option value="">Semua Outlet</option>
                        <?php foreach ($outlets as $outlet): ?>
                            <option value="<?= $outlet['id'] ?>" <?= isset($selectedOutlet) && $selectedOutlet == $outlet['id'] ? 'selected' : '' ?>>
                                <?= esc($outlet['nama_outlet']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-2 align-self-end">
                    <button class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Lihat
                    </button>
                </div>
            </form>

            <?php if (!empty($laporan)): ?>
                <h6 class="mt-4 font-weight-bold">Rekapitulasi Penjualan - <?= date('d M Y', strtotime($tanggal)) ?></h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-sm">
                        <thead class="thead-light text-center">
                            <tr>
                                <th>Metode</th>
                                <th>Total Penjualan</th>
                                <th>Potongan (%)</th>
                                <th>Nominal Potongan</th>
                                <th>Net Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $metode = [
                                'qris' => ['label' => 'QRIS', 'persen' => 0.007],
                                'grabfood' => ['label' => 'GrabFood', 'persen' => 0.18],
                                'gofood' => ['label' => 'GoFood', 'persen' => 0.20],
                                'shopeefood' => ['label' => 'ShopeeFood', 'persen' => 0.20],
                            ];
                            $totalAll = 0;
                            $totalPotongan = 0;
                            ?>
                            <?php foreach ($jenisCashless as $jenis => $total): ?>
                                <?php
                                $label = $metode[$jenis]['label'] ?? ucfirst($jenis);
                                $persen = $metode[$jenis]['persen'] ?? 0;
                                $potongan = $total * $persen;
                                $net = $total - $potongan;
                                $totalAll += $total;
                                $totalPotongan += $potongan;
                                ?>
                                <tr>
                                    <td class="text-capitalize"><?= $label ?></td>
                                    <td class="text-right">Rp <?= number_format($total, 0, ',', '.') ?></td>
                                    <td class="text-center"><?= number_format($persen * 100, 1) ?>%</td>
                                    <td class="text-right text-danger">Rp <?= number_format($potongan, 0, ',', '.') ?></td>
                                    <td class="text-right text-success">Rp <?= number_format($net, 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="font-weight-bold bg-light">
                                <td>Total</td>
                                <td class="text-right">Rp <?= number_format($totalAll, 0, ',', '.') ?></td>
                                <td class="text-center">-</td>
                                <td class="text-right text-danger">Rp <?= number_format($totalPotongan, 0, ',', '.') ?></td>
                                <td class="text-right text-success">Rp <?= number_format($totalAll - $totalPotongan, 0, ',', '.') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="mt-5 font-weight-bold">Detail Laporan Shift</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-sm">
                        <thead class="thead-dark text-center">
                            <tr>
                                <th>Shift</th>
                                <th>Jam</th>
                                <th>Kasir</th>
                                <th>Outlet</th>
                                <th>Total Penjualan</th>
                                <th>Total Pengeluaran</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laporan as $row): ?>
                                <tr>
                                    <td class="text-center"><?= esc($row['shift']) ?></td>
                                    <td class="text-center"><?= esc($row['jam']) ?></td>
                                    <td><?= esc($row['kasir']) ?></td>
                                    <td><?= esc($row['outlet']) ?></td>
                                    <td class="text-right text-success">Rp<?= number_format($row['total_penjualan'], 0, ',', '.') ?></td>
                                    <td class="text-right text-danger">Rp<?= number_format($row['total_pengeluaran'], 0, ',', '.') ?></td>
                                    <td><?= esc($row['keterangan_pengeluaran']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>