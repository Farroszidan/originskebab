<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <ul class="navbar-nav ml-auto">

        <?php
        $notifikasiModel = new \App\Models\NotifikasiModel();
        $notifikasi = $notifikasiModel
            ->where('user_id', user_id())
            ->where('dibaca', 0)
            ->orderBy('created_at', 'DESC')
            ->findAll(5);

        $jumlahBelumDibaca = count($notifikasi);
        ?>

        <!-- Notifikasi Bell -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <?php if ($jumlahBelumDibaca > 0): ?>
                    <span class="badge badge-danger badge-counter"><?= $jumlahBelumDibaca ?></span>
                <?php endif; ?>
            </a>

            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">Notifikasi</h6>

                <?php if ($notifikasi): ?>
                    <?php foreach ($notifikasi as $notif): ?>
                        <a class="dropdown-item d-flex align-items-center"
                            href="<?= base_url('transaksi/detail/' . $notif['tipe'] . '/' . $notif['relasi_id']) ?>">
                            <div class="mr-3">
                                <div class="icon-circle bg-primary">
                                    <i class="fas fa-info text-white"></i>
                                </div>
                            </div>
                            <div>
                                <div class="small text-gray-500"><?= date('d M Y H:i', strtotime($notif['created_at'])) ?></div>
                                <span class="<?= $notif['dibaca'] == 0 ? 'font-weight-bold' : '' ?>">
                                    <?= esc($notif['isi']) ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="dropdown-item text-center small text-gray-500">Tidak ada notifikasi</div>
                <?php endif; ?>

                <a class="dropdown-item text-center small text-primary" href="<?= base_url('notifikasi/pesan_masuk') ?>">
                    Lihat Semua Pesan
                </a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- User Info -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-800 large"><?= esc(user()->username); ?></span>
                <img class="img-profile rounded-circle" src="<?= base_url('img/' . esc(user()->user_image)); ?>">
            </a>

            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>
</nav>