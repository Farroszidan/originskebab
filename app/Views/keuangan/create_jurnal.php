<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container mt-4">
    <h3>Input Jurnal Umum</h3>
    <form method="post" action="<?= base_url('keuangan/simpan_jurnal') ?>">

        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" class="form-control" name="tanggal" required>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="akun_debit">Akun Debit</label>
                <select name="akun_debit" class="form-control" required>
                    <option value="">-- Pilih Akun Debit --</option>
                    <?php foreach ($akun as $a) : ?>
                        <option value="<?= $a['id'] ?>"><?= esc($a['nama_akun']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="akun_kredit">Akun Kredit</label>
                <select name="akun_kredit" class="form-control" required>
                    <option value="">-- Pilih Akun Kredit --</option>
                    <?php foreach ($akun as $a) : ?>
                        <option value="<?= $a['id'] ?>"><?= esc($a['nama_akun']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="nominal">Nominal</label>
            <input type="number" class="form-control" name="nominal" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea class="form-control" name="keterangan" rows="2"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="<?= base_url('keuangan/index') ?>" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<?= $this->endSection() ?>