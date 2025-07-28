<!-- app/Views/admin/perintah_pengiriman/index.php -->
<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <a href="<?= base_url('admin/perintah-pengiriman/input') ?>" class="btn btn-primary mb-3">+ Tambah Perintah Pengiriman</a>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Outlet Tujuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pengiriman_list)): ?>
                    <?php foreach ($pengiriman_list as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= esc($row['tanggal']) ?></td>
                            <td><?= esc($row['keterangan']) ?></td>
                            <td>
                                <?php if (!empty($row['outlets'])): ?>
                                    <ul class="mb-0">
                                        <?php foreach ($row['outlets'] as $outlet): ?>
                                            <li><?= esc($outlet['nama_outlet'] ?? $outlet['outlet_id']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url('admin/perintah-pengiriman/detail/' . $row['id']) ?>" class="btn btn-info btn-sm">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data perintah pengiriman.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection(); ?>