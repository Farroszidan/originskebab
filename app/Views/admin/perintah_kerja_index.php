<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Daftar Perintah Kerja Produksi BSJ</h1>
    <a href="<?= base_url('admin/perintah-kerja/input'); ?>" class="btn btn-primary mb-3">Tambah Perintah Kerja</a>
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Jumlah Kulit</th>
                        <th>Jumlah Ayam</th>
                        <th>Jumlah Sapi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($perintah_kerja_list)): ?>
                        <?php $no = 1;
                        foreach ($perintah_kerja_list as $pk): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= date('d-m-Y', strtotime($pk['tanggal'])); ?></td>
                                <td><?= isset($pk['jumlah_kulit']) ? esc($pk['jumlah_kulit']) : '-'; ?></td>
                                <td><?= isset($pk['jumlah_ayam']) ? esc($pk['jumlah_ayam']) : '-'; ?></td>
                                <td><?= isset($pk['jumlah_sapi']) ? esc($pk['jumlah_sapi']) : '-'; ?></td>
                                <td><?= isset($pk['status']) ? esc($pk['status']) : '-'; ?></td>
                                <td>
                                    <a href="<?= base_url('admin/perintah-kerja/detail/' . $pk['id']); ?>" class="btn btn-info btn-sm">Detail</a>
                                    <a href="<?= base_url('admin/perintah-kerja/hapus/' . $pk['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus perintah kerja ini?');">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data perintah kerja.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>