<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container mt-4">
    <h4 class="mb-4"><?= esc($tittle) ?></h4>

    <form action="<?= base_url('keuangan/simpan_pelunasan_utang') ?>" method="post">
        <?= csrf_field() ?>

        <!-- Hidden kode akun -->
        <input type="hidden" name="kode_akun" value="<?= esc($kode_akun) ?>">

        <div class="form-group">
            <label for="nama_akun">Akun Utang</label>
            <input type="text" class="form-control" value="<?= esc($nama_akun) ?>" readonly>
        </div>

        <div class="form-group">
            <label for="tanggal">Tanggal Pelunasan</label>
            <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
            <label for="nominal">Nominal Pelunasan</label>
            <input type="number" class="form-control" name="nominal" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea class="form-control" name="keterangan" rows="3">Pelunasan Utang</textarea>
        </div>

        <button type="submit" class="btn btn-success">Simpan Pelunasan</button>
        <a href="<?= base_url('keuangan/laporan_utang') ?>" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<?= $this->endSection(); ?>