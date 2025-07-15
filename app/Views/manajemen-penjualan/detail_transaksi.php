<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container-fluid mt-4">
    <h4 class="mb-4">Detail Transaksi</h4>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>No Faktur:</strong> <?= esc($transaksi['no_faktur']) ?></p>
            <p><strong>Tanggal:</strong> <?= esc($transaksi['tgl_jual']) ?></p>
            <p><strong>Outlet:</strong> <?= esc($transaksi['nama_outlet']) ?></p>
            <p><strong>Kasir:</strong> <?= esc($transaksi['nama_kasir']) ?></p>
            <p><strong>Grand Total:</strong> Rp<?= number_format($transaksi['grand_total'], 0, ',', '.') ?></p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Kode Menu</th>
                    <th>Nama Menu</th>
                    <th>Add Ons</th>
                    <th>Extra</th>
                    <th>Qty</th>
                    <th>Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                $total = 0; ?>
                <?php foreach ($detail as $item): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($item['kode_menu']) ?></td>
                        <td><?= esc($item['nama_menu']) ?></td>
                        <td><?= esc($item['add_ons'] ?: '-') ?></td>
                        <td><?= esc($item['extra'] ?: '-') ?></td>
                        <td><?= esc($item['qty']) ?></td>
                        <td>Rp<?= number_format($item['total_harga'], 0, ',', '.') ?></td>
                    </tr>
                    <?php $total += $item['total_harga']; ?>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6" class="text-right">Total</th>
                    <th>Rp<?= number_format($total, 0, ',', '.') ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <a href="<?= base_url('manajemen-penjualan/daftar-transaksi') ?>" class="btn btn-secondary mt-3">Kembali</a>
</div>

<?= $this->endSection() ?>