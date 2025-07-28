<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <!-- Judul -->
            <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>

            <!-- Tombol di bawah judul -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <button class="btn p-0 w-100 text-left" data-toggle="modal" data-target="#modalTambahPemasok">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Tambah Pemasok
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tabelPemasok" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Kode Pemasok</th>
                            <th>Nama Pemasok</th>
                            <th>Kategori Pemasok</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php foreach ($pemasok as $p) : ?>
                            <tr>
                                <td><?= esc($p['kode_sup']); ?></td>
                                <td><?= esc($p['nama']); ?></td>
                                <td><?= esc($p['kategori']); ?></td>
                                <td><?= esc($p['alamat']); ?></td>
                                <td><?= esc($p['telepon']); ?></td>
                                <td>
                                    <!-- tombol aksi -->
                                    <a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEditPemasok<?= $p['id'] ?>">Edit</a>
                                    <a href="<?= base_url('admin/pemasok/delete/' . $p['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus pemasok ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalTambahPemasok" tabindex="-1" role="dialog" aria-labelledby="modalTambahPemasokLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="<?= base_url('admin/pemasok/tambah') ?>" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahPemasokLabel">Tambah Pemasok</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="kode_sup">Kode Pemasok</label>
                        <input type="text" name="kode_sup" class="form-control" placeholder="Kode Pemasok" required>
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama Pemasok</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama Pemasok" required>
                    </div>
                    <div class="form-group">
                        <label for="kategori">Kategori Pemasok</label>
                        <select name="kategori" class="form-control">
                            <option value="daging">Daging</option>
                            <option value="sayur">Tepung</option>
                            <option value="lainnya">lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="alamat">Alamat</label>
                        <input type="text" name="alamat" class="form-control" placeholder="Alamat">
                    </div>
                    <div class="form-group">
                        <label for="telepon">Telepon / WA</label>
                        <input type="text" name="telepon" class="form-control" placeholder="Telepon / WA">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php foreach ($pemasok as $p) : ?>
    <div class="modal fade" id="modalEditPemasok<?= $p['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalEditPemasokLabel<?= $p['id'] ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="<?= base_url('admin/pemasok/update/' . $p['id']) ?>" method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Pemasok</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Kode Pemasok</label>
                            <input type="text" name="kode_sup" class="form-control" value="<?= esc($p['kode_sup']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Pemasok</label>
                            <input type="text" name="nama" class="form-control" value="<?= esc($p['nama']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="kategori" class="form-control">
                                <option value="daging" <?= $p['kategori'] == 'daging' ? 'selected' : '' ?>>Daging</option>
                                <option value="tepung  " <?= $p['kategori'] == 'tepung' ? 'selected' : '' ?>>Tepung</option>
                                <option value="lainnya" <?= $p['kategori'] == 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Alamat</label>
                            <input type="text" name="alamat" class="form-control" value="<?= esc($p['alamat']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Telepon / WA</label>
                            <input type="text" name="telepon" class="form-control" value="<?= esc($p['telepon']); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>
<?= $this->endSection(); ?>