<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>

    <div class="card mb-4">
        <div class="card-header">Informasi Pembelian</div>
        <div class="card-body">
            <p><b>Tanggal:</b> <?= $pembelian['tanggal']; ?></p>
            <p><b>Total:</b> Rp <?= number_format($pembelian['total_harga'], 0, ',', '.'); ?></p>
            <?php if (isset($pembelian['bukti_transaksi']) && $pembelian['bukti_transaksi']) : ?>
                <p><b>Bukti Transaksi:</b> <a href="<?= base_url('uploads/bukti_transaksi/' . $pembelian['bukti_transaksi']); ?>" target="_blank">Lihat</a></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Detail Bahan Dibeli</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama Bahan</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                        <th>Pemasok</th>
                        <th>Tipe Pembayaran</th>
                        <th>Bukti Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detail as $d) : ?>
                        <tr>
                            <td><?= $d['nama_bahan']; ?></td>
                            <td><?= $d['kategori']; ?></td>
                            <td><?= $d['jumlah']; ?></td>
                            <td><?= $d['satuan']; ?></td>
                            <td>Rp <?= number_format($d['harga_satuan'], 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($d['subtotal'], 0, ',', '.'); ?></td>
                            <td><?= $d['nama_pemasok'] ?? '-'; ?></td>
                            <td><?= ucfirst($d['tipe_pembayaran'] ?? '-'); ?></td>
                            <td>
                                <?php if (!empty($d['bukti_transaksi'])) : ?>
                                    <a href="<?= base_url('uploads/bukti_transaksi/' . $d['bukti_transaksi']); ?>" target="_blank">Lihat</a>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="<?= base_url('produksi/pembelian'); ?>" class="btn btn-secondary mt-3">Kembali</a>
</div>

<?= $this->endSection(); ?>