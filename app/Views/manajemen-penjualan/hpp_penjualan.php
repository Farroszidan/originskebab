<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid mt-4">
    <h3 class="font-weight-bold mb-4 text-dark">🧾 Laporan HPP</h3>

    <form method="get" class="form-inline mb-3">
        <label class="mr-2">Tanggal Awal</label>
        <input type="date" name="tanggal_awal" class="form-control mr-3" value="<?= $tanggal_awal ?>">

        <label class="mr-2">Tanggal Akhir</label>
        <input type="date" name="tanggal_akhir" class="form-control mr-3" value="<?= $tanggal_akhir ?>">

        <button class="btn btn-primary">Filter</button>
    </form>

    <table class="table table-bordered table-sm table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Bahan</th>
                <th>Stok Awal</th>
                <th>Stok Masuk</th>
                <th>Total Masuk</th>
                <th>Total Keluar</th>
                <th>Stok Akhir</th>
                <th>HPP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataHPP as $row): ?>
                <?php
                $stok_awal    = (float) $row['stok_awal'];
                $stok_masuk   = (float) $row['stok_masuk'];
                $total_masuk  = (float) $row['total_masuk'];
                $total_keluar = (float) $row['total_keluar'];
                $stok_akhir   = $stok_awal + $stok_masuk - $total_keluar;
                $hpp          = $total_keluar > 0 ? ($stok_awal + $stok_masuk - $stok_akhir) : 0;
                ?>
                <tr>
                    <td><?= esc($row['nama_bahan']) ?></td>
                    <td><?= number_format($stok_awal, 2) ?></td>
                    <td><?= number_format($stok_masuk, 2) ?></td>
                    <td><?= number_format($total_masuk, 2) ?></td>
                    <td><?= number_format($total_keluar, 2) ?></td>
                    <td><?= number_format($stok_akhir, 2) ?></td>
                    <td><strong><?= number_format($hpp, 2) ?></strong></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>

<?= $this->endSection(); ?>