<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container-fluid mt-4">
    <h3 class="mb-4 font-weight-bold text-gray-800">Daftar Transaksi</h3>

    <!-- Filter -->
    <form method="get" class="mb-4">
        <div class="form-row align-items-end">
            <?php if (in_groups('admin')) : ?>
                <div class="col-md-3 mb-2">
                    <label for="outlet_id">Outlet</label>
                    <select name="outlet_id" class="form-control">
                        <option value="">Semua Outlet</option>
                        <?php foreach ($outlets as $outlet): ?>
                            <option value="<?= $outlet['id'] ?>" <?= ($selectedOutlet == $outlet['id']) ? 'selected' : '' ?>>
                                <?= esc($outlet['nama_outlet']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="col-md-3 mb-2">
                <label for="start_date">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control" value="<?= esc($startDate ?? '') ?>">
            </div>

            <div class="col-md-3 mb-2">
                <label for="end_date">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control" value="<?= esc($endDate ?? '') ?>">
            </div>

            <div class="col-md-3 mb-2">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-filter mr-1"></i> Terapkan Filter
                </button>
            </div>
        </div>
    </form>

    <!-- Tombol Cetak -->
    <?php
    $queryStr = http_build_query([
        'outlet_id' => $selectedOutlet,
        'start_date' => $startDate,
        'end_date' => $endDate
    ]);
    ?>

    <div class="d-flex justify-content-between mb-3">
        <a href="<?= base_url('manajemen-penjualan/cetakSemua?' . $queryStr) ?>" target="_blank" class="btn btn-outline-success">
            <i class="fas fa-print mr-1"></i> Cetak Semua
        </a>

        <button type="submit" form="formCetakTerpilih" class="btn btn-outline-info">
            <i class="fas fa-print mr-1"></i> Cetak Terpilih
        </button>
    </div>

    <!-- Tabel Transaksi -->
    <form action="<?= base_url('manajemen-penjualan/cetak_terpilih') ?>" method="post" target="_blank" id="formCetakTerpilih">
        <?= csrf_field() ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead class="thead-dark text-center">
                    <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th>#</th>
                        <th>No Faktur</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Kasir</th>
                        <th>Outlet</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transaksi)): ?>
                        <?php $no = 1 + (10 * ($pager->getCurrentPage('transaksi') - 1));
                        foreach ($transaksi as $row): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="transaksi_ids[]" value="<?= $row['id'] ?>">
                                </td>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= esc($row['no_faktur']) ?></td>
                                <td><?= esc($row['tgl_jual']) ?></td>
                                <td>Rp<?= number_format($row['grand_total'], 0, ',', '.') ?></td>
                                <td><?= esc($row['nama_kasir']) ?></td>
                                <td><?= esc($row['nama_outlet'] ?? '-') ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('manajemen-penjualan/detail/' . $row['id']) ?>" class="btn btn-sm btn-outline-primary mb-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('manajemen-penjualan/cetak/' . $row['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary mb-1">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <?php if (in_groups('admin')) : ?>
                                        <a href="<?= base_url('manajemen-penjualan/hapus/' . $row['id']) ?>" class="btn btn-sm btn-outline-danger mb-1" onclick="return confirm('Yakin ingin menghapus transaksi ini?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data transaksi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <small class="text-muted">
                        Halaman <?= $pager->getCurrentPage('transaksi') ?> dari <?= $pager->getPageCount('transaksi') ?>
                    </small>
                </div>
                <div>
                    <nav aria-label="Page navigation">
                        <?= $pager->only(['outlet_id', 'start_date', 'end_date'])->links('transaksi', 'bootstrap_full') ?>
                    </nav>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Script Check All -->
<script>
    document.getElementById('checkAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="transaksi_ids[]"]');
        for (const cb of checkboxes) {
            cb.checked = this.checked;
        }
    });
</script>

<?= $this->endSection() ?>