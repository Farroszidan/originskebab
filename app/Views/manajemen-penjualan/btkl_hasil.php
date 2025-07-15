<?= $this->extend('templates/index_templates_general'); ?>

<?= $this->section('page-content'); ?>

<div class="container mt-4">
    <h4>Hasil Perhitungan BTKL Gaji Shift Karyawan Penjualan</h4>
    <p>Periode: <?= date('d M Y', strtotime($start)) ?> - <?= date('d M Y', strtotime($end)) ?></p>

    <table class="table table-bordered table-hover mt-4">
        <thead class="thead-dark">
            <tr>
                <th>Nama Karyawan</th>
                <th>Jumlah Shift</th>
                <th>Gaji per Shift</th>
                <th>Total Gaji</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($results) > 0): ?>
                <?php foreach ($results as $r): ?>
                    <tr>
                        <td><?= esc($r['fullname']) ?></td>
                        <td><?= $r['total_shift'] ?></td>
                        <td>Rp <?= number_format($gaji_per_shift, 0, ',', '.') ?></td>
                        <td><strong>Rp <?= number_format($r['total_shift'] * $gaji_per_shift, 0, ',', '.') ?></strong></td>
                    </tr>
                <?php endforeach ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data shift pada periode ini.</td>
                </tr>
            <?php endif ?>
        </tbody>
    </table>

    <a href="<?= base_url('manajemen-penjualan/btkl') ?>" class="btn btn-secondary mt-3">Kembali</a>
</div>

<?= $this->endSection() ?>