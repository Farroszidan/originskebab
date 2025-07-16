<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>
<div class="container mt-4">
    <div class="container mt-4 text-center">
        <h2 class="mb-0">Sistem Informasi Laporan Keuangan</h2>
        <h2 class="mb-0">Origins Kebab</h2> <br>
        <h3 class="mb-4">Laporan Utang</h3> <br>
    </div>
    <!-- Form Filter -->
    <form method="get" class="mb-3">
        <div class="form-row align-items-end">
            <div class="col-md-3">
                <label for="start_date">Dari</label>
                <input type="date" name="start_date" class="form-control" value="<?= $start ?>" required>
            </div>
            <div class="col-md-3">
                <label for="end_date">Sampai</label>
                <input type="date" name="end_date" class="form-control" value="<?= $end ?>" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary mt-3">Tampilkan</button>
                <a href="<?= current_url() ?>" class="btn btn-secondary mt-3">Reset</a>
            </div>
        </div>
    </form>

    <hr>
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-bordered mt-3">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40%">Supplier</th>
                        <th style="width: 15%">Jumlah Utang (Rp)</th>
                        <th style="width: 10%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($utang)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data utang pada periode ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($utang as $row): ?>
                            <tr>
                                <td><?= esc($row['nama_supplier']) ?></td>
                                <td><?= number_format($row['jumlah'], 2, ',', '.') ?></td>
                                <td>
                                    <a href="<?= base_url('keuangan/form_pelunasan_utang/' . $row['kode_akun']) ?>" class="btn btn-sm btn-success">
                                        Pelunasan
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

                <?php if (!empty($utang)): ?>
                    <tfoot>
                        <tr>
                            <th class="text-right">Total Utang</th>
                            <th class="text-right">Rp <?= number_format($total_utang, 2, ',', '.') ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <br>
    <a href="<?= base_url("keuangan/export_pdf_utang?start_date=$start&end_date=$end") ?>" class="btn btn-danger mb-3" target="_blank">
        <i class="fas fa-file-pdf"></i> Export PDF
    </a>
</div>
<?= $this->endSection(); ?>