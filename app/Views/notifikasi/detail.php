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
            if (strpos($isi, 'permintaan') !== false): ?>
                <h6 class="text-primary">Detail Permintaan</h6>
                <p>Silakan cek halaman <a href="<?= base_url('permintaan') ?>">Permintaan</a> untuk menindaklanjuti.</p>

            <?php elseif (strpos($isi, 'pengiriman') !== false): ?>
                <h6 class="text-warning">Detail Pengiriman</h6>
                <p>Barang sedang dikirim. Lihat detail di <a href="<?= base_url('pengiriman') ?>">Pengiriman</a>.</p>

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