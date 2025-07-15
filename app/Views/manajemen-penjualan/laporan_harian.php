<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>
<div class="container mt-4">
    <h2>Laporan Harian Per Shift</h2>
    <form method="get" action="<?= base_url('manajemen-penjualan/laporanHarian') ?>">
        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" name="tanggal" class="form-control mb-2" value="<?= esc($tanggal) ?>">
        </div>
        <div class="form-group">
            <label for="outlet">Pilih Outlet</label>
            <select name="outlet_id" class="form-control mb-3">
                <option value="">Semua Outlet</option>
                <?php foreach ($outlets as $outlet): ?>
                    <option value="<?= $outlet['id'] ?>" <?= isset($selectedOutlet) && $selectedOutlet == $outlet['id'] ? 'selected' : '' ?>>
                        <?= $outlet['nama_outlet'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary">Lihat Laporan</button>
        <?php if (!empty($laporan)): ?>
            <a href="javascript:window.print()" class="btn btn-success ml-2">Cetak Laporan</a>
        <?php endif; ?>
    </form>

    <?php if (!empty($laporan)): ?>
        <h3 class="mt-4">Laporan Shift - <?= esc($tanggal) ?></h3>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Shift</th>
                    <th>Jam</th>
                    <th>Kasir</th>
                    <th>Outlet</th>
                    <th>Total Penjualan</th>
                    <th>Total Pengeluaran</th>
                    <th>Keterangan Pengeluaran</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($laporan as $row): ?>
                    <tr>
                        <td><?= esc($row['shift']) ?></td>
                        <td><?= esc($row['jam']) ?></td>
                        <td><?= esc($row['kasir']) ?></td>
                        <td><?= esc($row['outlet']) ?></td>
                        <td>Rp<?= number_format($row['total_penjualan'], 0, ',', '.') ?></td>
                        <td>Rp<?= number_format($row['total_pengeluaran'], 0, ',', '.') ?></td>
                        <td><?= esc($row['keterangan_pengeluaran']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?= $this->endSection(); ?>