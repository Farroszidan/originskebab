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
                    <button class="btn p-0 w-100 text-left" data-toggle="modal" data-target="#modalTambahBOP">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Tambah Biaya Overhead Pabrik
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
                <table class="table table-bordered table-striped" id="tabelBOP" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama BOP</th>
                            <th>Jenis BSJ</th>
                            <th>Jumlah Biaya</th>
                            <th>Detail Biaya</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php foreach ($bop as $b) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= esc($b['nama']); ?></td>
                                <td><?= esc($b['jenis_bsj'] ?? '-'); ?></td>
                                <td><?= 'Rp ' . number_format(esc($b['biaya'] ?? 0), 0, ',', '.'); ?></td>
                                <td>
                                    <?php if (!empty($b['detail'])): ?>
                                        <ul class="mb-0">
                                            <?php foreach ($b['detail'] as $d): ?>
                                                <li><?= esc($d['nama_biaya']); ?>: Rp <?= number_format($d['jumlah_biaya'], 0, ',', '.'); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <em>-</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- tombol aksi -->
                                    <a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEditBOP<?= $b['id'] ?>">Edit</a>
                                    <a href="<?= base_url('admin/biaya/deleteBOP/' . $b['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus biaya ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalTambahBOP" tabindex="-1" role="dialog" aria-labelledby="modalTambahBOPLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="<?= base_url('admin/biaya/simpanBOP') ?>" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahBOPLabel">Tambah Biaya Overhead Pabrik</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama">Nama BOP</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama BOP" required>
                    </div>
                    <div class="form-group">
                        <label for="jenis_bsj">Jenis BSJ</label>
                        <select name="jenis_bsj" class="form-control" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="sedikit">Sedikit (&lt;500)</option>
                            <option value="sedang">Sedang (500-1000)</option>
                            <option value="banyak">Banyak (1000-1500)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="biaya">Jumlah Biaya Overhead</label>
                        <input type="text" name="biaya" class="form-control" placeholder="Rp" required>
                    </div>
                    <hr>
                    <label>Detail Biaya Overhead</label>
                    <div id="detail-bop-list">
                        <div class="form-row mb-2">
                            <div class="col">
                                <input type="text" name="nama_biaya[]" class="form-control" placeholder="Nama Biaya">
                            </div>
                            <div class="col">
                                <input type="text" name="jumlah_biaya[]" class="form-control" placeholder="Jumlah Biaya">
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-danger btn-sm remove-detail-bop">Hapus</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="add-detail-bop">Tambah Detail</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php foreach ($bop as $b) : ?>
    <div class="modal fade" id="modalEditBOP<?= $b['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalEditBOPLabel<?= $b['id'] ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="<?= base_url('admin/biaya/updateBOP/' . $b['id']) ?>" method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Biaya Overhead Pabrik</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama BOP</label>
                            <input type="text" name="nama" class="form-control" value="<?= esc($b['nama']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Jenis BSJ</label>
                            <select name="jenis_bsj" class="form-control" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="sedikit" <?= ($b['jenis_bsj'] == 'sedikit') ? 'selected' : '' ?>>Sedikit (&lt;500)</option>
                                <option value="sedang" <?= ($b['jenis_bsj'] == 'sedang') ? 'selected' : '' ?>>Sedang (500-1000)</option>
                                <option value="banyak" <?= ($b['jenis_bsj'] == 'banyak') ? 'selected' : '' ?>>Banyak (1000-1500)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jumlah Biaya Overhead</label>
                            <input type="text" name="biaya" class="form-control" value="<?= esc($b['biaya'] ?? 0); ?>">
                        </div>
                        <hr>
                        <label>Detail Biaya Overhead</label>
                        <div id="edit-detail-bop-list-<?= $b['id'] ?>">
                            <?php if (!empty($b['detail'])): ?>
                                <?php foreach ($b['detail'] as $d): ?>
                                    <div class="form-row mb-2">
                                        <div class="col">
                                            <input type="text" name="nama_biaya[]" class="form-control" value="<?= esc($d['nama_biaya']); ?>" placeholder="Nama Biaya">
                                        </div>
                                        <div class="col">
                                            <input type="text" name="jumlah_biaya[]" class="form-control" value="<?= esc($d['jumlah_biaya']); ?>" placeholder="Jumlah Biaya">
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-danger btn-sm remove-detail-bop">Hapus</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="form-row mb-2">
                                    <div class="col">
                                        <input type="text" name="nama_biaya[]" class="form-control" placeholder="Nama Biaya">
                                    </div>
                                    <div class="col">
                                        <input type="text" name="jumlah_biaya[]" class="form-control" placeholder="Jumlah Biaya">
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-danger btn-sm remove-detail-bop">Hapus</button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-success btn-sm add-edit-detail-bop" data-bop-id="<?= $b['id'] ?>">Tambah Detail</button>
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
<script>
    // Fungsi untuk format Rupiah
    function formatRupiah(angka, prefix) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp ' + rupiah : '');
    }

    // Fungsi untuk konversi ke angka (hapus format Rupiah)
    function convertToAngka(rupiah) {
        return parseInt(rupiah.replace(/[^0-9]/g, ''));
    }

    // Format input saat mengetik (modal tambah)
    document.querySelector('input[name="biaya"]').addEventListener('keyup', function(e) {
        this.value = formatRupiah(this.value, 'Rp ');
    });

    // Format input detail biaya (modal tambah)
    document.querySelectorAll('input[name="jumlah_biaya[]"]').forEach(function(input) {
        input.addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value, 'Rp ');
        });
    });

    // Tambah baris detail biaya (modal tambah)
    document.getElementById('add-detail-bop').addEventListener('click', function() {
        var container = document.getElementById('detail-bop-list');
        var row = document.createElement('div');
        row.className = 'form-row mb-2';
        row.innerHTML = `<div class="col"><input type="text" name="nama_biaya[]" class="form-control" placeholder="Nama Biaya"></div><div class="col"><input type="text" name="jumlah_biaya[]" class="form-control" placeholder="Jumlah Biaya"></div><div class="col-auto"><button type="button" class="btn btn-danger btn-sm remove-detail-bop">Hapus</button></div>`;
        container.appendChild(row);
        row.querySelector('input[name="jumlah_biaya[]"]').addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value, 'Rp ');
        });
    });

    // Hapus baris detail biaya (modal tambah)
    document.getElementById('detail-bop-list').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-detail-bop')) {
            e.target.closest('.form-row').remove();
        }
    });

    // Format input saat mengetik (modal edit)
    <?php foreach ($bop as $b) : ?>
        document.querySelector('#modalEditBOP<?= $b['id'] ?> input[name="biaya"]').addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value, 'Rp ');
        });
        document.querySelectorAll('#modalEditBOP<?= $b['id'] ?> input[name="jumlah_biaya[]"]').forEach(function(input) {
            input.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value, 'Rp ');
            });
        });
        // Tambah baris detail biaya (modal edit)
        document.querySelector('#modalEditBOP<?= $b['id'] ?> .add-edit-detail-bop').addEventListener('click', function() {
            var container = document.getElementById('edit-detail-bop-list-<?= $b['id'] ?>');
            var row = document.createElement('div');
            row.className = 'form-row mb-2';
            row.innerHTML = `<div class="col"><input type="text" name="nama_biaya[]" class="form-control" placeholder="Nama Biaya"></div><div class="col"><input type="text" name="jumlah_biaya[]" class="form-control" placeholder="Jumlah Biaya"></div><div class="col-auto"><button type="button" class="btn btn-danger btn-sm remove-detail-bop">Hapus</button></div>`;
            container.appendChild(row);
            row.querySelector('input[name="jumlah_biaya[]"]').addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value, 'Rp ');
            });
        });
        // Hapus baris detail biaya (modal edit)
        document.getElementById('edit-detail-bop-list-<?= $b['id'] ?>').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-detail-bop')) {
                e.target.closest('.form-row').remove();
            }
        });
    <?php endforeach; ?>

    // Sebelum form disubmit, konversi nilai Rupiah ke angka
    document.querySelector('form[action="<?= base_url('admin/biaya/simpanBOP') ?>"]').addEventListener('submit', function(e) {
        var biayaInput = this.querySelector('input[name="biaya"]');
        biayaInput.value = convertToAngka(biayaInput.value);
        this.querySelectorAll('input[name="jumlah_biaya[]"]').forEach(function(input) {
            input.value = convertToAngka(input.value);
        });
    });

    <?php foreach ($bop as $b) : ?>
        document.querySelector('form[action="<?= base_url('admin/biaya/updateBOP/' . $b['id']) ?>"]').addEventListener('submit', function(e) {
            var biayaInput = this.querySelector('input[name="biaya"]');
            biayaInput.value = convertToAngka(biayaInput.value);
            this.querySelectorAll('input[name="jumlah_biaya[]"]').forEach(function(input) {
                input.value = convertToAngka(input.value);
            });
        });
    <?php endforeach; ?>
</script>
<?= $this->endSection(); ?>