<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container mt-4">
    <h3 class="mb-4">Form Input Jam Shift</h3>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('manajemen-penjualan/simpanJamShift') ?>" method="post" class="mb-5">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="nama_shift">Nama Shift</label>
            <input type="text" class="form-control" id="nama_shift" name="nama_shift" required>
        </div>
        <div class="form-group">
            <label for="jam_mulai">Jam Mulai</label>
            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
        </div>
        <div class="form-group">
            <label for="jam_selesai">Jam Selesai</label>
            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>

    <h4>Daftar Shift Kerja</h4>
    <table class="table table-bordered table-striped table-sm">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Nama Shift</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($shifts as $i => $shift) : ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($shift['nama_shift']) ?></td>
                    <td><?= esc($shift['jam_mulai']) ?></td>
                    <td><?= esc($shift['jam_selesai']) ?></td>
                    <td>
                        <a href="<?= base_url('manajemen-penjualan/hapusJamShift/' . $shift['id']) ?>"
                            class="btn btn-sm btn-danger"
                            onclick="return confirm('Yakin ingin menghapus shift ini?')">
                            Hapus
                        </a>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>