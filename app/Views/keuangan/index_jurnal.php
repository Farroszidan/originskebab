<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>
<div class="container mt-4 ">
    <div class="container mt-4 text-center">
        <h2 class="mb-0">Sistem Informasi Laporan Keuangan</h2>
        <h2 class="mb-0">Origins Kebab</h2> <br>
        <h3 class="mb-4">Jurnal Umum</h3> <br>
    </div>

    <div class="d-flex justify-content-between mb-3">
        <a href="<?= base_url('keuangan/create_jurnal') ?>" class="btn btn-success">+ Tambah Jurnal</a>
    </div>
    <form method="get" action="<?= base_url('keuangan/index') ?>" class="form-inline mb-3">
        <label class="mr-2">Filter Periode:</label>
        <input type="date" name="start_date" class="form-control mr-2" value="<?= esc($_GET['start_date'] ?? '') ?>">
        <input type="date" name="end_date" class="form-control mr-2" value="<?= esc($_GET['end_date'] ?? '') ?>">
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    <table class="table table-bordered table-hover table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Akun</th>
                <th>Debit (Rp)</th>
                <th>Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($jurnal)) : ?>
                <?php foreach ($jurnal as $group) : ?>
                    <?php $rowspan = count($group['detail']); ?>
                    <?php foreach ($group['detail'] as $index => $row) : ?>
                        <tr>
                            <?php if ($index === 0) : ?>
                                <td rowspan="<?= $rowspan ?>"><?= esc($group['tanggal']) ?></td>
                                <td rowspan="<?= $rowspan ?>"><?= esc($group['keterangan']) ?></td>
                            <?php endif; ?>
                            <td><?= esc($row['nama_akun']) ?></td>
                            <td><?= number_format($row['debit'], 2, ',', '.') ?></td>
                            <td><?= number_format($row['kredit'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data jurnal.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>
<?= $this->endSection(); ?>