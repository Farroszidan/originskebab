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
            <!-- Kolom Akun Debit -->
            <div class="form-group col-md-6">
                <label for="akun_debit">Akun Debit</label>
                <select name="akun_debit" class="form-control" required>
                    <option value="">-- Pilih Akun Debit --</option>
                    <?php foreach ($akun as $a) : ?>
                        <option value="<?= $a['id'] ?>"><?= esc($a['nama_akun']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Kolom Akun Kredit -->
            <div class="form-group col-md-6">
                <label for="akun_kredit">Akun Kredit</label>
                <select name="akun_kredit" id="akun_kredit" class="form-control" required>
                    <option value="">-- Pilih Akun Kredit --</option>
                    <?php foreach ($akun as $a): ?>
                        <option value="<?= $a['id'] ?>" data-kode="<?= esc($a['kode_akun']) ?>">
                            <?= esc($a['nama_akun']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <!-- Tambahan form pemasok -->
        <div class="form-group" id="supplier-wrapper" style="display: none;">
            <label for="supplier_id">Pilih Supplier</label>
            <select name="supplier_id" id="supplier_id" class="form-control">
                <option value="">-- Pilih Supplier --</option>
                <?php foreach ($suppliers as $sup): ?>
                    <option value="<?= $sup['id'] ?>"><?= esc($sup['nama']) ?></option>
                <?php endforeach; ?>
            </select>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const akunKredit = document.getElementById('akun_kredit');
        const supplierWrapper = document.getElementById('supplier-wrapper');

        akunKredit.addEventListener('change', function() {
            const selectedOption = akunKredit.options[akunKredit.selectedIndex];
            const kodeAkun = selectedOption.getAttribute('data-kode') || '';

            if (kodeAkun.startsWith('2')) {
                supplierWrapper.style.display = 'block';
            } else {
                supplierWrapper.style.display = 'none';
                document.getElementById('supplier_id').value = '';
            }
        });
    });
</script>

<?= $this->endSection() ?>