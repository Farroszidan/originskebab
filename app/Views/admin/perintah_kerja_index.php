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
                        <th>Nama BSJ</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($perintah_kerja_list)): ?>
                        <?php $no = 1;
                        foreach ($perintah_kerja_list as $pk): ?>
                            <?php
                            $bsjList = [];
                            if (!empty($pk['bsj'])) {
                                $decoded = json_decode($pk['bsj'], true);
                                if (is_array($decoded)) {
                                    $bsjList = $decoded;
                                }
                            }

                            // Filter hanya data bertipe BSJ
                            $bsjItems = array_filter($bsjList, function ($item) {
                                return ($item['tipe'] ?? 'bsj') === 'bsj';
                            });


                            // Jika ada BSJ tampilkan baris untuk masing-masing item
                            if (!empty($bsjItems)) {
                                foreach ($bsjItems as $item) { ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= date('d-m-Y', strtotime($pk['tanggal'])); ?></td>
                                        <td><?= esc($item['nama']) ?></td>
                                        <td><?= esc($item['jumlah']) . ' ' . esc($item['satuan']) ?></td>
                                        <td>
                                            <a href="<?= base_url('admin/perintah-kerja/detail/' . $pk['id']); ?>" class="btn btn-info btn-sm">Detail</a>
                                            <a href="<?= base_url('admin/perintah-kerja/hapus/' . $pk['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus perintah kerja ini?');">Hapus</a>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d-m-Y', strtotime($pk['tanggal'])); ?></td>
                                    <td colspan="2">Tidak ada BSJ</td>
                                    <td>
                                        <a href="<?= base_url('admin/perintah-kerja/detail/' . $pk['id']); ?>" class="btn btn-info btn-sm">Detail</a>
                                        <a href="<?= base_url('admin/perintah-kerja/hapus/' . $pk['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus perintah kerja ini?');">Hapus</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Belum ada data perintah kerja.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>