<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><?= esc($tittle) ?></h5>
        </div>
        <div class="card-body">

            <form method="get" class="form-row align-items-end mb-4">
                <div class="form-group col-md-3">
                    <label for="start_date">Dari</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?= esc($filter['start_date'] ?? '') ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="end_date">Sampai</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?= esc($filter['end_date'] ?? '') ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="outlet_id">Outlet</label>
                    <select name="outlet_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <?php foreach ($outlets as $outlet): ?>
                            <option value="<?= $outlet['id'] ?>" <?= ($filter['outlet_id'] ?? '') == $outlet['id'] ? 'selected' : '' ?>>
                                <?= esc($outlet['nama_outlet']) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="user_id">User</label>
                    <select name="user_id" class="form-control">
                        <option value="">Semua</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user->id ?>" <?= ($filter['user_id'] ?? '') == $user->id ? 'selected' : '' ?>>
                                <?= esc($user->username) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="form-group col-md-1">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Nama Outlet</th>
                            <th>Nama Pegawai</th>
                            <th>Jumlah Shift</th>
                            <th>Total Gaji (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rekap as $outletId => $pegawai): ?>
                            <?php foreach ($pegawai as $nama => $jumlah): ?>
                                <tr>
                                    <td><?= esc($outletId) ?></td>
                                    <td><?= esc($nama) ?></td>
                                    <td><?= $jumlah ?></td>
                                    <td><?= number_to_currency($jumlah * $gaji_per_shift, 'IDR') ?></td>
                                </tr>
                            <?php endforeach ?>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>