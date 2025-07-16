<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container mt-4">
    <h4 class="mb-2">Hasil Perhitungan Gaji Karyawan Shift (BTKL)</h4>
    <p class="text-muted">Periode: <strong><?= date('d M Y', strtotime($start)) ?> - <?= date('d M Y', strtotime($end)) ?></strong></p>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>Jumlah Shift</th>
                        <th>Gaji per Shift</th>
                        <th>Total Gaji</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grandTotal = 0;
                    ?>
                    <?php if (count($results) > 0): ?>
                        <?php foreach ($results as $r):
                            $totalGaji = $r['total_shift'] * $gaji_per_shift;
                            $grandTotal += $totalGaji;
                        ?>
                            <tr>
                                <td><?= esc($r['fullname']) ?></td>
                                <td><?= $r['total_shift'] ?></td>
                                <td>Rp <?= number_format($gaji_per_shift, 0, ',', '.') ?></td>
                                <td><strong>Rp <?= number_format($totalGaji, 0, ',', '.') ?></strong></td>
                            </tr>
                        <?php endforeach ?>
                        <tr class="bg-light font-weight-bold">
                            <td colspan="3" class="text-right">Total Keseluruhan</td>
                            <td>Rp <?= number_format($grandTotal, 0, ',', '.') ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-danger">Tidak ada data shift pada periode ini.</td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="<?= base_url('manajemen-penjualan/btkl') ?>" class="btn btn-secondary mt-3">
        <i class="fas fa-arrow-left mr-1"></i> Kembali
    </a>
</div>

<?= $this->endSection() ?>