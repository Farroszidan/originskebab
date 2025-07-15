<?= $this->extend('templates/index_templates_general'); ?>

<?= $this->section('page-content') ?>
<div class="container mt-4">
    <h2 class="mb-4">Semua Notifikasi</h2>

    <?php if (empty($notifikasis)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-bell-slash fa-2x mb-2"></i>
            <p>Tidak ada notifikasi.</p>
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($notifikasis as $notif): ?>
                <a href="<?= base_url('notifikasi/baca/' . $notif['id']) ?>"
                    class="list-group-item list-group-item-action flex-column align-items-start <?= $notif['dibaca'] == 0 ? 'list-group-item-primary' : '' ?>">

                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">
                            <?= esc($notif['isi']) ?>
                        </h5>
                        <small class="text-muted"><?= date('d M Y H:i', strtotime($notif['created_at'])) ?></small>
                    </div>

                    <?php if ($notif['dibaca'] == 0): ?>
                        <small class="badge badge-pill badge-info">Baru</small>
                    <?php else: ?>
                        <small class="text-secondary">Sudah dibaca</small>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>