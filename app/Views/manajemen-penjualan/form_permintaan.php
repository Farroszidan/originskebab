<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container-fluid mt-4">
    <h4 class="mb-4 text-gray-800 font-weight-bold">Form Permintaan Barang ke Produksi</h4>

    <form action="<?= base_url('manajemen-penjualan/storePermintaan') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="tanggal">Tanggal Permintaan</label>
                <input type="date" name="tanggal" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label for="tujuan">Tujuan</label>
                <input type="text" class="form-control" value="Produksi" readonly>
                <input type="hidden" name="tujuan" value="produksi">
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Detail Barang</h6>
            </div>
            <div class="card-body">
                <div id="barang-container"></div>
                <button type="button" class="btn btn-sm btn-success mt-2" onclick="tambahBarang()">
                    <i class="fas fa-plus"></i> Tambah Barang
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="catatan">Catatan</label>
            <textarea name="catatan" class="form-control" rows="3"></textarea>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Ajukan Permintaan</button>
            <button type="reset" class="btn btn-secondary ml-2"><i class="fas fa-undo"></i> Reset</button>
            <button type="button" class="btn btn-secondary ml-2" onclick="window.history.back();"><i class="fas fa-arrow-left"></i> Kembali</button>
        </div>
    </form>
</div>

<script>
    let indexBarang = 0;
    const barangBSJ = <?= json_encode($barang_bsj) ?>;
    const barangBahan = <?= json_encode($bahan) ?>;

    function tambahBarang() {
        const container = document.getElementById('barang-container');
        const div = document.createElement('div');
        div.className = "form-row align-items-center mb-3";
        div.innerHTML = `
            <div class="col-md-2">
                <select name="barang[${indexBarang}][tipe]" class="form-control tipe-barang" required onchange="updateBarangSelect(this, ${indexBarang})">
                    <option value="">-- Tipe --</option>
                    <option value="bsj">BSJ</option>
                    <option value="bahan">Bahan Baku</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="barang[${indexBarang}][barang_id]" class="form-control barang-select" required disabled onchange="updateSatuan(this, ${indexBarang})">
                    <option value="">-- Pilih Barang --</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="barang[${indexBarang}][jumlah]" class="form-control" placeholder="Jumlah" required min="1" step="1">
            </div>
            <div class="col-md-2">
                <input type="text" name="barang[${indexBarang}][satuan]" class="form-control satuan-barang" readonly required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
        indexBarang++;
    }

    function updateBarangSelect(select, idx) {
        const tipe = select.value;
        const row = select.closest('.form-row');
        const barangSelect = row.querySelector('.barang-select');
        barangSelect.innerHTML = '<option value="">-- Pilih Barang --</option>';
        barangSelect.disabled = false;

        let data = tipe === 'bsj' ? barangBSJ : barangBahan;

        data.forEach(item => {
            barangSelect.innerHTML += `<option value="${item.id}" data-satuan="${item.satuan}" data-stok="${item.stok}">${item.nama}</option>`;
        });

        row.querySelector('.satuan-barang').value = '';
    }


    function updateSatuan(select, idx) {
        const selected = select.options[select.selectedIndex];
        const satuan = selected.getAttribute('data-satuan') || '';
        const nama = selected.textContent.replace(/\s*\(Stok:.*?\)/, '') || ''; // Ambil nama dari isi <option>

        const row = select.closest('.form-row');

        const satuanInput = row.querySelector('.satuan-barang');
        if (satuanInput) satuanInput.value = satuan;

        // Tambahkan input hidden untuk nama barang
        let inputNama = row.querySelector(`input[name="barang[${idx}][nama]"]`);
        if (!inputNama) {
            inputNama = document.createElement('input');
            inputNama.type = 'hidden';
            inputNama.name = `barang[${idx}][nama]`;
            row.appendChild(inputNama);
        }

        inputNama.value = nama;
    }
</script>

<?= $this->endSection() ?>