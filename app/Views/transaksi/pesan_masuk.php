<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container-fluid d-flex">
    <!-- Sidebar -->
    <nav class="nav flex-column bg-light p-3" style="width: 220px; min-height: 90vh;">
        <h5 class="mb-4">Menu Pesan Masuk</h5>
        <a href="<?= base_url('transaksi/pesan_masuk/permintaan') ?>" class="nav-link <?= ($jenis === 'permintaan') ? 'active font-weight-bold' : '' ?>">Permintaan</a>
        <a href="<?= base_url('transaksi/pesan_masuk/pengiriman') ?>" class="nav-link <?= ($jenis === 'pengiriman') ? 'active font-weight-bold' : '' ?>">Pengiriman</a>
        <a href="<?= base_url('transaksi/pesan_masuk/bukti_pembelian') ?>" class="nav-link <?= ($jenis === 'bukti_pembelian') ? 'active font-weight-bold' : '' ?>">Bukti Pembelian</a>
    </nav>

    <!-- Main content -->
    <div class="flex-grow-1 p-3">
        <h3>Daftar <?= ucfirst(str_replace('_', ' ', $jenis)) ?></h3>
        <?php if (empty($data) || count($data) == 0): ?>
            <div class="alert alert-info">Tidak ada pesan <?= $jenis ?>.</div>
        <?php else: ?>
            <table class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>User ID</th>
                        <th>Isi Singkat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $item): ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td><?= $item['tanggal'] ?? $item['created_at'] ?></td>
                            <td><?= $item['user_id'] ?></td>
                            <td>
                                <?php
                                // Ambil ringkasan isi berdasarkan jenis
                                switch ($jenis) {
                                    case 'permintaan':
                                        echo esc(substr($item['catatan'] ?? '-', 0, 50));
                                        break;
                                    case 'pengiriman':
                                        echo esc(substr($item['catatan'] ?? '-', 0, 50));
                                        break;
                                    case 'bukti_pembelian':
                                        echo esc(substr($item['keterangan'] ?? '-', 0, 50));
                                        break;
                                    default:
                                        echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="<?= base_url("transaksi/detail/{$jenis}/{$item['id']}") ?>" class="btn btn-sm btn-primary">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php endif ?>
    </div>
</div>

<?= $this->endSection() ?>