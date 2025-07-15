<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-gray-800 font-weight-bold"><i class="fas fa-bell mr-1"></i> Pesan Masuk</h4>
        <a href="<?= base_url('manajemen-transaksi') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus-circle mr-1"></i> Buat Pesan
        </a>
    </div>

    <div class="card shadow-sm border-left-primary">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-envelope mr-1"></i> Daftar Notifikasi</h6>
            <a href="<?= base_url('notifikasi/tandai_semua') ?>" class="btn btn-sm btn-success">
                <i class="fas fa-check-circle mr-1"></i> Tandai Semua Dibaca
            </a>
        </div>

        <div class="card-body">
            <?php if (!empty($notifikasi)) : ?>
                <div class="list-group">
                    <?php foreach ($notifikasi as $notif) : ?>
                        <a href="<?= base_url('notifikasi/baca/' . $notif['id']) ?>"
                            class="list-group-item list-group-item-action shadow-sm rounded mb-2 
                                   <?= $notif['dibaca'] == 0 ? 'border-left-primary bg-light font-weight-bold' : '' ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-truncate" style="max-width: 80%;">
                                    <i class="fas fa-comment-alt text-primary mr-2"></i>
                                    <?= esc($notif['isi']) ?>
                                </div>
                                <small class="text-muted"><?= date('d M Y H:i', strtotime($notif['created_at'])) ?></small>
                            </div>
                            <?php if ($notif['dibaca'] == 0) : ?>
                                <span class="badge badge-primary badge-pill mt-2">Belum Dibaca</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <h5 class="mb-2">Tidak ada notifikasi masuk</h5>
                    <p class="text-muted">Semua notifikasi akan muncul di sini saat tersedia.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>