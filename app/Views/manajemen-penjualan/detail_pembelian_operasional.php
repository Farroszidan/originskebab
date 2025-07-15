<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h1 class="h3 text-gray-800 mb-0">Detail Pembelian</h1>
            <a href="<?= base_url('manajemen-penjualan/pembelian-operasional') ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">

            <!-- Info Umum -->
            <div class="mb-4">
                <h5><strong>Tanggal Pembelian:</strong> <?= date('d M Y', strtotime($pembelian['tanggal'])) ?></h5>
                <h5><strong>Total Pembelian:</strong> Rp <?= number_format($pembelian['total'], 0, ',', '.') ?></h5>
            </div>

            <!-- Daftar Barang -->
            <h5 class="mb-3">Daftar Barang:</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($pembelian['item'] as $item): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= esc($item['nama_barang']) ?></td>
                                <td><?= number_format($item['jumlah'], 0) ?></td>
                                <td class="text-right">Rp <?= number_format($item['total'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Bukti Pembelian -->
            <h5 class="mt-4">Bukti Pembelian:</h5>
            <?php if ($pembelian['bukti']): ?>
                <div class="text-center mt-2">
                    <img src="<?= base_url('uploads/bukti/' . $pembelian['bukti']) ?>" class="img-fluid rounded shadow-sm" alt="Bukti Pembelian" style="max-width: 400px;">
                    <div class="mt-3">
                        <a href="<?= base_url('uploads/bukti/' . $pembelian['bukti']) ?>" class="btn btn-success" download>
                            <i class="fas fa-download"></i> Download Bukti
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-muted">Tidak ada bukti pembelian yang diunggah.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>