<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 text-gray-800">Master Data Varian Menu</h1>
        <a href="#" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
        </a>
    </div>

    <!-- Tambah Varian Menu Card -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <button class="btn p-0 w-100 text-left" data-toggle="modal" data-target="#modalTambahMenu">
                <div class="card border-left-primary shadow h-100 py-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tambah Varian Menu</div>
                            <div class="small text-muted">Klik untuk menambahkan</div>
                        </div>
                        <i class="fas fa-plus-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </button>
        </div>
    </div>

    <!-- Tabel Varian Menu -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Kode Barang</th>
                            <th>Nama Menu</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($varian_menu as $menu): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= esc($menu['kode_barang']) ?></td>
                                <td><?= esc($menu['nama_menu']) ?></td>
                                <td><?= esc($menu['kategori']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalEditMenu<?= $menu['id'] ?>" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="<?= base_url('manajemen-penjualan/hapusVarianMenu/' . $menu['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus menu ini?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal Tambah Varian Menu -->
<div class="modal fade" id="modalTambahMenu" tabindex="-1" role="dialog" aria-labelledby="modalTambahMenuLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="<?= base_url('manajemen-penjualan/simpanVarianMenu') ?>" method="post">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTambahMenuLabel">Tambah Varian Menu</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="kategori">Kategori</label>
                            <select class="form-control" id="kategori" name="kategori" required>
                                <option value="" disabled selected>Pilih Kategori</option>
                                <option value="Original">Original</option>
                                <option value="Phenomenal">Phenomenal</option>
                                <option value="Meatlovers">Meatlovers</option>
                                <option value="Mentai">Mentai</option>
                                <option value="Curry">Curry</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="kode_barang">Kode Barang</label>
                            <input type="text" class="form-control" id="kode_barang" name="kode_barang" required readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nama_menu">Nama Menu</label>
                        <input type="text" class="form-control" id="nama_menu" name="nama_menu" required>
                    </div>
                </div>
                <div class="modal-footer px-4">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<?php foreach ($varian_menu as $menu): ?>
    <div class="modal fade" id="modalEditMenu<?= $menu['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalEditMenuLabel<?= $menu['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form action="<?= base_url('manajemen-penjualan/updateVarianMenu/' . $menu['id']) ?>" method="post">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">Edit Varian Menu</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Kategori</label>
                                <select class="form-control" name="kategori" required>
                                    <?php $kategoriList = ['Original', 'Phenomenal', 'Meatlovers', 'Mentai', 'Curry']; ?>
                                    <?php foreach ($kategoriList as $kat): ?>
                                        <option value="<?= $kat ?>" <?= $menu['kategori'] == $kat ? 'selected' : '' ?>><?= $kat ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Kode Barang</label>
                                <input type="text" class="form-control" name="kode_barang" value="<?= esc($menu['kode_barang']) ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nama Menu</label>
                            <input type="text" class="form-control" name="nama_menu" value="<?= esc($menu['nama_menu']) ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer px-4">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-white">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kategoriSelect = document.getElementById('kategori');
        const kodeBarangInput = document.getElementById('kode_barang');

        kategoriSelect.addEventListener('change', function() {
            const selected = this.value;
            kodeBarangInput.value = selected ? selected.substr(0, 3).toUpperCase() : '';
        });
    });
</script>

<?= $this->endSection(); ?>