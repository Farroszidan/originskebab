<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container mt-4">
    <h3>Tambah Akun</h3>
    <form action="<?= base_url('keuangan/save_akun') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="kode_akun">Kode Akun</label>
            <input type="text" class="form-control" id="kode_akun" name="kode_akun" placeholder="Contoh: 101" required>
        </div>

        <div class="form-group">
            <label for="nama_akun">Nama Akun</label>
            <input type="text" class="form-control" id="nama_akun" name="nama_akun" placeholder="Contoh: Kas" required>
        </div>

        <div class="form-group">
            <label for="jenis_akun">Jenis Akun</label>
            <select class="form-control" id="jenis_akun" name="jenis_akun" required>
                <option value="">-- Pilih Jenis --</option>
                <option value="Aset">Aset</option>
                <option value="Kewajiban">Kewajiban</option>
                <option value="Ekuitas">Ekuitas</option>
                <option value="Pendapatan">Pendapatan</option>
                <option value="Beban">Beban</option>
            </select>
        </div>

        <select name="tipe" class="form-control" required>
            <option value="">-- Pilih Tipe --</option>
            <option value="Debit">Debit</option>
            <option value="Kredit">Kredit</option>
        </select>


        <div class="form-group">
            <label for="saldo_awal">Saldo Awal</label>
            <input type="number" class="form-control" id="nama_akun" name="saldo_awal" placeholder="" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Akun</button>
        <a href="<?= base_url('keuangan/akun') ?>" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<?= $this->endSection() ?>