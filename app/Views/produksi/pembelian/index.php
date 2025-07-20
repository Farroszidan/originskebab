<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>

            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success'); ?></div>
            <?php endif; ?>

            <a href="<?= base_url('produksi/pembelian/create') ?>" class="btn btn-primary mb-3">
                <i class="fas fa-plus"></i> Tambah Pembelian
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>No. Nota</th>
                            <th>Tanggal</th>
                            <th>Pemasok</th>
                            <th>Item Dibeli</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($pembelian as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= esc($row['no_nota']) ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                                <td><?= esc($row['nama_pemasok']) ?></td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($row['item'] as $item): ?>
                                            <?php
                                            $satuan = strtolower($item['satuan']);
                                            $jumlah = $item['jumlah'];
                                            if ($satuan === 'kg') {
                                                $jumlah = $jumlah / 1000;
                                            } elseif ($satuan === 'liter') {
                                                $jumlah = $jumlah / 1000;
                                            }
                                            ?>
                                            <li><?= $item['nama'] ?> (<?= number_format($jumlah, 2) ?> <?= $item['satuan'] ?>)</li>
                                        <?php endforeach ?>
                                    </ul>
                                </td>
                                <td class="text-right">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <?php
                                    $status = isset($row['status_barang']) ? $row['status_barang'] : '-';
                                    if ($status === 'sudah_diterima') {
                                        echo '<span class="badge badge-success">Sudah Diterima</span>';
                                    } elseif ($status === 'belum_diterima') {
                                        echo '<span class="badge badge-warning">Belum Diterima</span>';
                                    } else {
                                        echo esc($status);
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('produksi/pembelian/detail/' . $row['id']) ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <?php if (isset($row['status_barang']) && $row['status_barang'] === 'belum_diterima'): ?>
                                        <a href="<?= base_url('produksi/pembelian/edit/' . $row['id']); ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('produksi/pembelian/delete/' . $row['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus pembelian ini?');">Hapus</a>
                                    <?php if (isset($row['status_barang']) && $row['status_barang'] === 'belum_diterima'): ?>
                                        <a href="<?= base_url('produksi/pembelian/update-status/' . $row['id'] . '/sudah_diterima') ?>" class="btn btn-success btn-sm" onclick="return confirm('Ubah status menjadi Sudah Diterima dan tambahkan ke persediaan?');">
                                            <i class="fas fa-check"></i> Terima Barang
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>