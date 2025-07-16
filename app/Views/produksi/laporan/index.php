<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Laporan Produksi</h4>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="<?= base_url('produksi/laporan/cetak_pembelian') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-file-earmark-text me-2"></i> Cetak Daftar Pembelian
                    </a>
                    <a href="<?= base_url('produksi/laporan/cetak_produksi') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-clipboard-data me-2"></i> Cetak Daftar Produksi
                    </a>
                    <a href="<?= base_url('produksi/laporan/cetak_persediaan_bahan') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-box-seam me-2"></i> Cetak Persediaan Bahan
                    </a>
                    <a href="<?= base_url('produksi/laporan/cetak_persediaan_bsj') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-boxes me-2"></i> Cetak Persediaan BSJ
                    </a>
                    <a href="<?= base_url('produksi/laporan/cetak_pengiriman') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-truck me-2"></i> Cetak Pengiriman
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>