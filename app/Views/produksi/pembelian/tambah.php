<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Tambah Pembelian</h6>
        </div>
        <div class="card-body">
            <form action="<?= base_url('produksi/pembelian/simpan-pembelian'); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group row">
                    <div class="col-sm-2">
                        <label for="tanggal" class="col-form-label">Tanggal Pembelian</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="col-sm-2">
                        <label for="no_nota" class="col-form-label">No. Nota</label>
                        <input type="text" name="no_nota" class="form-control" required>
                    </div>
                    <div class="col-sm-5">
                        <label for="pemasok_id" class="col-form-label">Pemasok</label>
                        <select name="pemasok_id" id="pemasok_id" class="form-control" required>
                            <option value="">-- Pilih Pemasok --</option>
                            <?php foreach ($pemasok as $row): ?>
                                <option value="<?= $row['id']; ?>" data-kategori="<?= $row['kategori']; ?>">
                                    <?= $row['nama'] ?> (<?= ucfirst($row['kategori']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label for="bukti" class="col-form-label">Upload Bukti Pembelian</label>
                        <input type="file" name="bukti" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>

                <hr>
                <h5 class="mb-3">Barang Dibeli</h5>

                <div class="form-row mb-3 align-items-end">
                    <div class="col-md-4">
                        <label>Bahan</label>
                        <select id="bahan-select" class="form-control">
                            <option value="">-- Pilih Bahan --</option>
                            <?php foreach ($bahan as $row): ?>
                                <option value="<?= $row['id'] ?>"
                                    data-nama="<?= $row['nama'] ?>"
                                    data-jenis="<?= $row['jenis'] ?>"
                                    data-satuan="<?= $row['satuan'] ?>">
                                    <?= $row['nama'] ?> (<?= ucfirst($row['jenis']) ?>)
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Jumlah</label>
                        <input type="number" step="1" id="jumlah" class="form-control" placeholder="Jumlah">
                    </div>
                    <div class="col-md-3">
                        <label>Harga Satuan</label>
                        <input type="number" step="1" id="harga-satuan" class="form-control" placeholder="Harga Satuan">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" id="btn-tambah">
                            <i class="fas fa-plus"></i> Tambah
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="daftar-pembelian">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Total Pembelian</label>
                    <div class="col-sm-10">
                        <input type="text" name="total" class="form-control font-weight-bold" id="total-pembelian" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-10 offset-sm-2">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-save"></i> Simpan Pembelian
                        </button>
                        <a href="<?= base_url('produksi/pembelian') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pemasokSelect = document.getElementById('pemasok_id');
        const bahanSelect = document.getElementById('bahan-select');
        const btnTambah = document.getElementById('btn-tambah');
        const tableBody = document.querySelector('#daftar-pembelian tbody');
        const totalInput = document.getElementById('total-pembelian');

        // Filter bahan berdasarkan kategori pemasok
        pemasokSelect.addEventListener('change', function() {
            const selectedKategori = this.options[this.selectedIndex].dataset.kategori;
            Array.from(bahanSelect.options).forEach(option => {
                if (option.value === "") return;
                const jenis = option.dataset.jenis;
                option.style.display = (jenis === selectedKategori) ? 'block' : 'none';
            });
            bahanSelect.selectedIndex = 0;
        });

        // Tambah barang ke tabel
        btnTambah.addEventListener('click', function() {
            const selectedOption = bahanSelect.options[bahanSelect.selectedIndex];
            const jumlah = parseFloat(document.getElementById('jumlah').value);
            const harga = parseFloat(document.getElementById('harga-satuan').value);

            if (!selectedOption.value || isNaN(jumlah) || isNaN(harga)) {
                alert('Harap lengkapi semua field!');
                return;
            }

            const subtotal = jumlah * harga;
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    ${selectedOption.dataset.nama}
                    <input type="hidden" name="bahan_id[]" value="${selectedOption.value}">
                </td>
                <td>${selectedOption.dataset.jenis}</td>
                <td>${selectedOption.dataset.satuan}</td>
                <td>
                    ${jumlah}
                    <input type="hidden" name="jumlah[]" value="${jumlah}">
                </td>
                <td>
                    ${harga.toLocaleString('id-ID')}
                    <input type="hidden" name="harga_satuan[]" value="${harga}">
                </td>
                <td>${subtotal.toLocaleString('id-ID')}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm btn-hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

            tableBody.appendChild(newRow);
            hitungTotal();
            resetFormInput();
        });

        // Hapus barang dari tabel
        tableBody.addEventListener('click', function(e) {
            if (e.target.closest('.btn-hapus')) {
                e.target.closest('tr').remove();
                hitungTotal();
            }
        });

        // Fungsi hitung total
        function hitungTotal() {
            let total = 0;
            const rows = tableBody.querySelectorAll('tr');

            rows.forEach(row => {
                const jumlah = parseFloat(row.querySelector('input[name="jumlah[]"]').value);
                const harga = parseFloat(row.querySelector('input[name="harga_satuan[]"]').value);
                total += jumlah * harga;
            });

            totalInput.value = total.toLocaleString('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            });
        }

        // Reset form input setelah tambah
        function resetFormInput() {
            document.getElementById('jumlah').value = '';
            document.getElementById('harga-satuan').value = '';
            bahanSelect.selectedIndex = 0;
        }
    });
</script>

<?= $this->endSection(); ?>