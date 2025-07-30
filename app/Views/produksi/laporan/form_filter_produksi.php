<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container mt-4">
    <h4 class="mb-3">Form Cetak Laporan Produksi</h4>

    <form method="get" action="<?= base_url('produksi/laporan/cetak_produksi'); ?>" class="row g-3">
        <div class="col-md-4">
            <label for="start">Tanggal Mulai</label>
            <input type="date" class="form-control" id="start" name="start" required value="<?= date('Y-m-01'); ?>">
        </div>
        <div class="col-md-4">
            <label for="end">Tanggal Akhir</label>
            <input type="date" class="form-control" id="end" name="end" required value="<?= date('Y-m-d'); ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </div>
    </form>
</div>

<?= $this->endSection(); ?>