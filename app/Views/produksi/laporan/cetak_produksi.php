<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>
<div class="container mt-4">
    <h4 class="mb-3"><?= esc($tittle); ?></h4>
    <form method="get" class="row g-3 mb-4">
        <div class="col-auto">
            <input type="date" name="tanggal_mulai" class="form-control" value="<?= esc($_GET['tanggal_mulai'] ?? '') ?>" required>
        </div>
        <div class="col-auto">
            <input type="date" name="tanggal_selesai" class="form-control" value="<?= esc($_GET['tanggal_selesai'] ?? '') ?>" required>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No Produksi</th>
                    <th>BSJ</th>
                    <th>Jumlah</th>
                    <th>Total Biaya</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($produksi)) : $no = 1;
                    foreach ($produksi as $row) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= esc($row['tanggal']) ?></td>
                            <td><?= esc($row['no_produksi']) ?></td>
                            <td><?= esc($row['bsj_nama']) ?></td>
                            <td><?= esc($row['jumlah']) ?> <?= esc($row['bsj_satuan']) ?></td>
                            <td>Rp <?= number_format($row['total_biaya'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection(); ?>