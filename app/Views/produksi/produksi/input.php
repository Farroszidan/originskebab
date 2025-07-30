<!-- ===== FORM INPUT PRODUKSI ===== -->
<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Input Produksi BSJ</h1>

    <form action="<?= base_url('produksi/produksi/simpan'); ?>" method="post">
        <div class="card mb-4">
            <div class="card-body">
                <div class="form-group">
                    <label for="tanggal">Tanggal Produksi</label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control" required value="<?= date('Y-m-d'); ?>">
                </div>
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
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right font-weight-bold">Total Biaya Bahan</td>
                            <td class="font-weight-bold"><span id="total-biaya-bahan">Rp 0</span></td>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header font-weight-bold">Biaya Tenaga Kerja & Overhead (Otomatis)</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Biaya Tenaga Kerja</label>
                        <input type="text" id="biaya-tenaga-kerja" class="form-control" readonly value="Rp 0">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Biaya Overhead Pabrik</label>
                        <input type="text" id="biaya-bop" class="form-control" readonly value="Rp 0">
                        <small id="jenis-bop-info" class="form-text text-muted"></small>
                    </div>
                </div>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right font-weight-bold">Total Biaya Tenaga Kerja & BOP</td>
                        <td class="font-weight-bold"><span id="total-biaya-tenaga-kerja-bop">Rp 0</span></td>
                    </tr>
                </tfoot>
                <div class="mt-3">
                    <h5>Total Biaya Produksi: <span id="total-biaya-produksi">Rp 0</span></h5>
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
    const bopData = <?= json_encode($bop_all); ?>; // semua data BOP
    const tkData = <?= json_encode($tenaga_kerja_all); ?>; // semua data tenaga kerja

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
            document.getElementById('biaya-tenaga-kerja').value = 'Rp 0';
            document.getElementById('biaya-bop').value = 'Rp 0';
            document.getElementById('jenis-bop-info').innerText = '';
            return;
        }

        const komposisi = bsjData.filter(k => k.id_bsj == bsjId);
        let totalBiaya = 0;

        // Deteksi apakah BSJ olahan daging ayam/sapi
        let bsjNama = '';
        const bsjSelect = document.getElementById('bsj_id');
        if (bsjSelect) {
            const selectedOption = bsjSelect.options[bsjSelect.selectedIndex];
            if (selectedOption) {
                bsjNama = selectedOption.textContent.toLowerCase();
            }
        }
        const isOlahanDaging = bsjNama.includes('olahan daging ayam') || bsjNama.includes('olahan daging sapi');

        komposisi.forEach(k => {
            const bahan = bahanData.find(b => b.id == k.id_bahan);
            let totalJumlahGram = 0;
            let totalJumlahKg = 0;
            if (isOlahanDaging) {
                // Daging: 45 gram per porsi
                if (bahan.kategori === 'baku' && (bahan.nama.toLowerCase().includes('daging sapi') || bahan.nama.toLowerCase().includes('daging ayam'))) {
                    totalJumlahGram = jumlah * 45;
                } else {
                    // Bahan lain: komposisi per 1kg × 45/1000 × jumlah produksi
                    totalJumlahGram = jumlah * (parseFloat(k.jumlah) * 45 / 1000);
                }
                totalJumlahKg = totalJumlahGram / 1000;
            } else {
                // Default: kebutuhan bahan dalam gram (asumsi per kg)
                totalJumlahGram = jumlah * parseFloat(k.jumlah);
                totalJumlahKg = totalJumlahGram / 1000;
            }
            const harga = parseFloat(bahan.harga_satuan);
            const subtotal = totalJumlahKg * harga;
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

        // Otomatis pilih jenis BOP berdasarkan jumlah produksi
        let jenisBOP = '';
        if (jumlah < 500) {
            jenisBOP = 'sedikit';
        } else if (jumlah >= 500 && jumlah <= 1000) {
            jenisBOP = 'sedang';
        } else if (jumlah > 1000) {
            jenisBOP = 'banyak';
        }

        // Ambil nilai BOP sesuai jenis, lalu bagi 3
        let totalBOP = 0;
        bopData.forEach(bop => {
            if (bop.jenis_bsj === jenisBOP) {
                totalBOP += parseInt(bop.biaya);
            }
        });
        totalBOP = totalBOP / 3;
        document.getElementById('biaya-bop').value = 'Rp ' + Math.round(totalBOP).toLocaleString('id-ID');
        document.getElementById('jenis-bop-info').innerText = jenisBOP ? `Jenis BOP: ${jenisBOP.charAt(0).toUpperCase() + jenisBOP.slice(1)}` : '';

        // Biaya tenaga kerja: totalTK × jumlah produksi
        let totalTK = 0;
        tkData.forEach(tk => {
            totalTK += parseInt(tk.biaya);
        });
        totalTK = totalTK * jumlah;
        document.getElementById('biaya-tenaga-kerja').value = 'Rp ' + Math.round(totalTK).toLocaleString('id-ID');
        const totalBiayaTenagaKerjaBOP = totalBOP + totalTK;
        document.getElementById('total-biaya-tenaga-kerja-bop').innerText = 'Rp ' + Math.round(totalBiayaTenagaKerjaBOP).toLocaleString('id-ID');
        const totalProduksi = totalBiaya + totalTK + totalBOP;
        document.getElementById('total-biaya-produksi').innerText = 'Rp ' + Math.round(totalProduksi).toLocaleString('id-ID');
    }
</script>

<?= $this->endSection(); ?>