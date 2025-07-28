<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Detail Produksi</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"> <?= session()->getFlashdata('success'); ?> </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>No Produksi:</strong> <?= esc($produksi['no_produksi']) ?></p>
            <p><strong>Tanggal:</strong> <?= date('d-m-Y', strtotime($produksi['tanggal'])) ?></p>
            <p><strong>BSJ:</strong> <?= esc($produksi['nama_bsj']) ?> (<?= esc($produksi['jumlah']) ?> <?= esc($produksi['satuan']) ?>)</p>
            <p><strong>Total Biaya Produksi:</strong> Rp <?= number_format($produksi['total_biaya'], 0, ',', '.') ?></p>
        </div>
    </div>

    <h5>Rincian Penggunaan Bahan & Biaya</h5>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>No</th>
                <th>Kategori</th>
                <th>Nama</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            foreach ($detail as $item): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= ucfirst($item['kategori']) ?></td>
                    <td>
                        <?php if ($item['kategori'] == 'baku' || $item['kategori'] == 'penolong'): ?>
                            <?= esc($item['nama_bahan']) ?>
                        <?php else: ?>
                            <?= esc($item['nama_biaya'] ?? '-') ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($item['kategori'] == 'baku' || $item['kategori'] == 'penolong'): ?>
                            <?php $jumlah_kg = $item['jumlah'] / 1000; ?>
                            <?= number_format($jumlah_kg, 2) ?> kg
                        <?php else: ?>
                            <?= esc($item['jumlah']) ?>
                        <?php endif; ?>
                    </td>
                    <td>Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                    <td>
                        <?php if ($item['kategori'] == 'baku' || $item['kategori'] == 'penolong'): ?>
                            Rp <?= number_format(($item['jumlah'] / 1000) * $item['harga_satuan'], 0, ',', '.') ?>
                        <?php else: ?>
                            Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="<?= base_url('produksi/produksi/daftar'); ?>" class="btn btn-secondary">Kembali</a>
</div>

<?= $this->endSection(); ?>