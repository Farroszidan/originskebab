<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <a href="<?= base_url('admin/perintah-kerja/input'); ?>" class="btn btn-primary mb-3">+ Tambah Perintah Kerja</a>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Produksi</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Tipe</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($perintah_list)) : ?>
                        <?php
                        // Gabungkan berdasarkan tanggal dan admin_id
                        $grouped = [];
                        foreach ($perintah_list as $item) {
                            $key = $item['tanggal'] . '_' . ($item['admin_id'] ?? '0');
                            if (!isset($grouped[$key])) {
                                $grouped[$key] = [
                                    'tanggal' => $item['tanggal'],
                                    'admin_id' => $item['admin_id'] ?? null,
                                    'items' => []
                                ];
                            }
                            $grouped[$key]['items'][] = $item;
                        }
                        $no = 1;
                        foreach ($grouped as $group) :
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d-m-Y', strtotime($group['tanggal'])) ?></td>
                                <td>
                                    <?php foreach ($group['items'] as $i => $item) : ?>
                                        <?= esc($item['nama']) ?><?= $i < count($group['items']) - 1 ? '<br>' : '' ?>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php foreach ($group['items'] as $i => $item) : ?>
                                        <?= esc($item['jumlah']) ?><?= $i < count($group['items']) - 1 ? '<br>' : '' ?>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php foreach ($group['items'] as $i => $item) : ?>
                                        <?= esc($item['satuan']) ?><?= $i < count($group['items']) - 1 ? '<br>' : '' ?>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php foreach ($group['items'] as $i => $item) : ?>
                                        <?= strtoupper($item['tipe']) ?><?= $i < count($group['items']) - 1 ? '<br>' : '' ?>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php $firstItem = $group['items'][0]; ?>
                                    <a href="<?= base_url('admin/perintah-kerja/detail/' . $firstItem['id']); ?>" class="btn btn-sm btn-info mb-1">Detail</a>
                                    <a href="<?= base_url('admin/perintah-kerja/hapus/' . $firstItem['id']); ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Yakin ingin menghapus semua perintah kerja admin ini di tanggal ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data perintah kerja.</td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>