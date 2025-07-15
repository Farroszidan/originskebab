<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Detail Pengiriman Barang</h1>
    <div class="card mb-3">
        <div class="card-header bg-info text-white">
            <strong>Informasi Pengiriman</strong>
        </div>
        <div class="card-body">
            <table class="table table-borderless">
                <tr>
                    <th width="200">Tanggal</th>
                    <td>: <?= isset($pengiriman['tanggal']) ? date('d-m-Y', strtotime($pengiriman['tanggal'])) : '-'; ?></td>
                </tr>
                <tr>
                    <th>Outlet Tujuan</th>
                    <td>: <?= esc($outlet['nama_outlet'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Catatan</th>
                    <td>: <?= esc($pengiriman['catatan'] ?? '-'); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <strong>Detail Barang Dikirim</strong>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($barang)): ?>
                        <?php $no = 1;
                        foreach ($barang as $b): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= esc($b['nama']); ?></td>
                                <td><?= esc($b['tipe'] ?? '-'); ?></td>
                                <td><?= esc($b['jumlah']); ?></td>
                                <td><?= esc($b['satuan']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada detail barang.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <a href="<?= base_url('produksi/pengiriman'); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<?= $this->endSection(); ?>