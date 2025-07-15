<?php if (empty($notifikasi)): ?>
    <li class="dropdown-item text-center">Tidak ada notifikasi baru</li>
<?php else: ?>
    <?php foreach ($notifikasi as $notif): ?>
        <li class="dropdown-item">
            <a href="<?= base_url("notifikasi/detail/" . $notif['id']) ?>">
                <strong><?= esc($notif['jenis']) ?></strong><br>
                <?php if (!empty($notif['detail'])): ?>
                    <?php
                    // Contoh isi notifikasi berdasarkan tipe
                    switch ($notif['jenis']) {
                        case 'permintaan':
                            echo 'Permintaan tanggal ' . date('d-m-Y', strtotime($notif['detail']['tanggal'])) . ', catatan: ' . esc($notif['detail']['catatan']);
                            break;
                        case 'pengiriman':
                            echo 'Pengiriman tanggal ' . date('d-m-Y', strtotime($notif['detail']['tanggal'])) . ', status: ' . esc($notif['detail']['status']);
                            break;
                        case 'pengajuan':
                            echo 'Pengajuan ' . esc($notif['detail']['jenis_pengajuan']) . ', status: ' . esc($notif['detail']['status']);
                            break;
                        case 'bukti_transfer':
                            echo 'Bukti Transfer tanggal ' . date('d-m-Y', strtotime($notif['detail']['tanggal']));
                            break;
                        default:
                            echo 'Notifikasi baru';
                    }
                    ?>
                <?php else: ?>
                    Notifikasi baru
                <?php endif; ?>
                <br><small class="text-muted"><?= date('d-m-Y H:i', strtotime($notif['created_at'])) ?></small>
            </a>
        </li>
    <?php endforeach; ?>
<?php endif; ?>