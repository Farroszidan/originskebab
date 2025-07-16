<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container mt-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white rounded-top">
            <h5 class="mb-0"><i class="fas fa-calculator mr-2"></i>Perhitungan Gaji Karyawan (BTKL) - Shift Penjualan</h5>
        </div>
        <div class="card-body">

            <!-- Form Perhitungan -->
            <form action="<?= base_url('manajemen-penjualan/btkl/form') ?>" method="post" class="row mb-4">
                <div class="form-group col-md-5">
                    <label><strong>Dari Tanggal</strong></label>
                    <input type="date" name="start_date" class="form-control shadow-sm" required value="<?= esc($start) ?>">
                </div>
                <div class="form-group col-md-5">
                    <label><strong>Sampai Tanggal</strong></label>
                    <input type="date" name="end_date" class="form-control shadow-sm" required value="<?= esc($end) ?>">
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block shadow-sm">
                        <i class="fas fa-search"></i> Hitung
                    </button>
                </div>
            </form>

            <?php if (!empty($results)): ?>
                <!-- Hasil Perhitungan -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <h5 class="mb-1"><i class="fas fa-table mr-2"></i>Hasil Perhitungan BTKL</h5>
                        <p class="text-muted mb-3">Periode: <strong><?= date('d M Y', strtotime($start)) ?> - <?= date('d M Y', strtotime($end)) ?></strong></p>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover shadow-sm">
                                <thead class="thead-dark text-center">
                                    <tr>
                                        <th>Nama Karyawan</th>
                                        <th>Jumlah Shift</th>
                                        <th>Gaji per Shift</th>
                                        <th>Total Gaji</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $r): ?>
                                        <tr class="text-center align-middle">
                                            <td class="text-left"><?= esc($r['username']) ?></td>
                                            <td><?= $r['jumlah_shift'] ?></td>
                                            <td>Rp <?= number_format($gaji_per_shift, 0, ',', '.') ?></td>
                                            <td><strong>Rp <?= number_format($r['total_gaji'], 0, ',', '.') ?></strong></td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <th colspan="3" class="text-right">Total Keseluruhan Gaji</th>
                                        <th class="text-center">Rp <?= number_format($total_keseluruhan, 0, ',', '.') ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="text-right mt-3">
                            <a href="<?= base_url('manajemen-penjualan/btkl?start_date=' . $start . '&end_date=' . $end) ?>" class="btn btn-success shadow-sm">
                                <i class="fas fa-list mr-1"></i> Lihat Daftar BTKL Tersimpan
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif ?>

        </div>
    </div>
</div>

<?= $this->endSection(); ?>