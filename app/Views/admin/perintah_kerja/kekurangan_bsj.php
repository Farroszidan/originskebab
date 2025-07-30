<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>

    <!-- TABEL BSJ -->
    <div class="card mb-4">
        <div class="card-header">Kekurangan Stok BSJ (Kulit & Olahan Daging)</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Nama BSJ</th>
                        <th>Kode</th>
                        <th>Kekurangan Total</th>
                        <th>Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bsj as $item): ?>
                        <tr>
                            <td><?= esc($item['nama']) ?></td>
                            <td><?= esc($item['kode']) ?></td>
                            <td><?= esc($item['kurang']) ?></td>
                            <td><?= esc($item['satuan']) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TABEL BAHAN BAKU -->
    <div class="card mb-4">
        <div class="card-header">Kekurangan Stok Bahan Baku (BPxxx)</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Nama Bahan</th>
                        <th>Kode</th>
                        <th>Kekurangan Total</th>
                        <th>Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bahan_baku)): ?>
                        <?php foreach ($bahan_baku as $item): ?>
                            <tr>
                                <td><?= esc($item['nama']) ?></td>
                                <td><?= esc($item['kode']) ?></td>
                                <td><?= esc($item['kurang']) ?></td>
                                <td><?= esc($item['satuan']) ?></td>
                            </tr>
                        <?php endforeach ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Semua bahan baku cukup.</td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="<?= base_url('admin/perintah-kerja') ?>" class="btn btn-secondary">Kembali</a>
</div>

<?= $this->endSection(); ?>