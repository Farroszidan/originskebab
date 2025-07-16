<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>

    <?php if ($role === 'admin'): ?>
        <div class="card shadow-sm border-left-primary mb-4">
            <div class="card-body">
                <h5 class="text-primary font-weight-bold mb-3">
                    <i class="fas fa-filter mr-2"></i> Filter Periode Penjualan
                </h5>
                <form method="get" action="<?= base_url('dashboard') ?>" class="form-row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label for="start">Tanggal Mulai</label>
                        <input type="date" name="start" id="start" class="form-control" value="<?= esc($start) ?>" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="end">Tanggal Selesai</label>
                        <input type="date" name="end" id="end" class="form-control" value="<?= esc($end) ?>" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search mr-1"></i> Tampilkan
                        </button>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary btn-block">
                            <i class="fas fa-sync-alt mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>


        <?php if (!empty($penjualanPerOutlet)): ?>
            <div class="row">
                <?php foreach ($penjualanPerOutlet as $outlet): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body d-flex align-items-center">
                                <div class="mr-3">
                                    <i class="fas fa-store fa-2x text-success"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        <?= esc($outlet['nama_outlet']) ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        Rp <?= number_format($outlet['total'], 0, ',', '.') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="col-12 mt-3">
                    <div class="alert alert-info shadow-sm">
                        <strong>Total Penjualan Semua Outlet:</strong>
                        <span class="float-right font-weight-bold text-success">
                            Rp <?= number_format($totalSeluruhOutlet ?? 0, 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Tidak ada data penjualan untuk periode ini.</div>
        <?php endif; ?>

        <?php if (!empty($kas_outlet_admin)): ?>
            <div class="row mt-4">
                <div class="col-12 mb-2">
                    <h5 class="text-info font-weight-bold">
                        <i class="fas fa-wallet mr-2"></i> Uang Laci (Saldo Kas) Semua Outlet
                    </h5>
                </div>
                <?php foreach ($kas_outlet_admin as $kas): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body d-flex align-items-center">
                                <div class="mr-3">
                                    <i class="fas fa-cash-register fa-2x text-info"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        <?= esc($kas['nama_akun']) ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        Rp <?= number_format($kas['saldo_awal'], 0, ',', '.') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>


    <?php elseif ($role === 'penjualan'): ?>
        <div class="card shadow-sm mb-4 border-left-primary">
            <div class="card-body">
                <h5 class="text-primary font-weight-bold mb-3">
                    <i class="fas fa-calendar-alt mr-2"></i> Filter Periode Penjualan
                </h5>
                <form method="get" action="<?= base_url('dashboard') ?>" class="form-row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label for="start">Tanggal Mulai</label>
                        <input type="date" name="start" id="start" class="form-control" value="<?= esc($start) ?>" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="end">Tanggal Selesai</label>
                        <input type="date" name="end" id="end" class="form-control" value="<?= esc($end) ?>" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter mr-1"></i> Tampilkan
                        </button>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary btn-block">
                            <i class="fas fa-sync-alt mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-left-success mb-3">
            <div class="card-body d-flex align-items-center">
                <div class="mr-3">
                    <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                </div>
                <div>
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Total Penjualan <?= esc($nama_outlet) ?>
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        Rp <?= number_format($total_penjualan ?? 0, 0, ',', '.') ?>
                    </div>
                    <small class="text-muted">
                        Periode: <?= date('d M Y', strtotime($start)) ?> s/d <?= date('d M Y', strtotime($end)) ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Kas Outlet -->
        <?php if (!empty($kas_outlet)): ?>
            <div class="row mt-4">
                <div class="col-12 mb-2">
                    <h5 class="text-info font-weight-bold">
                        <i class="fas fa-wallet mr-2"></i> Uang Laci
                    </h5>
                </div>
                <?php foreach ($kas_outlet as $kas): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body d-flex align-items-center">
                                <div class="mr-3">
                                    <i class="fas fa-cash-register fa-2x text-info"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        <?= esc($kas['nama_outlet']) ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        Rp <?= number_format($kas['saldo_awal'], 0, ',', '.') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php elseif ($role === 'keuangan'): ?>
        <div class="alert alert-primary shadow-sm">
            <strong>Selamat datang, Keuangan!</strong> Berikut ringkasan kas outlet.
        </div>

        <div class="row">
            <?php if (!empty($kas_outlet)): ?>
                <div class="row mt-4">
                    <div class="col-12 mb-2">
                        <h5 class="text-info font-weight-bold">
                            <i class="fas fa-wallet mr-2"></i> Uang Laci (Saldo Kas) Semua Outlet
                        </h5>
                    </div>
                    <?php foreach ($kas_outlet as $kas): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-cash-register fa-2x text-info"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            <?= esc($kas['nama_outlet']) ?>
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            Rp <?= number_format($kas['saldo_awal'], 0, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

        <a href="<?= base_url('keuangan/isi-kas') ?>" class="btn btn-sm btn-success shadow-sm mb-4">
            <i class="fas fa-plus-circle mr-1"></i> Isi Kas Outlet
        </a>

    <?php elseif ($role === 'produksi'): ?>
        <div class="alert alert-info shadow-sm">
            <strong>Selamat datang, Produksi!</strong> Fitur produksi sedang dikembangkan.
        </div>

    <?php else: ?>
        <div class="alert alert-danger shadow-sm">
            Role tidak dikenali. Silakan hubungi administrator.
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection(); ?>