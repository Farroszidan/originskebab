<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Notifikasi</h1>

    <form action="<?= base_url('notifikasi/mark-all') ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-primary mb-3">Tandai Semua Dibaca</button>
    </form>

    <div class="list-group">
        <?php if (count($notifikasi) > 0): ?>
            <?php foreach ($notifikasi as $n): ?>
                <?php
                // Tentukan link redirect sesuai tipe dan relasi id
                $link = base_url('notifikasi/baca/' . $n['id']); // fallback
                if ($n['tipe'] === 'permintaan' && !empty($n['permintaan_id'])) {
                    $link = base_url('permintaan/detail/' . $n['permintaan_id']);
                } elseif ($n['tipe'] === 'pengajuan' && !empty($n['pengajuan_id'])) {
                    $link = base_url('pengajuan/detail/' . $n['pengajuan_id']);
                } elseif ($n['tipe'] === 'pengiriman' && !empty($n['pengiriman_id'])) {
                    $link = base_url('pengiriman/detail/' . $n['pengiriman_id']);
                } elseif ($n['tipe'] === 'bukti_transfer' && !empty($n['bukti_transfer_id'])) {
                    $link = base_url('transfer/detail/' . $n['bukti_transfer_id']);
                }
                ?>
                <a href="<?= $link ?>"
                    class="list-group-item list-group-item-action <?= $n['dibaca'] == 0 ? 'font-weight-bold' : '' ?>">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-1"><?= esc(ucfirst($n['tipe'])) ?></h6>
                        <small><?= date('d M Y H:i', strtotime($n['created_at'])) ?></small>
                    </div>
                    <p class="mb-1"><?= esc($n['isi']) ?></p>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">Belum ada notifikasi.</div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>