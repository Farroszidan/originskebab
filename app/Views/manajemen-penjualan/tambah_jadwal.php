<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container mt-4">
    <h4 class="mb-4">Tambah Jadwal Pegawai</h4>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error'); ?></div>
    <?php endif; ?>

    <form action="<?= base_url('manajemen-penjualan/jadwalpegawai/simpan') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" name="tanggal" id="tanggal" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="outlet_id">Outlet</label>
            <select name="outlet_id" id="outlet_id" class="form-control" required>
                <option value="">-- Pilih Outlet --</option>
                <?php foreach ($outletList as $outlet) : ?>
                    <option value="<?= $outlet['id']; ?>"><?= $outlet['nama_outlet']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <hr>

        <label>Daftar Pegawai dan Shift</label>
        <div id="jadwal-container">
            <div class="jadwal-row row mb-2">
                <div class="col-md-6">
                    <select name="user_id[]" class="form-control user-select" required>
                        <option value="">-- Pilih Pegawai --</option>
                        <?php foreach ($pegawai as $p) : ?>
                            <option value="<?= $p['id'] ?>" data-outlet="<?= $p['outlet_id'] ?>">
                                <?= esc($p['username']) ?> (<?= esc($p['nama_outlet']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="shift_id[]" class="form-control" required>
                        <option value="">-- Pilih Shift --</option>
                        <?php foreach ($shift as $s) : ?>
                            <option value="<?= $s['id']; ?>"><?= $s['nama_shift']; ?> (<?= $s['jam_mulai']; ?> - <?= $s['jam_selesai']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-remove-row">✖</button>
                </div>
            </div>
        </div>

        <button type="button" id="btn-tambah-row" class="btn btn-sm btn-info mt-2">+ Tambah Pegawai</button>

        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
        </div>
    </form>
</div>

<script>
    // Filter pegawai berdasarkan outlet
    const outletSelect = document.getElementById('outlet_id');
    outletSelect.addEventListener('change', function() {
        const outletId = this.value;
        const userSelects = document.querySelectorAll('.user-select');

        userSelects.forEach(select => {
            Array.from(select.options).forEach(opt => {
                if (opt.value === "") return;
                opt.hidden = (opt.dataset.outlet !== outletId);
            });
        });
    });

    // Tambah baris pegawai+shift
    document.getElementById('btn-tambah-row').addEventListener('click', function() {
        const container = document.getElementById('jadwal-container');
        const row = container.querySelector('.jadwal-row');
        const clone = row.cloneNode(true);

        clone.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
        });

        container.appendChild(clone);
    });

    // Hapus baris
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove-row')) {
            const rows = document.querySelectorAll('.jadwal-row');
            if (rows.length > 1) {
                e.target.closest('.jadwal-row').remove();
            }
        }
    });
</script>

<?= $this->endSection(); ?>