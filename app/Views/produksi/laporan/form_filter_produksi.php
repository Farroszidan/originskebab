<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container mt-4">
    <h4 class="mb-4">Filter Cetak Laporan Produksi</h4>

    <form action="<?= base_url('produksi/laporan/cetak_produksi'); ?>" method="get" target="_blank">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="start" class="form-label">Tanggal Awal</label>
                <input type="date" name="start" id="start" class="form-control" required value="<?= date('Y-m-01'); ?>">
            </div>
            <div class="col-md-4">
                <label for="end" class="form-label">Tanggal Akhir</label>
                <input type="date" name="end" id="end" class="form-control" required value="<?= date('Y-m-d'); ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-dark">Cetak Laporan</button>
            </div>
        </div>
    </form>
</div>

<?= $this->endSection(); ?>