<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h2>Detail Laporan Tanggal <?= esc($laporan['tanggal']) ?></h2>
    <hr>

    <h4>Rincian Menu Terjual</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Menu</th>
                <th>Nama Menu</th>
                <th>Harga</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Kategori</th>
                <th>Add Ons</th>
                <th>Extra</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rincianMenu as $item): ?>
                <tr>
                    <td><?= esc($item['kode_menu']) ?></td>
                    <td><?= esc($item['nama_menu']) ?></td>
                    <td><?= number_format($item['harga']) ?></td>
                    <td><?= $item['qty'] ?></td>
                    <td><?= number_format($item['total_harga']) ?></td>
                    <td><?= esc($item['kategori']) ?></td>
                    <td><?= esc($item['add_ons']) ?></td>
                    <td><?= esc($item['extra']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <tr>
        <h5>Rincian Potongan Per Transaksi</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No Faktur</th>
                    <th>Metode Pembayaran</th>
                    <th>Jenis Cashless</th>
                    <th>Total Transaksi</th>
                    <th>Potongan Cashless</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detailPotongan as $dp): ?>
                    <tr>
                        <td><?= esc($dp['no_faktur']) ?></td>
                        <td><?= esc($dp['metode_pembayaran']) ?></td>
                        <td><?= esc($dp['jenis_cashless']) ?></td>
                        <td><?= number_format($dp['grand_total']) ?></td>
                        <td><?= number_format($dp['potongan_cashless']) ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </tr>

    <h4 class="mt-4">Rincian Pengeluaran</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pengeluaran as $p): ?>
                <tr>
                    <td><?= esc($p['nama_barang']) ?></td>
                    <td><?= $p['jumlah'] ?></td>
                    <td><?= number_format($p['total']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="<?= base_url('manajemen-penjualan/laporanPerTanggal') ?>" class="btn btn-secondary mt-3">Kembali</a>
</div>

<?= $this->endSection(); ?>