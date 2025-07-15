<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Manajemen Komposisi BSJ</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"> <?= session()->getFlashdata('success'); ?> </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="font-weight-bold">Daftar Komposisi</span>
            <a href="<?= base_url('admin/komposisi/tambah'); ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Komposisi
            </a>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>BSJ</th>
                        <th>Bahan</th>
                        <th>Kategori</th>
                        <th>Jumlah (gram)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    $bsjSudahTampil = [];
                    foreach ($komposisi as $k): ?>
                        <?php $bsjNama = array_filter($bsj, fn($b) => $b['id'] == $k['id_bsj']); ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= esc(reset($bsjNama)['nama'] ?? '-'); ?></td>
                            <td><?= esc($k['nama_bahan']); ?></td>
                            <td><?= esc($k['kategori']); ?></td>
                            <td><?= esc($k['jumlah']); ?> gram</td>
                            <td>
                                <?php if (!in_array($k['id_bsj'], $bsjSudahTampil)) : ?>
                                    <a href="<?= base_url('admin/komposisi/edit/' . $k['id_bsj']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="<?= base_url('admin/komposisi/hapus/' . $k['id_bsj']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus semua komposisi untuk BSJ ini?')">Hapus</a>
                                    <?php $bsjSudahTampil[] = $k['id_bsj']; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>