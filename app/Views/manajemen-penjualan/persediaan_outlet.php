<?php $this->extend('templates/index_templates_general'); ?>
<?php $this->section('page-content'); ?>

<div class="container-fluid mt-4">
    <h4 class="mb-4 font-weight-bold text-gray-800"><i class="fas fa-warehouse mr-2"></i> Manajemen Persediaan Outlet</h4>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php if (in_groups('admin')): ?>
        <form method="get" action="<?= base_url('manajemen-penjualan/persediaanOutlet') ?>" class="form-inline mb-4">
            <label for="outlet_id" class="mr-2">Pilih Outlet:</label>
            <select name="outlet_id" class="form-control mr-2" onchange="this.form.submit()">
                <?php foreach ($outlets as $outlet): ?>
                    <option value="<?= $outlet['id'] ?>" <?= $selected_outlet == $outlet['id'] ? 'selected' : '' ?>>
                        <?= esc($outlet['nama_outlet']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php endif; ?>

    <button class="btn btn-outline-primary mb-3" type="button" id="toggleFormBtn">
        <i id="toggleIcon" class="fas fa-chevron-down mr-1"></i> Tambah Persediaan
    </button>
    <button class="btn btn-outline-warning mb-3 ml-2" type="button" id="toggleProduksiBtn">
        <i id="toggleProduksiIcon" class="fas fa-chevron-down mr-1"></i> Produksi Signature Sauce
    </button>

    <div id="formTambahPersediaan" class="card card-body shadow-sm border-left-primary mb-4" style="display: none;">
        <form action="<?= base_url('manajemen-penjualan/tambahPersediaanOutlet') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="outlet_id" value="<?= $selected_outlet ?>">

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="tanggal">Tanggal Masuk</label>
                    <input type="date" name="tanggal" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="kode_bahan">Bahan</label>
                    <select name="kode_bahan" class="form-control" required>
                        <option value="">-- Pilih Bahan --</option>
                        <?php foreach ($bsj as $bahan): ?>
                            <option value="<?= esc($bahan['kode']) ?>">
                                <?= strtoupper($bahan['kode']) ?> - <?= esc($bahan['nama']) ?> (<?= esc($bahan['satuan']) ?>)
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="jumlah">Jumlah</label>
                    <input type="number" name="jumlah" class="form-control" step="0.01" required>
                    <small class="form-text text-muted">Masukkan dalam kilogram (Kg) atau pcs</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-2">Simpan</button>
        </form>
    </div>
    <div id="formProduksiSaus" class="card card-body shadow-sm border-left-warning mb-4" style="display: none;">
        <form action="<?= base_url('manajemen-penjualan/tambahPersediaanOutlet') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="outlet_id" value="<?= $selected_outlet ?>">
            <input type="hidden" name="mode" value="produksi_signature_sauce">
            <input type="hidden" name="tanggal" value="<?= date('Y-m-d') ?>">

            <h6 class="font-weight-bold mb-3 text-warning">Form Produksi Signature Sauce</h6>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Bawang putih bubuk (gr)</label>
                    <input type="number" step="0.01" name="bawang" class="form-control" required>
                </div>
                <div class="form-group col-md-4">
                    <label>Paprika bubuk (gr)</label>
                    <input type="number" step="0.01" name="paprika" class="form-control" required>
                </div>
                <div class="form-group col-md-4">
                    <label>Lada putih bubuk (gr)</label>
                    <input type="number" step="0.01" name="lada" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Oregano (gr)</label>
                    <input type="number" step="0.01" name="oregano" class="form-control" required>
                </div>
                <div class="form-group col-md-4">
                    <label>Parsley (gr)</label>
                    <input type="number" step="0.01" name="parsley" class="form-control" required>
                </div>
                <div class="form-group col-md-4">
                    <label>Saus tomat (gr)</label>
                    <input type="number" step="0.01" name="saus_tomat" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-warning mt-3">
                <i class="fas fa-cogs mr-1"></i> Produksi Signature Sauce
            </button>
        </form>
    </div>



    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white font-weight-bold">
            <i class="fas fa-boxes mr-2"></i> Stok Saat Ini
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered m-0 text-center table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Kode Bahan</th>
                            <th>Nama Bahan</th>
                            <th>Satuan</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($persediaan)): ?>
                            <tr>
                                <td colspan="4" class="text-muted">Tidak ada data stok untuk outlet ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($persediaan as $item): ?>
                                <tr>
                                    <td><?= esc($item['kode_bahan']) ?></td>
                                    <td><?= esc($item['nama_bahan']) ?></td>
                                    <td><?= esc($item['satuan']) ?></td>
                                    <td>
                                        <?php
                                        $satuan = strtolower($item['satuan']);
                                        if ($satuan === 'kg') {
                                            echo number_format($item['stok'] / 1000, 2, ',', '.') . ' Kg';
                                        } else {
                                            echo number_format($item['stok'], 0) . ' ' . ucfirst($satuan);
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Toggle Script -->
<style>
    .rotated {
        transform: rotate(180deg);
    }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(function() {
        $('#toggleFormBtn').click(function() {
            $('#formTambahPersediaan').slideToggle(200);
            $('#toggleIcon').toggleClass('rotated');
            $('#formProduksiSaus').slideUp(200); // tutup form lain
        });

        $('#toggleProduksiBtn').click(function() {
            $('#formProduksiSaus').slideToggle(200);
            $('#toggleProduksiIcon').toggleClass('rotated');
            $('#formTambahPersediaan').slideUp(200); // tutup form lain
        });
    });
</script>


<?php $this->endSection(); ?>