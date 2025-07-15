<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Input HPP per BSJ</h1>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"> <?= session()->getFlashdata('success'); ?> </div>
    <?php endif; ?>
    <form action="<?= base_url('produksi/hpp/simpan'); ?>" method="post">
        <div class="form-group">
            <label for="produksi_id">Pilih Kode Produksi</label>
            <select name="produksi_id" id="produksi_id" class="form-control" required onchange="updateHPP()">
                <option value="">-- Pilih Kode Produksi --</option>
                <?php foreach ($produksi as $p): ?>
                    <option value="<?= $p['id']; ?>"
                        data-total="<?= $p['total_biaya']; ?>"
                        data-jumlah="<?= $p['jumlah']; ?>"
                        data-hpp="<?= $p['jumlah'] > 0 ? $p['total_biaya'] / $p['jumlah'] : 0; ?>"><?= $p['no_produksi']; ?> (<?= $p['tanggal']; ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Total Biaya Produksi</label>
            <input type="text" id="total_biaya" class="form-control" readonly>
        </div>
        <div class="form-group">
            <label>Jumlah Produksi</label>
            <input type="text" id="jumlah_produksi" class="form-control" readonly>
        </div>
        <div class="form-group">
            <label>HPP per Unit</label>
            <input type="text" id="hpp_per_unit" class="form-control" readonly>
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <input type="text" name="keterangan" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Simpan HPP</button>
        <a href="<?= base_url('produksi/hpp'); ?>" class="btn btn-secondary">Kembali</a>
    </form>
</div>
<script>
    function updateHPP() {
        const select = document.getElementById('produksi_id');
        const selected = select.options[select.selectedIndex];
        document.getElementById('total_biaya').value = selected.getAttribute('data-total') ? 'Rp ' + parseFloat(selected.getAttribute('data-total')).toLocaleString('id-ID') : '';
        document.getElementById('jumlah_produksi').value = selected.getAttribute('data-jumlah') || '';
        document.getElementById('hpp_per_unit').value = selected.getAttribute('data-hpp') ? 'Rp ' + parseFloat(selected.getAttribute('data-hpp')).toLocaleString('id-ID') : '';
    }
</script>
<?= $this->endSection(); ?>