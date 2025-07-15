<?= $this->extend('templates/index_templates_general'); ?>

<?= $this->section('page-content'); ?>
<div class="container mt-4 ">
    <h3>Daftar Jurnal Umum</h3>
    <div class="d-flex justify-content-between mb-3">
        <a href="<?= base_url('keuangan/create_jurnal') ?>" class="btn btn-success">+ Tambah Jurnal</a>
    </div>
    <table class="table table-bordered table-hover table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Tanggal</th>
                <th>Akun</th>
                <th>Debit (Rp)</th>
                <th>Kredit (Rp)</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($jurnal)) : ?>
                <?php foreach ($jurnal as $row) : ?>
                    <tr>
                        <td><?= esc($row['tanggal']) ?></td>
                        <td><?= esc($row['nama_akun'] ?? '—') ?></td>
                        <td><?= number_format($row['debit'], 2, ',', '.') ?></td>
                        <td><?= number_format($row['kredit'], 2, ',', '.') ?></td>
                        <td><?= esc($row['keterangan']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" class="text-center">Belum ada data jurnal.</td>
                </tr>
            <?php endif; ?>

        </tbody>
    </table>
</div>
<?= $this->endSection(); ?>