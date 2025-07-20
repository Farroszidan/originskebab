<!-- ===== FORM INPUT PRODUKSI ===== -->
<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Input Produksi BSJ</h1>

    <form action="<?= base_url('produksi/produksi/simpan'); ?>" method="post">
        <div class="card mb-4">
            <div class="card-body">
                <div class="form-group">
                    <label for="bsj_id">Pilih BSJ</label>
                    <select name="bsj_id" id="bsj_id" class="form-control" required>
                        <option value="">-- Pilih BSJ --</option>
                        <?php foreach ($bsj as $item) : ?>
                            <option value="<?= $item['id']; ?>"><?= $item['nama']; ?> (<?= $item['satuan']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="jumlah">Jumlah Produksi</label>
                    <input type="number" name="jumlah" id="jumlah" class="form-control" required min="1">
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header font-weight-bold">Kebutuhan Bahan (Otomatis)</div>
            <div class="card-body">
                <table class="table table-bordered" id="tabel-bahan">
                    <thead class="thead-dark">
                        <tr>
                            <th>Nama Bahan</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- akan diisi dengan JS dari komposisi_bahan_bsj -->
                    </tbody>
                </table>
                <div class="mt-3">
                    <h5>Total Biaya Bahan: <span id="total-biaya-bahan">Rp 0</span></h5>
                    <h5>Total Biaya Produksi: <span id="total-biaya-produksi">Rp 0</span></h5>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header font-weight-bold">Tambahkan Biaya Tenaga Kerja & Overhead</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Biaya Produksi</label>
                        <input type="text" class="form-control" readonly value="Rp <?= number_format($total_tenaga_kerja, 0, ',', '.'); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Biaya Overhead Pabrik</label>
                        <input type="text" class="form-control" readonly value="Rp <?= number_format($total_bop, 0, ',', '.'); ?>">
                    </div>
                </div>

            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-success">Simpan Produksi</button>
            <a href="<?= base_url('produksi/produksi/daftar'); ?>" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<!-- Script otomatisasi bahan -->
<script>
    const bsjData = <?= json_encode($komposisi); ?>; // hasil join bsj-komposisi-bahan dikirim dari controller
    const bahanData = <?= json_encode($bahan_all); ?>; // semua data bahan untuk ambil harga_satuan

    document.getElementById('bsj_id').addEventListener('change', updateBahan);
    document.getElementById('jumlah').addEventListener('input', updateBahan);

    function updateBahan() {
        const bsjId = document.getElementById('bsj_id').value;
        const jumlah = parseInt(document.getElementById('jumlah').value);
        const tbody = document.querySelector('#tabel-bahan tbody');
        tbody.innerHTML = '';

        if (!bsjId || isNaN(jumlah)) {
            document.getElementById('total-biaya-bahan').innerText = 'Rp 0';
            document.getElementById('total-biaya-produksi').innerText = 'Rp 0';
            return;
        }

        const komposisi = bsjData.filter(k => k.id_bsj == bsjId);
        let totalBiaya = 0;

        komposisi.forEach(k => {
            const totalJumlahGram = jumlah * parseFloat(k.jumlah); // total kebutuhan bahan dalam gram
            const totalJumlahKg = totalJumlahGram / 1000; // konversi ke kilogram
            const bahan = bahanData.find(b => b.id == k.id_bahan);
            const harga = parseFloat(bahan.harga_satuan);
            const subtotal = totalJumlahKg * harga; // menggunakan jumlah dalam kg untuk perhitungan
            totalBiaya += subtotal;

            // Tampilkan satuan di bawah jumlah
            const satuan = bahan.satuan ? bahan.satuan : '';

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${bahan.nama}
                    <input type="hidden" name="bahan_baku[${bahan.id}]" value="${totalJumlahGram}">
                </td>
                <td>${bahan.kategori}</td>
                <td>${totalJumlahKg.toFixed(2)}<br><span class="badge badge-secondary">${satuan}</span></td>
                <td>Rp ${harga.toLocaleString('id-ID')}</td>
                <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
            `;
            tbody.appendChild(row);
        });
        document.getElementById('total-biaya-bahan').innerText = 'Rp ' + totalBiaya.toLocaleString('id-ID');

        // Ambil biaya tenaga kerja & bop dari PHP
        const biayaTenagaKerja = <?= (int)$total_tenaga_kerja ?>;
        const biayaBOP = <?= (int)$total_bop ?>;
        const totalProduksi = totalBiaya + biayaTenagaKerja + biayaBOP;
        document.getElementById('total-biaya-produksi').innerText = 'Rp ' + totalProduksi.toLocaleString('id-ID');
    }
</script>

<?= $this->endSection(); ?>