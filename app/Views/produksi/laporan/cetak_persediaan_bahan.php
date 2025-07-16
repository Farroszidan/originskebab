<?php $tittle = 'Cetak Persediaan Bahan'; ?>
<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>
<div class="container mt-4">
    <h4 class="mb-3"><?= esc($tittle); ?></h4>
    <form method="get" class="row g-3 mb-4">
        <div class="col-auto">
            <input type="text" name="keyword" class="form-control" placeholder="Cari nama/kode bahan..." value="<?= esc($_GET['keyword'] ?? '') ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Cari</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th>Stok</th>
                    <th>Satuan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bahan)) : $no = 1;
                    foreach ($bahan as $row) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= esc($row['kode']) ?></td>
                            <td><?= esc($row['nama']) ?></td>
                            <td><?= esc($row['kategori']) ?></td>
                            <td><?= esc($row['jenis']) ?></td>
                            <td><?= esc($row['stok']) ?></td>
                            <td><?= esc($row['satuan']) ?></td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection(); ?>