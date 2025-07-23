<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center rounded-top">
            <h5 class="mb-0"><i class="fas fa-calendar-alt mr-2"></i><?= esc($tittle) ?></h5>
        </div>
        <div class="card-body">
            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Filter Form -->
            <form method="get" class="form-row align-items-end mb-4">
                <div class="form-group col-md-3">
                    <label for="start_date">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start_date" class="form-control shadow-sm"
                        value="<?= esc($start_date ?? '') ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="end_date">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end_date" class="form-control shadow-sm"
                        value="<?= esc($end_date ?? '') ?>">
                </div>
                <?php if (in_groups(['admin', 'keuangan'])): ?>
                    <div class="form-group col-md-3">
                        <label for="outlet_id">Outlet</label>
                        <select name="outlet_id" class="form-control">
                            <option value="">Semua Outlet</option>
                            <?php foreach ($outlets as $outlet): ?>
                                <option value="<?= $outlet['id'] ?>" <?= ($outlet_id == $outlet['id']) ? 'selected' : '' ?>>
                                    <?= esc($outlet['nama_outlet']) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                <?php endif ?>

                <div class="form-group col-md-2">
                    <button type="submit" class="btn btn-primary btn-block shadow-sm">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </form>

            <!-- Shift Table -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover shadow-sm">
                    <thead class="thead-dark">
                        <tr class="text-center">
                            <th style="width: 50px;">No</th>
                            <th>Nama Pegawai</th>
                            <th>Shift</th>
                            <th>Tanggal</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <?php if (in_groups('admin')): ?>
                                <th>Foto Absensi</th>
                                <th style="width: 100px;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($shifts)): ?>
                            <?php $no = 1;
                            foreach ($shifts as $row): ?>
                                <tr class="text-center align-middle">
                                    <td><?= $no++ ?></td>
                                    <td class="text-left"><?= esc($row['username']) ?></td>
                                    <td><?= esc($row['nama_shift']) ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                                    <td><?= esc($row['jam_mulai']) ?></td>
                                    <td><?= esc($row['jam_selesai']) ?></td>
                                    <?php if (in_groups('admin')): ?>
                                        <td>
                                            <?php if (!empty($row['foto_absensi'])) : ?>
                                                <a href="<?= base_url($row['foto_absensi']) ?>" target="_blank">
                                                    <img src="<?= base_url($row['foto_absensi']) ?>" alt="Foto Absensi" width="80" height="80">
                                                </a>
                                            <?php else : ?>
                                                <span class="text-muted">-</span>
                                            <?php endif ?>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('manajemen-penjualan/delete-shift/' . $row['id']) ?>"
                                                class="btn btn-sm btn-outline-danger btn-block"
                                                onclick="return confirm('Yakin ingin menghapus shift ini?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= in_groups('admin') ? 7 : 6 ?>" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Tidak ada data shift untuk tanggal yang dipilih.
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