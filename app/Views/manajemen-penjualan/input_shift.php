<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container-fluid mt-5">
    <h3 class="mb-4 font-weight-bold text-gray-800">Input Jadwal Shift Kerja</h3>

    <form action="<?= base_url('manajemen-penjualan/simpan-shift') ?>" method="post" id="formShift">
        <!-- Outlet -->
        <div class="form-group">
            <label for="outletSelect">Outlet</label>
            <select name="outlet_id" id="outletSelect" class="form-control" required>
                <option value="">-- Pilih Outlet --</option>
                <?php foreach ($outlets as $outlet): ?>
                    <option value="<?= $outlet['id'] ?>"><?= esc($outlet['nama_outlet']) ?></option>
                <?php endforeach ?>
            </select>
        </div>

        <!-- Pegawai -->
        <label>Nama Pegawai</label>
        <div id="pegawai-container">
            <div class="form-row mb-2">
                <div class="col-md-10">
                    <select name="user_id[]" class="form-control pegawai-select" required>
                        <option value="">-- Pilih Pegawai --</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-success btn-block add-pegawai">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Shift -->
        <div class="form-group">
            <label for="shift_id">Shift</label>
            <select name="shift_id" class="form-control" required>
                <option value="">-- Pilih Shift --</option>
                <?php foreach ($shifts as $shift): ?>
                    <option value="<?= $shift['id'] ?>">
                        <?= esc($shift['nama_shift']) ?> (<?= $shift['jam_mulai'] ?> - <?= $shift['jam_selesai'] ?>)
                    </option>
                <?php endforeach ?>
            </select>
        </div>

        <!-- Tanggal -->
        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required>
        </div>

        <!-- Tombol Submit -->
        <button type="submit" class="btn btn-primary mt-3">
            <i class="fas fa-save mr-1"></i> Simpan Jadwal
        </button>
    </form>
</div>

<!-- Script -->
<script>
    // Fetch Pegawai
    function fetchPegawai(outletId, targetSelect) {
        targetSelect.innerHTML = '<option value="">-- Pilih Pegawai --</option>';
        if (!outletId) return;

        fetch("/admin/users-by-outlet/" + outletId)
            .then(res => res.json())
            .then(data => {
                data.forEach(user => {
                    const opt = document.createElement('option');
                    opt.value = user.id;
                    opt.textContent = user.username;
                    targetSelect.appendChild(opt);
                });
            });
    }

    // Load pegawai saat outlet dipilih
    document.getElementById('outletSelect').addEventListener('change', function() {
        const outletId = this.value;
        document.querySelectorAll('.pegawai-select').forEach(select => {
            fetchPegawai(outletId, select);
        });
    });

    // Tambah Pegawai
    document.querySelector('.add-pegawai').addEventListener('click', function(e) {
        e.preventDefault();
        const outletId = document.getElementById('outletSelect').value;
        if (!outletId) {
            alert('Pilih outlet terlebih dahulu.');
            return;
        }

        const container = document.getElementById('pegawai-container');
        const row = document.createElement('div');
        row.classList.add('form-row', 'mb-2');
        row.innerHTML = `
            <div class="col-md-10">
                <select name="user_id[]" class="form-control pegawai-select" required>
                    <option value="">-- Pilih Pegawai --</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-block remove-pegawai">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;
        container.appendChild(row);

        const newSelect = row.querySelector('.pegawai-select');
        fetchPegawai(outletId, newSelect);

        // Event hapus pegawai
        row.querySelector('.remove-pegawai').addEventListener('click', function() {
            row.remove();
        });
    });
</script>

<?= $this->endSection() ?>