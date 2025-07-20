<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= base_url('dashboard'); ?>">
        <div class="sidebar-brand-icon rotate-n-15">
            <img src="<?= base_url('img/logo_sidebar.png'); ?>" class="rounded-circle" alt="logo-sidebar">
        </div>
        <div class="sidebar-brand-text mx-3" style="line-height: 1; vertical-align: middle;">
            SIOK <span style="display: inline-block; transform: translateY(-1px);">|</span>
            <?php if (in_groups('admin')) : ?>
                Admin
            <?php elseif (in_groups('penjualan')) : ?>
                Penjualan
            <?php elseif (in_groups('produksi')) : ?>
                Produksi
            <?php elseif (in_groups('keuangan')) : ?>
                Keuangan
            <?php endif; ?>
        </div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Role Penjualan -->
    <?php if (in_groups('penjualan')) : ?>
        <!-- Nav Item - Dashboard -->
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('dashboard'); ?>">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('notifikasi/pesan_masuk'); ?>">
                <i class="fas fa-fw fa-envelope"></i>
                <span>Pesan Masuk</span></a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            Manajemen
        </div>

        <!-- Nav Item - Penjualan -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                aria-expanded="true" aria-controls="collapseTwo">
                <i class="fas fa-fw fa-cog"></i>
                <span>Penjualan</span>
            </a>
            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/inputtransaksi'); ?>">Input Transaksi</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/daftar-transaksi'); ?>">Daftar Transaksi</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/persediaanOutlet'); ?>">Persediaan Outlet</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/pembelian-operasional'); ?>">Pembelian Operasional</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/input-shift'); ?>">Input Jadwal Shift</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/data-shift'); ?>">Data Shift</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/permintaan'); ?>">
                        Permintaan ke Produksi
                    </a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLaporan"
                aria-expanded="false" aria-controls="collapseLaporan">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Laporan</span>
            </a>
            <div id="collapseLaporan" class="collapse" aria-labelledby="headingLaporan" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/input-laporan-shift'); ?>">
                        <i class="fas fa-fw fa-pencil-alt text-primary mr-1"></i> Input Laporan Shift
                    </a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/laporanpenjualan'); ?>">
                        <i class="fas fa-fw fa-file-invoice-dollar text-success mr-1"></i> Laporan Penjualan
                    </a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/hppPenjualan'); ?>">
                        <i class="fas fa-fw fa-balance-scale text-warning mr-1"></i> HPP Penjualan
                    </a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBTKL">
                <i class="fas fa-fw fa-user-check"></i>
                <span>BTKL</span>
            </a>
            <div id="collapseBTKL" class="collapse" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/input-shift'); ?>">Input Jadwal Shift</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/data-shift'); ?>">Data Shift</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/btkl'); ?>">Perhitungan Gaji</a>
                </div>
            </div>
        </li>

        <!-- Nav Item - Manajemen TO -->
        <!-- <li class="nav-item">
            <a class="nav-link" href="<?= base_url('manajemen-transaksi'); ?>">
                <i class="fas fa-fw fa-folder"></i>
                <span>Manajemen TO</span>
            </a>
        </li> -->

        <!-- Nav Item - Master -->
        <!-- <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMasterPenjualan"
                aria-expanded="true" aria-controls="collapseMasterPenjualan">
                <i class="fas fa-fw fa-cog"></i>
                <span>Master</span>
            </a>
            <div id="collapseMasterPenjualan" class="collapse" aria-labelledby="headingMasterPenjualan" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/daftar-transaksi'); ?>">Daftar Transaksi</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/daftarMenu'); ?>">Daftar Menu</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/stokOutlet'); ?>">Daftar Stok Outlet</a>
                </div>
            </div>
        </li> -->
    <?php endif; ?>

    <!-- Role Produksi -->
    <?php if (in_groups('produksi')) : ?>
        <!-- Nav Item - Dashboard -->
        <li class="nav-item">
            <a class="nav-link" href="/dashboard">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('notifikasi/pesan_masuk'); ?>">
                <i class="fas fa-fw fa-envelope"></i>
                <span>Pesan Masuk</span></a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            Manajemen
        </div>

        <!-- Nav Item - Utilities Collapse Menu -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseProduksi"
                aria-expanded="true" aria-controls="collapseProduksi">
                <i class="fas fa-fw fa-wrench"></i>
                <span>Produksi</span>
            </a>
            <div id="collapseProduksi" class="collapse" aria-labelledby="headingProduksi"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Pembelian:</h6>
                    <a class="collapse-item" href="<?= base_url('produksi/pembelian'); ?>">Pembelian</a>
                    <h6 class="collapse-header">Produksi:</h6>
                    <a class="collapse-item" href="<?= base_url('produksi/produksi/daftar'); ?>">Produksi</a>
                    <a class="collapse-item" href="<?= base_url('produksi/hpp'); ?>">HPP BSJ</a>
                    <h6 class="collapse-header">Persediaan:</h6>
                    <a class="collapse-item" href="<?= base_url('produksi/persediaan'); ?>">Bahan</a>
                    <a class="collapse-item" href="<?= base_url('produksi/persediaan/bsj'); ?>">Barang Setengah Jadi</a>
                    <a class="collapse-item" href="<?= base_url('produksi/pengiriman'); ?>">Pengiriman</a>
                </div>
            </div>
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLaporan"
                aria-expanded="true" aria-controls="collapseLaporan">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Laporan</span>
            </a>
            <div id="collapseLaporan" class="collapse" aria-labelledby="headingProduksi"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Laporan:</h6>
                    <a class="collapse-item" href="<?= base_url('produksi/laporan'); ?>">laporan</a>
                </div>
            </div>
        </li>
    <?php endif; ?>

    <!-- Role Keuangan -->
    <?php if (in_groups('keuangan')) : ?>
        <!-- Nav Item - Dashboard -->
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('dashboard/index'); ?>">
                <i class="fas fa-fw fa-tachometer-alt text-primary"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Nav Item - Pesan Masuk -->
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('notifikasi/pesan_masuk'); ?>">
                <i class="fas fa-fw fa-inbox text-info"></i>
                <span>Pesan Masuk</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            Manajemen
        </div>

        <!-- Nav Item - Penjualan Collapse -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePenjualan"
                aria-expanded="false" aria-controls="collapsePenjualan">
                <i class="fas fa-fw fa-shopping-cart text-warning"></i>
                <span>Penjualan</span>
            </a>
            <div id="collapsePenjualan" class="collapse" aria-labelledby="headingPenjualan" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Transaksi:</h6>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/daftar-transaksi'); ?>">
                        <i class="fas fa-fw fa-list-alt mr-1 text-success"></i> Daftar Transaksi
                    </a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/data-shift'); ?>">
                        <i class="fas fa-fw fa-table mr-1 text-dark"></i> Data Shift
                    </a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/btkl'); ?>">
                        <i class="fas fa-fw fa-user-clock mr-1 text-danger"></i> Perhitungan Gaji
                    </a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/laporanpenjualan'); ?>">
                        <i class="fas fa-fw fa-file-invoice-dollar mr-1 text-primary"></i> Laporan Penjualan
                    </a>
                </div>
            </div>
        </li>

        <!-- Nav Item - Keuangan Collapse -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseKeuangan"
                aria-expanded="false" aria-controls="collapseKeuangan">
                <i class="fas fa-fw fa-wallet text-danger"></i>
                <span>Keuangan</span>
            </a>
            <div id="collapseKeuangan" class="collapse" aria-labelledby="headingKeuangan" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Laporan Keuangan:</h6>
                    <a class="collapse-item" href="<?= base_url('/keuangan/akun') ?>">
                        <i class="fas fa-fw fa-user-circle mr-1 text-primary"></i> Daftar Akun
                    </a>
                    <a class="collapse-item" href="<?= base_url('/keuangan/index') ?>">
                        <i class="fas fa-fw fa-book mr-1 text-primary"></i> Jurnal Umum
                    </a>
                    <a class="collapse-item" href="<?= base_url('keuangan/laporan_utang'); ?>">
                        <i class="fas fa-fw fa-file-invoice mr-1 text-warning"></i> Laporan Utang
                    </a>
                    <a class="collapse-item" href="<?= base_url('keuangan/laporan_piutang'); ?>">
                        <i class="fas fa-fw fa-file-invoice-dollar mr-1 text-success"></i> Laporan Piutang
                    </a>

                    <a class="collapse-item" href="<?= base_url('/keuangan/neraca_saldo') ?>">
                        <i class="fas fa-fw fa-balance-scale mr-1 text-success"></i> Neraca Saldo
                    </a>
                    <a class="collapse-item" href="<?= base_url('keuangan/laba_rugi'); ?>">
                        <i class="fas fa-fw fa-chart-line mr-1 text-info"></i> Laporan Laba Rugi
                    </a>
                    <a class="collapse-item" href="<?= base_url('keuangan/laporan_perubahan_ekuitas'); ?>">
                        <i class="fas fa-fw fa-exchange-alt mr-1 text-warning"></i> Laporan Ekuitas
                    </a>
                    <a class="collapse-item" href="<?= base_url('keuangan/laporan_neraca'); ?>">
                        <i class="fas fa-fw fa-balance-scale-left mr-1 text-danger"></i> Laporan Neraca
                    </a>
                    <a class="collapse-item" href="<?= base_url('keuangan/arus_kas'); ?>">
                        <i class="fas fa-fw fa-money-bill-wave mr-1 text-danger"></i> Laporan Arus Kas
                    </a>
                </div>
            </div>
        </li>
    <?php endif; ?>



    <!-- Role Admin -->
    <?php if (in_groups('admin')) : ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('dashboard'); ?>">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('notifikasi/pesan_masuk'); ?>">
                <i class="fas fa-fw fa-inbox"></i>
                <span>Pesan Masuk</span></a>
        </li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Manajemen</div>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                aria-expanded="true" aria-controls="collapseTwo">
                <i class="fas fa-fw fa-cog"></i>
                <span>Penjualan</span>
            </a>
            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/inputtransaksi'); ?>">Input Transaksi</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/daftar-transaksi'); ?>">Daftar Transaksi</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/persediaanOutlet'); ?>">Persediaan Outlet</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/pembelian-operasional'); ?>">Pembelian Operasional</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/input-shift'); ?>">Input Jadwal Shift</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/data-shift'); ?>">Data Shift</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/permintaan'); ?>">
                        Permintaan ke Produksi
                    </a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <!-- <a class="nav-link" href="<?= base_url('manajemen-penjualan/laporanHarian'); ?>">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Laporan Harian</span>
            </a> -->
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseProduksi"
                aria-expanded="true" aria-controls="collapseProduksi">
                <i class="fas fa-fw fa-wrench"></i>
                <span>Produksi</span>
            </a>
            <div id="collapseProduksi" class="collapse" aria-labelledby="headingProduksi"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Pembelian:</h6>
                    <a class="collapse-item" href="<?= base_url('produksi/pembelian'); ?>">Pembelian</a>
                    <h6 class="collapse-header">Produksi:</h6>
                    <a class="collapse-item" href="<?= base_url('produksi/produksi/daftar'); ?>">Produksi</a>
                    <a class="collapse-item" href="<?= base_url('produksi/hpp'); ?>">HPP BSJ</a>
                    <h6 class="collapse-header">Persediaan:</h6>
                    <a class="collapse-item" href="<?= base_url('produksi/persediaan'); ?>">Bahan</a>
                    <a class="collapse-item" href="<?= base_url('produksi/persediaan/bsj'); ?>">Barang Setengah Jadi</a>
                    <a class="collapse-item" href="<?= base_url('produksi/pengiriman'); ?>">Pengiriman</a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseKeuangan"
                aria-expanded="false" aria-controls="collapseKeuangan">
                <i class="fas fa-fw fa-wallet text-danger"></i>
                <span>Keuangan</span>
            </a>
            <div id="collapseKeuangan" class="collapse" aria-labelledby="headingKeuangan" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Laporan Keuangan:</h6>
                    <a class="collapse-item" href="<?= base_url('/keuangan/akun') ?>">
                        <i class="fas fa-fw fa-user-circle mr-1 text-primary"></i> Daftar Akun
                    </a>
                    <a class="collapse-item" href="<?= base_url('/keuangan/index') ?>">
                        <i class="fas fa-fw fa-book mr-1 text-primary"></i> Jurnal Umum
                    </a>
                    <a class="collapse-item" href="<?= base_url('keuangan/laporan_utang'); ?>">
                        <i class="fas fa-fw fa-file-invoice mr-1 text-warning"></i> Laporan Utang
                    </a>
                    <a class="collapse-item" href="<?= base_url('keuangan/laporan_piutang'); ?>">
                        <i class="fas fa-fw fa-file-invoice-dollar mr-1 text-success"></i> Laporan Piutang
                    </a>

                    <a class="collapse-item" href="<?= base_url('/keuangan/neraca_saldo') ?>">
                        <i class="fas fa-fw fa-balance-scale mr-1 text-success"></i> Neraca Saldo
                    </a>
                    <a class="collapse-item" href="<?= base_url('keuangan/laba_rugi'); ?>">
                        <i class="fas fa-fw fa-chart-line mr-1 text-info"></i> Laporan Laba Rugi
                    </a>
                    <a class="collapse-item" href="<?= base_url('keuangan/laporan_perubahan_ekuitas'); ?>">
                        <i class="fas fa-fw fa-exchange-alt mr-1 text-warning"></i> Laporan Ekuitas
                    </a>
                    <a class="collapse-item" href="<?= base_url('keuangan/laporan_neraca'); ?>">
                        <i class="fas fa-fw fa-balance-scale-left mr-1 text-danger"></i> Laporan Neraca
                    </a>
                    <a class="collapse-item" href="<?= base_url('keuangan/arus_kas'); ?>">
                        <i class="fas fa-fw fa-money-bill-wave mr-1 text-danger"></i> Laporan Arus Kas
                    </a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLaporan"
                aria-expanded="false" aria-controls="collapseLaporan">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Laporan</span>
            </a>
            <div id="collapseLaporan" class="collapse" aria-labelledby="headingLaporan" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/input-laporan-shift'); ?>">
                        <i class="fas fa-fw fa-pencil-alt text-primary mr-1"></i> Input Laporan Shift
                    </a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/laporanpenjualan'); ?>">
                        <i class="fas fa-fw fa-file-invoice-dollar text-success mr-1"></i> Laporan Penjualan
                    </a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/hppPenjualan'); ?>">
                        <i class="fas fa-fw fa-balance-scale text-warning mr-1"></i> HPP Penjualan
                    </a>
                </div>
            </div>
        </li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Admin Site</div>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAdmin">
                <i class="fas fa-fw fa-database"></i>
                <span>Master</span>
            </a>
            <div id="collapseAdmin" class="collapse" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?= base_url('admin/perintah-kerja'); ?>">Perintah Kerja</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/master'); ?>">Tambah Menu</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/varian_menu'); ?>">Varian Menu</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/inputOutlet'); ?>">Input Outlet</a>
                    <a class="collapse-item" href="<?= base_url('manajemen-penjualan/inputJamShift'); ?>">Input Jam Shift</a>
                    <a class="collapse-item" href="<?= base_url('admin/pemasok'); ?>">Tambah Pemasok</a>
                    <a class="collapse-item" href="<?= base_url('admin/biaya/view_tenaker'); ?>">Tambah Biaya Tenaga Kerja</a>
                    <a class="collapse-item" href="<?= base_url('admin/biaya/view_bop'); ?>">Tambah BOP</a>
                    <a class="collapse-item" href="<?= base_url('admin/komposisi'); ?>">Komposisi</a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBTKL" aria-expanded="true" aria-controls="collapseBTKL">
                <i class="fas fa-fw fa-user-check"></i>
                <span>BTKL</span>
            </a>
            <div id="collapseBTKL" class="collapse <?= (uri_string() === 'manajemen-penjualan/input-shift' || uri_string() === 'manajemen-penjualan/data-shift' || uri_string() === 'manajemen-penjualan/btkl' || uri_string() === 'manajemen-penjualan/btkl/form') ? 'show' : '' ?>" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Shift & Gaji:</h6>
                    <a class="collapse-item <?= uri_string() === 'manajemen-penjualan/input-shift' ? 'active' : '' ?>" href="<?= base_url('manajemen-penjualan/input-shift'); ?>">Input Jadwal Shift</a>
                    <a class="collapse-item <?= uri_string() === 'manajemen-penjualan/data-shift' ? 'active' : '' ?>" href="<?= base_url('manajemen-penjualan/data-shift'); ?>">Data Shift</a>
                    <a class="collapse-item <?= uri_string() === 'manajemen-penjualan/btkl/form' ? 'active' : '' ?>" href="<?= base_url('manajemen-penjualan/btkl/form'); ?>">Hitung Gaji BTKL</a>
                    <a class="collapse-item <?= uri_string() === 'manajemen-penjualan/btkl' ? 'active' : '' ?>" href="<?= base_url('manajemen-penjualan/btkl'); ?>">Rekap Gaji BTKL</a>
                </div>
            </div>
        </li>

    <?php endif; ?>
    <hr class="sidebar-divider d-none d-md-block">
    <li class="nav-item">
        <a class="nav-link" href="<?= base_url('logout'); ?>">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Log Out</span></a>
    </li>
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>