<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container mt-4">
    <h3 class="mb-4">Form Input Outlet</h3>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('manajemen-penjualan/simpanOutlet') ?>" method="post" class="mb-5">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="nama_outlet">Nama Outlet</label>
            <input type="text" class="form-control" id="nama_outlet" name="nama_outlet" required>
        </div>
        <div class="form-group">
            <label for="alamat">Alamat (Kota)</label>
            <input type="text" class="form-control" id="alamat" name="alamat" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>

    <h4>Daftar Outlet</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Nama Outlet</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($outlet as $i => $o) : ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($o['nama_outlet']) ?></td>
                        <td><?= esc($o['alamat']) ?></td>
                        <td>
                            <a href="<?= base_url('manajemen-penjualan/hapusOutlet/' . $o['id']) ?>"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('Yakin ingin menghapus outlet ini?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>