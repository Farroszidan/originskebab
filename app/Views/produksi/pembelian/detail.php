<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>No. Nota:</strong> <?= esc($pembelian['no_nota']) ?></p>
            <p><strong>Tanggal:</strong> <?= date('d-m-Y', strtotime($pembelian['tanggal'])) ?></p>
            <p><strong>Pemasok:</strong> <?= esc($pemasok['nama']) ?> (<?= esc($pemasok['kategori']) ?>)</p>
            <p><strong>Total:</strong> Rp <?= number_format($pembelian['total'], 0, ',', '.') ?></p>
            <?php if (!empty($pembelian['bukti_transaksi'])): ?>
                <p><strong>Bukti Transaksi:</strong>
                    <a href="<?= base_url('uploads/bukti_pembelian/' . $pembelian['bukti_transaksi']) ?>" target="_blank">
                        Lihat Bukti
                    </a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <h5>Detail Barang Dibeli</h5>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>No</th>
                <th>Nama Bahan</th>
                <th>Jumlah</th>
                <th>Satuan</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detail as $i => $row): ?>
                <?php $bahan = (new \App\Models\BahanModel())->find($row['bahan_id']);
                $satuan = strtolower($bahan['satuan']);
                $jumlah = $row['jumlah'];
                if ($satuan === 'kg' || $satuan === 'liter') {
                    $jumlah = $jumlah / 1000;
                }
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($bahan['nama']) ?></td>
                    <td><?= number_format($jumlah, 2) ?></td>
                    <td><?= esc($bahan['satuan']) ?></td>
                    <td>Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <a href="<?= base_url('produksi/pembelian') ?>" class="btn btn-secondary">Kembali</a>
</div>

<?= $this->endSection(); ?>