<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="h4 text-dark mb-0">Detail Pembelian</h1>
            <a href="<?= base_url('manajemen-penjualan/pembelian-operasional') ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card-body">

            <!-- Info Umum -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Tanggal Pembelian:</strong><br><?= date('d M Y', strtotime($pembelian['tanggal'])) ?></p>
                </div>
                <div class="col-md-6 text-md-right">
                    <p><strong>Total Pembelian:</strong><br><span class="text-primary">Rp <?= number_format($pembelian['total'], 0, ',', '.') ?></span></p>
                </div>
            </div>

            <!-- Daftar Barang -->
            <h5 class="mb-3">Daftar Barang</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm text-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nama Barang</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-right">Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($pembelian['item'] as $item): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= esc($item['nama_barang']) ?></td>
                                <td class="text-center"><?= number_format($item['jumlah'], 0) ?></td>
                                <td class="text-right">Rp <?= number_format($item['total'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Bukti Pembelian -->
            <h5 class="mt-5">Bukti Pembelian</h5>
            <?php if ($pembelian['bukti']): ?>
                <div class="card mt-3 p-3 text-center">
                    <img src="<?= base_url('uploads/bukti/' . $pembelian['bukti']) ?>" class="img-fluid rounded shadow-sm mb-3" alt="Bukti Pembelian" style="max-width: 400px;">
                    <a href="<?= base_url('uploads/bukti/' . $pembelian['bukti']) ?>" class="btn btn-success btn-sm" download>
                        <i class="fas fa-download"></i> Download Bukti
                    </a>
                </div>
            <?php else: ?>
                <p class="text-muted">Tidak ada bukti pembelian yang diunggah.</p>
            <?php endif; ?>

        </div>
    </div>
</div>

<?= $this->endSection(); ?>