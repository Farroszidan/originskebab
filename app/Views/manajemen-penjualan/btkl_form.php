<?= $this->extend('templates/index_templates_general'); ?>

<?= $this->section('page-content'); ?>

<div class="container mt-5">
    <h4>Perhitungan Gaji Karyawan (BTKL) - Shift Penjualan</h4>
    <form action="<?= base_url('menajemen-penjualan/btkl/hitung') ?>" method="post" class="card p-4 shadow-sm rounded">
        <div class="form-group">
            <label>Dari Tanggal</label>
            <input type="date" name="start_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Sampai Tanggal</label>
            <input type="date" name="end_date" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block mt-3">Hitung Gaji</button>
    </form>
</div>

<?= $this->endSection() ?>