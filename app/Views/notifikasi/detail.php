<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Detail Notifikasi</h1>

    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span><i class="fas fa-bell mr-2"></i> Notifikasi</span>
            <a href="<?= base_url('notifikasi/pesan_masuk') ?>" class="btn btn-sm btn-light">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
        <div class="card-body">

            <h5 class="card-title"><?= esc($notifikasi['isi']) ?></h5>
            <p class="text-muted mb-2">
                Diterima pada: <?= date('d M Y H:i', strtotime($notifikasi['created_at'])) ?>
            </p>

            <hr>

            <?php
            // Deteksi jenis notifikasi
            $isi = strtolower($notifikasi['isi']);
            if ($notifikasi['tipe'] === 'perintah_kerja'): ?>
                <h6 class="text-success">Detail Perintah Kerja Produksi</h6>
                <?php if (!empty($bsj)): ?>
                    <div class="mb-3">
                        <strong>Daftar BSJ yang harus diproduksi:</strong>
                        <ul>
                            <?php foreach ($bsj as $item): ?>
                                <li>
                                    <?= esc($item['nama'] ?? '-') ?>: <strong><?= esc($item['jumlah'] ?? 0) ?></strong>
                                    <?php if (!empty($item['totalBiaya'])): ?> (Biaya: Rp <?= number_format($item['totalBiaya'], 0, ',', '.') ?>)<?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">Data BSJ tidak tersedia.</div>
                <?php endif; ?>
                <?php if (!empty($perintahKerja)): ?>
                    <div class="mb-2">
                        <strong>Tanggal:</strong> <?= esc($perintahKerja['tanggal'] ?? '-') ?><br>
                        <strong>Jumlah Kulit:</strong> <?= esc($perintahKerja['jumlah_kulit'] ?? 0) ?><br>
                        <strong>Jumlah Ayam:</strong> <?= esc($perintahKerja['jumlah_ayam'] ?? 0) ?><br>
                        <strong>Jumlah Sapi:</strong> <?= esc($perintahKerja['jumlah_sapi'] ?? 0) ?><br>
                        <strong>Total Biaya:</strong> Rp <?= number_format($perintahKerja['total_biaya'] ?? 0, 0, ',', '.') ?><br>
                    </div>
                <?php endif; ?>
            <?php elseif (strpos($isi, 'permintaan') !== false): ?>
                <h6 class="text-primary">Detail Permintaan</h6>
                <p>Silakan cek halaman <a href="<?= base_url('permintaan') ?>">Permintaan</a> untuk menindaklanjuti.</p>

            <?php elseif ($data['jenis'] === 'pengiriman'): ?>
                <h6 class="text-warning">Detail Pengiriman</h6>
                <p><strong>Tanggal:</strong> <?= date('d-m-Y', strtotime($data['tanggal'])) ?></p>
                <p><strong>Jumlah Total:</strong> <?= $data['jumlah'] ?></p>
                <p><strong>Catatan:</strong> <?= $data['catatan'] ?></p>

                <?php if (!empty($data['detail_pengiriman'])): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['detail_pengiriman'] as $item): ?>
                                <tr>
                                    <td><?= esc($item['nama_barang']) ?></td>
                                    <td><?= esc($item['satuan']) ?></td>
                                    <td><?= esc($item['jumlah']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-danger">Tidak ada detail pengiriman.</p>
                <?php endif; ?>
            <?php elseif (strpos($isi, 'bukti pembelian') !== false): ?>
                <h6 class="text-info">Detail Bukti Pembelian</h6>
                <p>Bukti pembelian tersedia. Periksa di <a href="<?= base_url('bukti_pembelian') ?>">Bukti Pembelian</a>.</p>

            <?php else: ?>
                <h6 class="text-secondary">Informasi Umum</h6>
                <p>Jenis notifikasi tidak dikenali. Silakan cek manual di halaman terkait.</p>
            <?php endif; ?>

        </div>
    </div>

</div>

<?= $this->endSection() ?>