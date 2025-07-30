<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h1 class="h3 text-gray-800 mb-0"><?= esc($tittle); ?></h1>
            <div class="d-flex gap-2">
                <?php if (in_groups('admin')): ?>
                    <a href="<?= base_url('produksi/persediaan/bsj/tambah'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah BSJ
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('produksi/persediaan/kartu/bsj'); ?>" class="btn btn-info">
                    <i class="fas fa-search"></i> Cek Kartu Persediaan
                </a>
            </div>
        </div>

        <!-- Tabel Daftar BSJ -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th>Harga</th>
                            <th>Saldo</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bsj)) : ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data Barang Setengah Jadi</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1;
                            foreach ($bsj as $b) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= esc($b['kode']); ?></td>
                                    <td><?= esc($b['nama']); ?></td>
                                    <td>
                                        <?php if (strtolower($b['satuan']) == 'kg' || strtolower($b['satuan']) == 'kilogram'): ?>
                                            <?= number_format($b['stok'] / 1000, 0, ',', '.') ?>
                                        <?php else: ?>
                                            <?= $b['stok'] ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($b['satuan']); ?></td>
                                    <td>Rp <?= isset($b['harga']) ? number_format($b['harga'], 0, ',', '.') : '0' ?></td>
                                    <td>Rp <?= isset($b['saldo']) ? number_format($b['saldo'], 0, ',', '.') : '0' ?></td>
                                    <td>
                                        <?php if (in_groups('admin')): ?>
                                            <a href="<?= base_url('produksi/persediaan/bsj/edit/' . $b['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="<?= base_url('produksi/persediaan/bsj/delete/' . $b['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus barang ini?');">Hapus</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>