<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container mt-4">
    <h4 class="mb-4"><?= esc($tittle) ?></h4>

    <form action="<?= base_url('keuangan/simpan_pelunasan_piutang') ?>" method="post">
        <?= csrf_field() ?>

        <!-- Kode Akun Piutang (Hidden) -->
        <input type="hidden" name="kode_akun" value="<?= esc($kode_akun) ?>">

        <div class="form-group">
            <label for="nama_akun">Akun Piutang</label>
            <input type="text" class="form-control" value="<?= esc($nama_akun) ?>" readonly>
        </div>

        <div class="form-group">
            <label for="tanggal">Tanggal Pelunasan</label>
            <input type="date" class="form-control" name="tanggal" required value="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-group">
            <label for="nominal">Nominal Pelunasan</label>
            <input type="number" step="0.01" class="form-control" name="nominal" required>
        </div>

        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea class="form-control" name="keterangan" rows="3">Pelunasan Piutang</textarea>
        </div>

        <button type="submit" class="btn btn-success">Simpan Pelunasan</button>
        <a href="<?= base_url('keuangan/laporan_piutang') ?>" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<?= $this->endSection(); ?>