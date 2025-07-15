<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= esc($tittle) ?></h5>
        </div>
        <div class="card-body">
            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <form method="get" class="form-row align-items-end mb-4">
                <div class="form-group col-md-3">
                    <label for="start_date">Dari</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?= esc($_GET['start_date'] ?? '') ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="end_date">Sampai</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?= esc($_GET['end_date'] ?? '') ?>">
                </div>
                <div class="form-group col-md-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Pegawai</th>
                            <th>Shift</th>
                            <th>Tanggal</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <?php if (in_groups('admin')): ?>
                                <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($shifts as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= esc($row['username']) ?></td>
                                <td><?= esc($row['nama_shift']) ?></td>
                                <td><?= esc($row['tanggal']) ?></td>
                                <td><?= esc($row['jam_mulai']) ?></td>
                                <td><?= esc($row['jam_selesai']) ?></td>
                                <?php if (in_groups('admin')): ?>
                                    <td>
                                        <a href="<?= base_url('manajemen-penjualan/delete-shift/' . $row['id']) ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Yakin ingin menghapus shift ini?')">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>