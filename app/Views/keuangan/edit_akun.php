<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container mt-4">
    <h4>Edit Akun</h4>

    <form action="<?= base_url('keuangan/update_akun/' . $akun['id']) ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Kode Akun</label>
            <input type="text" name="kode_akun" class="form-control" value="<?= esc($akun['kode_akun']) ?>" required>
        </div>
        <div class="form-group">
            <label>Nama Akun</label>
            <input type="text" name="nama_akun" class="form-control" value="<?= esc($akun['nama_akun']) ?>" required>
        </div>
        <div class="form-group">
            <label>Jenis Akun</label>
            <select name="jenis_akun" class="form-control" required>
                <option <?= $akun['jenis_akun'] == 'Aset' ? 'selected' : '' ?>>Aset</option>
                <option <?= $akun['jenis_akun'] == 'Kewajiban' ? 'selected' : '' ?>>Kewajiban</option>
                <option <?= $akun['jenis_akun'] == 'Ekuitas' ? 'selected' : '' ?>>Ekuitas</option>
                <option <?= $akun['jenis_akun'] == 'Pendapatan' ? 'selected' : '' ?>>Pendapatan</option>
                <option <?= $akun['jenis_akun'] == 'Beban' ? 'selected' : '' ?>>Beban</option>
            </select>
        </div>
        <div class="form-group">
            <label>Tipe</label>
            <select name="tipe" class="form-control" required>
                <option <?= $akun['tipe'] == 'debit' ? 'selected' : '' ?>>debit</option>
                <option <?= $akun['tipe'] == 'kredit' ? 'selected' : '' ?>>kredit</option>
            </select>
        </div>
        <div class="form-group">
            <label>Saldo Awal</label>
            <input type="number" name="saldo" class="form-control" value="<?= esc($akun['saldo']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="<?= base_url('keuangan/akun') ?>" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<?= $this->endSection() ?>