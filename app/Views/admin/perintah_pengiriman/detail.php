<!-- app/Views/admin/perintah_pengiriman/detail.php -->
<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>
    <a href="<?= base_url('admin/perintah-pengiriman') ?>" class="btn btn-secondary mb-3">Kembali</a>
    <div class="card mb-3">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Tanggal</dt>
                <dd class="col-sm-9"> <?= esc($pengiriman['tanggal']) ?> </dd>
                <dt class="col-sm-3">Keterangan</dt>
                <dd class="col-sm-9"> <?= esc($pengiriman['keterangan']) ?> </dd>
            </dl>
            <h5>Outlet Tujuan</h5>
            <ul>
                <?php foreach ($outlets as $outlet): ?>
                    <li><strong><?= esc($outlet['nama_outlet'] ?? $outlet['outlet_id']) ?></strong> <?= esc($outlet['keterangan']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <h5>Detail Item yang Dikirim</h5>
    <?php
    // Group detail by outlet
    $grouped = [];
    if (!empty($detail)) {
        foreach ($detail as $row) {
            $outletKey = $row['nama_outlet'] ?? $row['perintah_pengiriman_outlet_id'];
            $grouped[$outletKey][] = $row;
        }
    }
    ?>
    <?php if (!empty($grouped)): ?>
        <?php foreach ($grouped as $outletName => $items): ?>
            <div class="mb-4">
                <h6 class="font-weight-bold">Outlet: <?= esc($outletName) ?></h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tipe</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $i => $row): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($row['tipe']) ?></td>
                                    <td><?= esc($row['nama_barang']) ?></td>
                                    <td><?= esc($row['jumlah']) ?></td>
                                    <td><?= esc($row['satuan']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">Tidak ada detail item.</div>
    <?php endif; ?>
</div>
<?= $this->endSection(); ?>