<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<style>
    .laporan-penjualan-card {
        display: none;
    }
</style>

<div class="container-fluid">
    <h1 class="mb-4">Laporan Penjualan</h1>

    <?php if (session()->getFlashdata('success_simpan')) : ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success_simpan') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success_hapus')) : ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success_hapus') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>


    <!-- FILTER -->
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><strong>Filter Data</strong></span>
            <button class="btn btn-sm btn-outline-primary" onclick="toggleSection('filterCard', this)">
                <i class="bi bi-eye-slash"></i> Sembunyikan
            </button>
        </div>
        <div class="card-body" id="filterCard">
            <form method="get" class="row">
                <div class="col-md-4 mb-3">
                    <label>Tanggal Review</label>
                    <input type="date" name="tanggal_review"
                        <?= $tanggalReview ? 'value="' . $tanggalReview . '"' : '' ?>
                        class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Outlet</label>
                    <?php if (!$isAdmin && !$isKeuangan) : ?>
                        <!-- Untuk penjualan (readonly outlet) -->
                        <input type="hidden" name="outlet_id" value="<?= $outlet_id ?>">
                        <select class="form-control" disabled>
                            <?php foreach ($outlets as $outlet) : ?>
                                <?php if ($outlet['id'] == $outlet_id) : ?>
                                    <option value="<?= $outlet['id'] ?>" selected><?= $outlet['nama_outlet'] ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    <?php else : ?>
                        <!-- Untuk admin / keuangan -->
                        <select name="outlet_id" class="form-control">
                            <option value="">Pilih Outlet</option>
                            <?php foreach ($outlets as $outlet) : ?>
                                <option value="<?= $outlet['id'] ?>" <?= $outlet_id == $outlet['id'] ? 'selected' : '' ?>>
                                    <?= $outlet['nama_outlet'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Tampilkan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- CETAK -->
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><strong>Cetak Laporan Penjualan</strong></span>
            <button class="btn btn-sm btn-outline-primary" onclick="toggleSection('cetakCard', this)">
                <i class="bi bi-eye-slash"></i> Sembunyikan
            </button>
        </div>
        <div class="card-body" id="cetakCard">
            <form action="<?= base_url('manajemen-penjualan/cetakLaporanPenjualan') ?>" method="get" target="_blank" class="form-inline">
                <input type="date" name="tanggal_awal" required class="form-control mx-1">
                <input type="date" name="tanggal_akhir" required class="form-control mx-1">
                <select name="outlet_id" class="form-control mx-1">
                    <option value="">Semua Outlet</option>
                    <?php foreach ($outlets as $o) : ?>
                        <option value="<?= $o['id'] ?>"><?= $o['nama_outlet'] ?></option>
                    <?php endforeach ?>
                </select>
                <button type="submit" class="btn btn-primary mx-1"><i class="bi bi-printer"></i> Cetak</button>
            </form>
        </div>
    </div>

    <!-- RINCIAN LAPORAN -->
    <?php if ($penjualan): ?>
        <!-- Ringkasan -->
        <!-- Ringkasan Penjualan -->
        <div class="laporan-penjualan-card mb-4">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card border-left-success shadow h-100">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Penjualan
                            </div>
                            <h4 class="font-weight-bold text-gray-800 mb-0">
                                Rp <?= number_format($totalPenjualan, 0, ',', '.') ?>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card border-left-danger shadow h-100">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Total Pengeluaran
                            </div>
                            <h4 class="font-weight-bold text-gray-800 mb-0">
                                Rp <?= number_format($totalPengeluaran, 0, ',', '.') ?>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card border-left-primary shadow h-100">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Selisih
                            </div>
                            <h4 class="font-weight-bold text-gray-800 mb-0">
                                Rp <?= number_format($totalPenjualan - $totalPengeluaran, 0, ',', '.') ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Menu Terjual -->
        <div class="card shadow mb-4 laporan-penjualan-card">
            <div class="card-header d-flex justify-content-between">
                <span><strong>Rincian Menu Terjual</strong></span>
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleSection('menuTerjual', this)">
                    <i class="bi bi-eye-slash"></i> Sembunyikan
                </button>
            </div>
            <div class="card-body" id="menuTerjual">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama Menu</th>
                            <th>Qty</th>
                            <th>Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detailMenuTerjual as $item): ?>
                            <tr>
                                <td><?= $item['nama_menu'] ?></td>
                                <td><?= $item['total_qty'] ?></td>
                                <td>Rp <?= number_format($item['total_harga'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Potongan Platform -->
        <div class="card shadow mb-4 laporan-penjualan-card">
            <div class="card-header d-flex justify-content-between">
                <span><strong>Potongan Platform Cashless</strong></span>
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleSection('potonganPlatform', this)">
                    <i class="bi bi-eye-slash"></i> Sembunyikan
                </button>
            </div>
            <div class="card-body" id="potonganPlatform">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Platform</th>
                            <th>Potongan (%)</th>
                            <th>Jumlah Transaksi</th>
                            <th>Total Potongan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($platforms as $jenis => $rate): ?>
                            <tr>
                                <td><?= strtoupper($jenis) ?></td>
                                <td><?= $rate * 100 ?>%</td>
                                <td><?= $potonganData[$jenis]['jumlah'] ?? 0 ?></td>
                                <td>Rp <?= number_format($potonganData[$jenis]['total'] ?? 0, 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rincian & Keterangan Pengeluaran -->
        <div class="card shadow mb-4 laporan-penjualan-card">
            <div class="card-header d-flex justify-content-between">
                <span><strong>Rincian Pengeluaran</strong></span>
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleSection('pengeluaranDetail', this)">
                    <i class="bi bi-eye-slash"></i> Sembunyikan
                </button>
            </div>
            <div class="card-body" id="pengeluaranDetail">
                <!-- Tabel Rincian -->
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detailPengeluaran as $p): ?>
                            <tr>
                                <td><?= $p['nama_barang'] ?></td>
                                <td><?= $p['jumlah'] ?></td>
                                <td>Rp <?= number_format($p['total'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Keterangan Pengeluaran -->
                <div class="form-group mt-3">
                    <label for="keterangan_pengeluaran"><strong>Keterangan Pengeluaran</strong></label>
                    <textarea name="keterangan_pengeluaran" id="keterangan_pengeluaran" class="form-control" rows="4" readonly style="background-color:#f9f9f9;"><?php foreach ($detailPengeluaran as $item) : ?>
                        <?= $item['nama_barang'] ?> (<?= $item['jumlah'] ?>): Rp <?= number_format($item['total'], 0, ',', '.') . "\n" ?>
                        <?php endforeach; ?>
                    </textarea>
                </div>
            </div>
        </div>


        <!-- Simpan Laporan -->
        <div class="card shadow mb-4 laporan-penjualan-card">
            <div class="card-header bg-success text-white">
                <strong><i class="fas fa-save"></i> Simpan Laporan Penjualan</strong>
            </div>
            <div class="card-body">

                <form id="form-simpan-laporan" method="post" action="<?= base_url('manajemen-penjualan/simpanLaporanPenjualan') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="tanggal" value="<?= $tanggalReview ?>">
                    <input type="hidden" name="outlet_id" value="<?= $outlet_id ?>">
                    <input type="hidden" name="total_penjualan" value="<?= $totalPenjualan ?>">
                    <input type="hidden" name="total_pengeluaran" value="<?= $totalPengeluaran ?>">
                    <input type="hidden" name="konfirmasi_simpan" value="0" id="konfirmasi-simpan">

                    <?php
                    // Buat string keterangan untuk dikirim (tanpa ditampilkan)
                    $keteranganPengeluaran = '';
                    foreach ($detailPengeluaran as $item) {
                        $keteranganPengeluaran .= $item['nama_barang'] . ' (' . $item['jumlah'] . '): Rp ' . number_format($item['total'], 0, ',', '.') . "\n";
                    }
                    ?>
                    <input type="hidden" name="keterangan_pengeluaran" value="<?= trim($keteranganPengeluaran) ?>">

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan Laporan</button>
                    </div>
                </form>

            </div>
        </div>
    <?php endif; ?>

    <!-- Laporan Tersimpan -->
    <div class="card shadow mb-4">
        <div class="card-header"><strong>Daftar Laporan Tersimpan</strong></div>
        <div class="card-body">
            <form method="get" class="row mb-3">
                <div class="col-md-4">
                    <label>Filter Tanggal</label>
                    <input type="date" name="tanggal_daftar"
                        <?= $tanggalDaftar ? 'value="' . $tanggalDaftar . '"' : '' ?>
                        class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-secondary w-100"><i class="bi bi-search"></i> Tampilkan</button>
                </div>
            </form>

            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Outlet</th>
                        <th>Total Penjualan</th>
                        <th>Total Pengeluaran</th>
                        <th>Selisih</th>
                        <th>Keterangan</th>
                        <?php if (in_groups('admin')) : ?>
                            <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laporanTersimpan as $lap): ?>
                        <tr>
                            <td><?= $lap['tanggal'] ?></td>
                            <td><?= $lap['nama_outlet'] ?></td>
                            <td>Rp <?= number_format($lap['total_penjualan'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($lap['total_pengeluaran'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($lap['total_penjualan'] - $lap['total_pengeluaran'], 0, ',', '.') ?></td>
                            <td><?= nl2br($lap['keterangan_pengeluaran']) ?></td>
                            <?php if (in_groups('admin')) : ?>
                                <td>
                                    <form action="<?= base_url('manajemen-penjualan/hapus-laporan'); ?>" method="post" onsubmit="return confirm('Yakin ingin hapus laporan tanggal <?= $lap['tanggal']; ?>?')">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="tanggal" value="<?= $lap['tanggal']; ?>">
                                        <input type="hidden" name="outlet_id" value="<?= $lap['outlet_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.laporan-penjualan-card');
        const filterCard = document.getElementById('filterCard');

        <?php if (session()->getFlashdata('success_simpan')) : ?>
            // Jika selesai simpan, sembunyikan semua card
            cards.forEach(card => card.style.display = 'none');
        <?php elseif ($penjualan) : ?>
            // Jika data tersedia (hasil filter), tampilkan semua card
            cards.forEach(card => card.style.display = 'block');
        <?php else : ?>
            // Selain itu, sembunyikan semua card
            cards.forEach(card => card.style.display = 'none');
        <?php endif; ?>

        // Handle konfirmasi simpan jika ada warning
        const form = document.getElementById('form-simpan-laporan');
        const konfirmasiInput = document.getElementById('konfirmasi-simpan');

        <?php if (session()->getFlashdata('warning')) : ?>
            if (confirm("<?= session()->getFlashdata('warning') ?>")) {
                konfirmasiInput.value = "1";
                form.submit();
            }
        <?php endif; ?>
    });

    // Fungsi toggle untuk sembunyikan/tampilkan section seperti filterCard
    function toggleSection(sectionId, button) {
        const section = document.getElementById(sectionId);
        if (!section) return;

        if (section.style.display === 'none' || section.classList.contains('d-none')) {
            section.style.display = 'block';
            section.classList.remove('d-none');
            button.innerHTML = '<i class="bi bi-eye-slash"></i> Sembunyikan';
        } else {
            section.style.display = 'none';
            button.innerHTML = '<i class="bi bi-eye"></i> Tampilkan';
        }
    }
</script>


<?= $this->endSection(); ?>