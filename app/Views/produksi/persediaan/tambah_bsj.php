<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error'); ?></div>
<?php endif; ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= base_url('produksi/persediaan/simpan_bsj') ?>" method="post">
                <div class="form-group">
                    <label for="kode">Kode</label>
                    <input type="text" name="kode" class="form-control" id="kode" placeholder="kode" required>
                </div>

                <div class="form-group">
                    <label for="nama">Nama Barang</label>
                    <input type="text" name="nama" class="form-control" id="nama" placeholder="nama" required>
                </div>

                <div class="form-group">
                    <label for="stok">Stok Awal</label>
                    <input type="number" step="1" name="stok" class="form-control" id="stok" placeholder="Stok Awal" required>
                </div>

                <div class="form-group">
                    <label for="satuan">Satuan</label>
                    <input type="text" name="satuan" class="form-control" id="satuan" placeholder="Satuan (kg, liter, dll)" required>
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="<?= base_url('produksi/persediaan/bsj') ?>" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>