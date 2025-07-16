<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">

    <h4 class="mb-4"><?= $tittle; ?></h4>

    <form method="get" action="<?= base_url('manajemen-penjualan/hppPenjualan'); ?>" class="row mb-3">
        <div class="col-md-3">
            <label>Dari Tanggal</label>
            <input type="date" name="start" class="form-control" value="<?= esc($start ?? '') ?>" required>
        </div>
        <div class="col-md-3">
            <label>Sampai Tanggal</label>
            <input type="date" name="end" class="form-control" value="<?= esc($end ?? '') ?>" required>
        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-primary btn-block">Tampilkan</button>
        </div>
    </form>

    <?php if (isset($start) && isset($end)) : ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <p><strong>Periode:</strong> <?= date('d M Y', strtotime($start)); ?> s/d <?= date('d M Y', strtotime($end)); ?> (<?= $days ?> hari)</p>

                <table class="table table-bordered">
                    <tr>
                        <th>Total Biaya Produksi (hpp_bsj)</th>
                        <td>Rp <?= number_format($total_biaya_hpp, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>Total Gaji (BTKL)</th>
                        <td>Rp <?= number_format($total_btkl, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>Total Pembelian Operasional</th>
                        <td>Rp <?= number_format($total_operasional, 0, ',', '.') ?></td>
                    </tr>
                    <tr class="table-warning">
                        <th>Total Semua Biaya</th>
                        <td><strong>Rp <?= number_format($total_semua_biaya, 0, ',', '.') ?></strong></td>
                    </tr>
                    <tr>
                        <th>Jumlah Hari</th>
                        <td><?= $days ?> hari</td>
                    </tr>
                    <tr>
                        <th>Total Produksi (porsi)</th>
                        <td><?= number_format($total_produksi, 0, ',', '.') ?> porsi</td>
                    </tr>
                    <tr class="table-info">
                        <th>HPP Rata-rata Harian (Total / 30)</th>
                        <td><strong>Rp <?= number_format($hpp_per_hari, 0, ',', '.') ?></strong></td>
                    </tr>
                    <tr class="table-success">
                        <th>HPP Penjualan per Porsi</th>
                        <td><strong>Rp <?= number_format($hpp_per_porsi, 0, ',', '.') ?> / porsi</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<?= $this->endSection(); ?>