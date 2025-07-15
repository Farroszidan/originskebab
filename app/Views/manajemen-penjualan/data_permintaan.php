<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Daftar Permintaan Barang ke Produksi</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success'); ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error'); ?>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <form method="get" class="form-inline">
                <div class="form-group mr-2">
                    <label for="start_date" class="mr-2">Mulai</label>
                    <input type="date" name="start_date" id="start_date" class="form-control"
                        value="<?= esc($filter['start_date'] ?? '') ?>">
                </div>

                <div class="form-group mr-2">
                    <label for="end_date" class="mr-2">Selesai</label>
                    <input type="date" name="end_date" id="end_date" class="form-control"
                        value="<?= esc($filter['end_date'] ?? '') ?>">
                </div>

                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </form>

            <div class="mb-2">
                <a href="<?= base_url('manajemen-penjualan/formPermintaan'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Input Permintaan
                </a>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Tujuan</th>
                        <th>Jumlah Item</th>
                        <th>Daftar Barang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($permintaan)): ?>
                        <?php $no = 1;
                        foreach ($permintaan as $p): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= date('d-m-Y', strtotime($p['tanggal'])); ?></td>
                                <td>Produksi</td>
                                <td><?= isset($p['barang']) ? count($p['barang']) : 0; ?> item</td>
                                <td>
                                    <?php if (!empty($p['barang'])): ?>
                                        <ul class="mb-0 pl-3">
                                            <?php foreach ($p['barang'] as $b): ?>
                                                <li><?= esc($b['nama']) . ' (' . (float) $b['jumlah'] . ' ' . esc($b['satuan']) . ')'; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <em>Tidak ada barang</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('manajemen-penjualan/permintaan/detail/' . $p['id']); ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <form action="<?= base_url('manajemen-penjualan/hapus/' . $p['id']); ?>" method="post" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus permintaan ini? Semua detail barang juga akan terhapus!');">
                                        <?= csrf_field(); ?>
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data permintaan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>