<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>
<div class="container-fluid">
    <h4 class="mb-4"><?= $tittle ?></h4>

    <form action="<?= base_url('keuangan/isi-kas') ?>" method="post">
        <div class="form-group">
            <label for="kas_outlet_id">Pilih Outlet</label>
            <select name="kas_outlet_id" class="form-control" required>
                <option value="">-- Pilih Outlet --</option>
                <?php foreach ($kas_outlet as $outlet): ?>
                    <option value="<?= $outlet->id ?>"><?= $outlet->nama_outlet ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="jumlah">Jumlah (Rp)</label>
            <input type="number" name="jumlah" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="<?= base_url('keuangan/isi-kas') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>
<?= $this->endSection(); ?>