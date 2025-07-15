<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error'); ?></div>
<?php endif; ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Bahan</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= base_url('produksi/persediaan/update/' . $bahan['id']) ?>" method="post">
                <div class="form-group">
                    <label for="kode">Kode</label>
                    <input type="text" name="kode" class="form-control" id="kode" value="<?= $bahan['kode'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="nama">Nama Bahan</label>
                    <input type="text" name="nama" class="form-control" id="nama" value="<?= $bahan['nama'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <select name="kategori" class="form-control" id="kategori">
                        <option value="baku" <?= $bahan['kategori'] == 'baku' ? 'selected' : '' ?>>Baku</option>
                        <option value="penolong" <?= $bahan['kategori'] == 'penolong' ? 'selected' : '' ?>>Penolong</option>
                        <option value="lainnya" <?= $bahan['kategori'] == 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="jenis">Jenis</label>
                    <select name="jenis" class="form-control" id="jenis">
                        <option value="daging" <?= $bahan['jenis'] == 'daging' ? 'selected' : '' ?>>Daging</option>
                        <option value="sayur" <?= $bahan['jenis'] == 'sayur' ? 'selected' : '' ?>>Sayur</option>
                        <option value="lainnya" <?= $bahan['jenis'] == 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="stok">Stok</label>
                    <input type="number" step="0.1" name="stok" class="form-control" id="stok" value="<?= $bahan['stok'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="satuan">Satuan</label>
                    <select name="satuan" class="form-control" id="satuan" required>
                        <option value="kg" <?= strtolower($bahan['satuan']) == 'kg' ? 'selected' : '' ?>>Kg</option>
                        <option value="pcs" <?= strtolower($bahan['satuan']) == 'pcs' ? 'selected' : '' ?>>Pcs</option>
                        <option value="liter" <?= strtolower($bahan['satuan']) == 'liter' ? 'selected' : '' ?>>Liter</option>
                        <option value="meter" <?= strtolower($bahan['satuan']) == 'meter' ? 'selected' : '' ?>>Meter</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="harga_satuan">Harga Satuan</label>
                    <input type="number" step="1" name="harga_satuan" class="form-control" id="harga_satuan" value="<?= $bahan['harga_satuan'] ?>" required>
                </div>

                <button type=" submit" class="btn btn-primary">Update</button>
                <a href="<?= base_url('produksi/persediaan') ?>" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>