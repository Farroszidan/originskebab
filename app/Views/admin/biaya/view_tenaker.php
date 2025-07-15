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
                    <button class="btn p-0 w-100 text-left" data-toggle="modal" data-target="#modalTambahTenaker">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Tambah Biaya Tenaga Kerja
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
                <table class="table table-bordered table-striped" id="tabelTenaker" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Biaya</th>
                            <th>Jumlah Biaya</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php foreach ($tenaker as $t) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= esc($t['nama']); ?></td>
                                <td><?= 'Rp ' . number_format(esc($t['biaya']), 0, ',', '.'); ?></td>

                                <td>
                                    <!-- tombol aksi -->
                                    <a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEditTenaker<?= $t['id'] ?>">Edit</a>
                                    <a href="<?= base_url('admin/biaya/delete/' . $t['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus biaya ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalTambahTenaker" tabindex="-1" role="dialog" aria-labelledby="modalTambahTenakerLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="<?= base_url('admin/biaya/simpan') ?>" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahTenakerLabel">Tambah Biaya</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label for="nama">Nama Biaya</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama Biaya" required>
                    </div>
                    <div class="form-group">
                        <label for="biaya">Jumlah Biaya</label>
                        <input type="text" name="biaya" class="form-control" placeholder="Rp">
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
<?php foreach ($tenaker as $t) : ?>
    <div class="modal fade" id="modalEditTenaker<?= $t['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalEditTenakerLabel<?= $t['id'] ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="<?= base_url('admin/biaya/update/' . $t['id']) ?>" method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Biaya Tenaga Kerja</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Biaya</label>
                            <input type="text" name="nama" class="form-control" value="<?= esc($t['nama']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Jumlah Biaya</label>
                            <input type="text" name="biaya" class="form-control" value="<?= 'Rp ' . number_format(esc($t['biaya']), 0, ',', '.'); ?>">
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

    // Format input saat mengetik (modal edit)
    <?php foreach ($tenaker as $t) : ?>
        document.querySelector('#modalEditTenaker<?= $t['id'] ?> input[name="biaya"]').addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value, 'Rp ');
        });
    <?php endforeach; ?>

    // Sebelum form disubmit, konversi nilai Rupiah ke angka
    document.querySelector('form[action="<?= base_url('admin/biaya/simpan') ?>"]').addEventListener('submit', function(e) {
        var biayaInput = this.querySelector('input[name="biaya"]');
        biayaInput.value = convertToAngka(biayaInput.value);
    });

    <?php foreach ($tenaker as $t) : ?>
        document.querySelector('form[action="<?= base_url('admin/biaya/update/' . $t['id']) ?>"]').addEventListener('submit', function(e) {
            var biayaInput = this.querySelector('input[name="biaya"]');
            biayaInput.value = convertToAngka(biayaInput.value);
        });
    <?php endforeach; ?>
</script>

<?= $this->endSection(); ?>