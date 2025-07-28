<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid mt-4">
    <h3 class="mb-4 font-weight-bold text-dark">📋 Jadwal Pegawai</h3>

    <div class="mb-3">
        <a class="btn btn-primary mb-3" href="<?= base_url('manajemen-penjualan/jadwalpegawai/tambah'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Jadwal
        </a>
    </div>

    <form method="get" class="form-inline mb-3">
        <label class="mr-2">Tanggal Awal</label>
        <input type="date" name="tanggal_awal" class="form-control mr-2" value="<?= esc($filter['tanggal_awal'] ?? '') ?>">

        <label class="mr-2">Tanggal Akhir</label>
        <input type="date" name="tanggal_akhir" class="form-control mr-2" value="<?= esc($filter['tanggal_akhir'] ?? '') ?>">

        <label class="mr-2">Outlet</label>
        <select name="outlet_id" class="form-control mr-2">
            <option value="all">Semua Outlet</option>
            <?php foreach ($outlets as $outlet): ?>
                <option value="<?= $outlet['id'] ?>" <?= ($filter['outlet_id'] ?? '') == $outlet['id'] ? 'selected' : '' ?>>
                    <?= esc($outlet['nama_outlet']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-primary">Filter</button>
    </form>


    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-dark">
                        <tr class="text-center">
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Pegawai</th>
                            <th>Shift</th>
                            <th>Outlet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($jadwal)): ?>
                            <?php $no = 1;
                            foreach ($jadwal as $row): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                                    <td><?= esc($row['username']) ?></td>
                                    <td><?= esc($row['jam_mulai']) ?> - <?= esc($row['jam_selesai']) ?></td>
                                    <td><?= esc($row['nama_outlet']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data jadwal pegawai</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>