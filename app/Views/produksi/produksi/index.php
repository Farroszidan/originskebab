<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success'); ?>
    </div>
<?php endif; ?>


<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Daftar Produksi</h1>

    <div class="card mb-3">
        <div class="card-header">
            <a href="<?= base_url('produksi/produksi/input'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Produksi
            </a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>BSJ</th>
                        <th>Jumlah</th>
                        <th>Total Biaya</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    foreach ($produksi as $p) : ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= date('d-m-Y', strtotime($p['tanggal'])); ?></td>
                            <td><?= esc($p['bsj_nama']); ?></td>
                            <td><?= esc($p['jumlah']); ?> <?= esc($p['bsj_satuan']); ?></td>
                            <td>Rp <?= number_format($p['total_biaya'], 0, ',', '.'); ?></td>
                            <td><?= ucfirst($p['status']); ?></td>
                            <td>
                                <a href="<?= base_url('produksi/produksi/detail/' . $p['id']); ?>" class="btn btn-info btn-sm">Detail</a>
                                <?php if ($p['status'] == 'draft') : ?>
                                    <a href="<?= base_url('produksi/produksi/updateStatus/' . $p['id'] . '/proses'); ?>" class="btn btn-warning btn-sm">Mulai Produksi</a>
                                    <a href="<?= base_url('produksi/produksi/updateStatus/' . $p['id'] . '/dibatalkan'); ?>" class="btn btn-secondary btn-sm" onclick="return confirm('Batalkan produksi ini?');">Batalkan</a>
                                <?php elseif ($p['status'] == 'proses') : ?>
                                    <a href="<?= base_url('produksi/produksi/updateStatus/' . $p['id'] . '/selesai'); ?>" class="btn btn-success btn-sm" onclick="return confirm('Selesaikan produksi ini?');">Selesaikan</a>
                                    <a href="<?= base_url('produksi/produksi/updateStatus/' . $p['id'] . '/dibatalkan'); ?>" class="btn btn-secondary btn-sm" onclick="return confirm('Batalkan produksi ini?');">Batalkan</a>
                                <?php endif; ?>
                                <a href="<?= base_url('produksi/produksi/hapus/' . $p['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus produksi ini?');">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>