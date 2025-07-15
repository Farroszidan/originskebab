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
            <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>

            <!-- Tombol Tambah BSJ -->
            <a href="<?= base_url('produksi/persediaan/tambah_bsj'); ?>" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Tambah BSJ</a>
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bsj)) : ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data Barang Setengah Jadi</td>
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
                                    <td>
                                        <a href="<?= base_url('produksi/persediaan/edit_bsj/' . $b['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="<?= base_url('produksi/persediaan/delete_bsj/' . $b['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus barang ini?');">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?= $this->endSection(); ?>