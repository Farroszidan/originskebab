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
                    <th>No Nota</th>
                    <th>Pemasok</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pembelian)) : $no = 1;
                    foreach ($pembelian as $row) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= esc($row['tanggal']) ?></td>
                            <td><?= esc($row['no_nota']) ?></td>
                            <td><?= esc($row['nama_pemasok']) ?></td>
                            <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection(); ?>