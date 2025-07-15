<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h1 mb-4 text-gray-800">Dashboard Keuangan</h1>
    <h1 class="h5 mb-4 text-gray-800">Selamat datang di halaman keuangan</h1>

    <div class="row">
        <?php if (!empty($kas_outlet)): ?>
            <?php foreach ($kas_outlet as $kas): ?>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-wallet fa-2x text-info"></i>
                            </div>
                            <div>
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    <?= esc($kas['nama_outlet']); ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Rp <?= number_format($kas['saldo_awal'], 0, ',', '.'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning">Data kas outlet belum tersedia.</div>
            </div>
        <?php endif; ?>
    </div>
    <a href="<?= base_url('keuangan/isi-kas') ?>" class="btn btn-sm btn-success mb-4">
        <i class="fas fa-plus-circle"></i> Isi Kas Outlet
    </a>
</div>

<?= $this->endSection(); ?>