<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center rounded-top">
            <h5 class="mb-0"><i class="fas fa-list mr-2"></i><?= esc($tittle) ?></h5>
            <a href="<?= base_url('manajemen-penjualan/btkl/form') ?>" class="btn btn-light btn-sm shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Form Perhitungan
            </a>
        </div>
        <div class="card-body">

            <!-- Filter Form -->
            <form method="get" class="form-row align-items-end mb-4">
                <div class="form-group col-md-3">
                    <label for="start_date">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start_date" class="form-control shadow-sm"
                        value="<?= esc($filter['start_date'] ?? '') ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="end_date">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end_date" class="form-control shadow-sm"
                        value="<?= esc($filter['end_date'] ?? '') ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="outlet_id">Outlet</label>
                    <select name="outlet_id" class="form-control shadow-sm" onchange="this.form.submit()">
                        <option value="">Semua Outlet</option>
                        <?php foreach ($outlets as $outlet): ?>
                            <option value="<?= $outlet['id'] ?>" <?= ($filter['outlet_id'] ?? '') == $outlet['id'] ? 'selected' : '' ?>>
                                <?= esc($outlet['nama_outlet']) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="user_id">User</label>
                    <select name="user_id" class="form-control shadow-sm">
                        <option value="">Semua</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user->id ?>" <?= ($filter['user_id'] ?? '') == $user->id ? 'selected' : '' ?>>
                                <?= esc($user->username) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="form-group col-md-1">
                    <button type="submit" class="btn btn-primary btn-block shadow-sm">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover shadow-sm">
                    <thead class="thead-dark text-center">
                        <tr>
                            <th>Nama Outlet</th>
                            <th>Nama Pegawai</th>
                            <th>Jumlah Shift</th>
                            <th>Total Gaji</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grandTotal = 0;
                        if (!empty($rekap)):
                            foreach ($rekap as $outlet => $pegawaiList):
                                foreach ($pegawaiList as $pegawai => $data):
                                    $grandTotal += $data['total_gaji'];
                        ?>
                                    <tr class="text-center align-middle">
                                        <td><?= esc($outlet) ?></td>
                                        <td class="text-left"><?= esc($pegawai) ?></td>
                                        <td><?= $data['total_shift'] ?></td>
                                        <td><strong>Rp <?= number_format($data['total_gaji'], 0, ',', '.') ?></strong></td>
                                    </tr>
                            <?php endforeach;
                            endforeach; ?>
                            <tr class="bg-light font-weight-bold">
                                <td colspan="3" class="text-right">Total Keseluruhan</td>
                                <td class="text-center">Rp <?= number_format($grandTotal, 0, ',', '.') ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle mr-1"></i> Tidak ada data BTKL ditemukan untuk periode ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection(); ?>