<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h1 class="h3 text-gray-800 mb-0"><?= esc($tittle); ?></h1>
            <div class="d-flex gap-2">
                <a href="<?= base_url('produksi/persediaan/create'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Bahan
                </a>
                <a href="<?= base_url('produksi/persediaan/kartu/bahan'); ?>" class="btn btn-info">
                    <i class="fas fa-search"></i> Cek Kartu Persediaan
                </a>
            </div>
        </div>

        <!-- Tabel Daftar Bahan -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Jenis</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th>Harga per Satuan</th>
                            <th>Saldo</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bahan)) : ?>
                            <tr>
                                <td colspan="9" class="text-center">Belum ada data bahan</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1;
                            foreach ($bahan as $b) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= esc($b['kode']); ?></td>
                                    <td><?= esc($b['nama']); ?></td>
                                    <td><?= esc($b['kategori']); ?></td>
                                    <td><?= esc($b['jenis']); ?></td>
                                    <td>
                                        <?php
                                        $satuan = strtolower($b['satuan']);
                                        if ($satuan === 'kg' || $satuan === 'liter') {
                                            echo esc($b['stok'] / 1000);
                                        } else {
                                            echo esc($b['stok']);
                                        }
                                        ?>
                                    </td>
                                    <td><?= esc($b['satuan']); ?></td>
                                    <td>Rp <?= number_format($b['harga_satuan'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($b['saldo'], 0, ',', '.'); ?></td>
                                    <td>
                                        <a href="<?= base_url('produksi/persediaan/edit/' . $b['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="<?= base_url('produksi/persediaan/delete/' . $b['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus bahan ini?');">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>