<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container mt-4">
    <h4 class="mb-4">Filter Cetak Laporan Persediaan Bahan</h4>

    <form action="<?= base_url('produksi/laporan/cetak_persediaan_bahan'); ?>" method="get" target="_blank">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-dark">Cetak Laporan</button>
            </div>
        </div>
    </form>
</div>

<?= $this->endSection(); ?>