<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 font-weight-bold text-gray-800">Daftar Permintaan Barang ke Produksi</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success'); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header bg-white border-0">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <form method="get" class="form-row">
                        <div class="col-md-5 mb-2">
                            <input type="date" name="start_date" id="start_date" class="form-control" value="<?= esc($filter['start_date'] ?? '') ?>" placeholder="Mulai">
                        </div>
                        <div class="col-md-5 mb-2">
                            <input type="date" name="end_date" id="end_date" class="form-control" value="<?= esc($filter['end_date'] ?? '') ?>" placeholder="Selesai">
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-secondary btn-block">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-md-right mt-2 mt-md-0">
                    <a href="<?= base_url('manajemen-penjualan/formPermintaan'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Input Permintaan
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Tujuan</th>
                            <th>Jumlah Item</th>
                            <th>Daftar Barang</th>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($permintaan)): ?>
                            <?php
                            $no = 1 + (10 * (intval($pager->getCurrentPage() - 1))); // jika perPage = 10
                            foreach ($permintaan as $p): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d-m-Y', strtotime($p['tanggal'])); ?></td>
                                    <td><span class="badge badge-info">Produksi</span></td>
                                    <td><?= isset($p['barang']) ? count($p['barang']) : 0; ?> item</td>
                                    <td>
                                        <?php if (!empty($p['barang'])): ?>
                                            <ul class="pl-3 mb-0">
                                                <?php foreach ($p['barang'] as $b): ?>
                                                    <li><?= esc($b['nama']) . ' (' . (float) $b['jumlah'] . ' ' . esc($b['satuan']) . ')'; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <em class="text-muted">Tidak ada barang</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('manajemen-penjualan/permintaan/detail/' . $p['id']); ?>" class="btn btn-sm btn-info mb-1">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <form action="<?= base_url('manajemen-penjualan/hapus/' . $p['id']); ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus permintaan ini? Semua detail barang juga akan terhapus!');">
                                            <?= csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada data permintaan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div class="mt-3">
                <?= $pager->links('permintaan', 'bootstrap_full'); ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>