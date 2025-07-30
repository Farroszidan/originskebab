<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success'); ?></div>
    <?php endif; ?>

    <a href="<?= base_url('produksi/pembelian/tambah'); ?>" class="btn btn-primary mb-3">Tambah Pembelian</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Perintah Kerja</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pembelian as $p) : ?>
                <tr>
                    <td><?= $p['tanggal']; ?></td>
                    <td><?= $p['perintah_kerja_id'] ?? '-'; ?></td>
                    <td>Rp <?= number_format($p['total_harga'], 0, ',', '.'); ?></td>
                    <!-- Total diambil dari hasil perhitungan di tambah.php, pastikan format sama -->
                    <!-- Sudah menggunakan number_format tanpa desimal, ribuan titik -->
                    <td>
                        <a href="<?= base_url('produksi/pembelian/detail/' . $p['id']); ?>" class="btn btn-info btn-sm">Detail</a>
                        <?php if (in_groups('admin')) : ?>
                            <form action="<?= base_url('produksi/pembelian/hapus/' . $p['id']); ?>" method="post" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus pembelian ini?');">
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection(); ?>