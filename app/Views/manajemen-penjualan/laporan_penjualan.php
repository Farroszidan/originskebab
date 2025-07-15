<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid mt-4">
    <h3 class="mb-4 font-weight-bold text-gray-800">Laporan Penjualan</h3>

    <!-- Filter Form -->
    <form method="get" action="<?= base_url('manajemen-penjualan/laporanpenjualan') ?>" class="form-row align-items-end mb-4 no-print">
        <!-- Tanggal Awal -->
        <div class="form-group col-md-3">
            <label for="tanggal_awal">Dari Tanggal</label>
            <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" value="<?= esc($tanggal_awal) ?>">
        </div>

        <!-- Tanggal Akhir -->
        <div class="form-group col-md-3">
            <label for="tanggal_akhir">Sampai Tanggal</label>
            <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" value="<?= esc($tanggal_akhir) ?>">
        </div>

        <!-- Outlet -->
        <div class="form-group col-md-4">
            <label for="outlet_id">Outlet</label>
            <?php if (in_groups(['admin', 'keuangan'])) : ?>
                <select name="outlet_id" id="outlet_id" class="form-control">
                    <option value="">Semua Outlet</option>
                    <?php foreach ($outlets as $outlet) : ?>
                        <option value="<?= $outlet['id'] ?>" <?= ($selectedOutlet == $outlet['id']) ? 'selected' : '' ?>>
                            <?= esc($outlet['nama_outlet']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif (in_groups('penjualan')) : ?>
                <input type="hidden" name="outlet_id" value="<?= esc($outlet_id) ?>">
                <input type="text" class="form-control" value="<?= esc($nama_outlet) ?>" readonly>
            <?php endif; ?>
        </div>

        <!-- Tampilkan Button -->
        <div class="form-group col-md-2">
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-filter"></i> Tampilkan
            </button>
        </div>

        <!-- Cetak Laporan Button -->
        <?php if (!empty($laporan) && $isFiltered): ?>
            <div class="form-group col-md-3 text-right">
                <a href="<?= base_url('manajemen-penjualan/cetak_laporan_penjualan?tanggal_awal=' . $tanggal_awal . '&tanggal_akhir=' . $tanggal_akhir . '&outlet_id=' . $selectedOutlet) ?>"
                    class="btn btn-success btn-block" target="_blank">
                    <i class="fas fa-print"></i> Cetak Laporan
                </a>
            </div>
        <?php endif; ?>
    </form>

    <!-- Ringkasan Penjualan (dalam bentuk tabel) -->
    <?php if (!empty($laporan) && $isFiltered): ?>
        <div class="table-responsive mt-3">
            <table class="table table-bordered text-center">
                <thead class="thead-light">
                    <tr>
                        <th colspan="2">
                            Ringkasan Penjualan
                            <?= $isSingleDate
                                ? 'Tanggal ' . date('d-m-Y', strtotime($tanggal_awal))
                                : 'dari ' . date('d-m-Y', strtotime($tanggal_awal)) . ' sampai ' . date('d-m-Y', strtotime($tanggal_akhir))
                            ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-left"><strong>Total Penjualan</strong></td>
                        <td class="text-right">Rp<?= number_format($grandTotalPenjualan, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td class="text-left"><strong>Total Pengeluaran</strong></td>
                        <td class="text-right">Rp<?= number_format($grandTotalPengeluaran, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td class="text-left"><strong>Keuntungan Kotor</strong></td>
                        <td class="text-right font-weight-bold <?= $selisih >= 0 ? 'text-success' : 'text-danger' ?>">
                            Rp<?= number_format($selisih, 0, ',', '.') ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Tabel Detail Shift -->
    <?php if (!empty($laporan)) : ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped">
                <thead class="thead-dark text-center">
                    <tr>
                        <th>Outlet</th>
                        <th>Shift</th>
                        <th>Jam</th>
                        <th>Total Penjualan</th>
                        <th>Total Pengeluaran</th>
                        <th>Rincian Pengeluaran</th>
                        <?php if (in_groups('admin')) : ?>
                            <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laporan as $row) : ?>
                        <tr>
                            <td><?= esc($row['nama_outlet'] ?? '-') ?></td>
                            <td><?= esc($row['nama_shift'] ?? '-') ?></td>
                            <td><?= esc($row['jam_mulai'] ?? '-') ?> - <?= esc($row['jam_selesai'] ?? '-') ?></td>
                            <td class="text-right">Rp<?= number_format($row['total_penjualan'], 0, ',', '.') ?></td>
                            <td class="text-right">Rp<?= number_format($row['total_pengeluaran'], 0, ',', '.') ?></td>
                            <td><?= esc($row['keterangan_pengeluaran'] ?? '-') ?></td>
                            <?php if (in_groups('admin')) : ?>
                                <td class="text-center">
                                    <form method="post" action="<?= base_url('manajemen-penjualan/hapus-laporan-shift/' . $row['id']) ?>"
                                        onsubmit="return confirm('Yakin ingin menghapus laporan shift ini?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="tanggal_awal" value="<?= esc($tanggal_awal) ?>">
                                        <input type="hidden" name="tanggal_akhir" value="<?= esc($tanggal_akhir) ?>">
                                        <input type="hidden" name="outlet_id" value="<?= esc($selectedOutlet) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-light font-weight-bold text-center">
                    <tr>
                        <td colspan="3" class="text-right">TOTAL</td>
                        <td class="text-right">Rp<?= number_format($grandTotalPenjualan, 0, ',', '.') ?></td>
                        <td class="text-right">Rp<?= number_format($grandTotalPengeluaran, 0, ',', '.') ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php else : ?>
        <div class="alert alert-info mt-3">Tidak ada data untuk tanggal dan outlet yang dipilih.</div>
    <?php endif; ?>
</div>

<?= $this->endSection(); ?>