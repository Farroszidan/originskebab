<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Input Perintah Kerja Produksi BSJ</h1>
    <form action="<?= base_url('admin/perintah-kerja/simpan'); ?>" method="post">
        <div class="card mb-4">
            <div class="card-header font-weight-bold">Input Produksi BSJ</div>
            <div class="card-body">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-5">
                        <label>Pilih BSJ</label>
                        <select name="bsj_id" id="bsj_id" class="form-control" required>
                            <option value="">-- Pilih BSJ --</option>
                            <?php foreach ($bsj as $item) : ?>
                                <option value="<?= $item['id']; ?>" data-satuan="<?= esc($item['satuan']); ?>"><?= esc($item['nama']); ?> (<?= esc($item['satuan']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label id="label-jumlah">Jumlah</label>
                        <input type="number" id="input-jumlah" class="form-control" min="1">
                    </div>
                    <div class="form-group col-md-2">
                        <button type="button" class="btn btn-primary" id="btn-add-bsj">Add</button>
                    </div>
                </div>
                <div id="preview-kebutuhan" class="mt-2"></div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header font-weight-bold">Daftar Produksi & Kebutuhan Bahan</div>
            <div class="card-body">
                <table class="table table-bordered" id="tabel-daftar-bsj">
                    <thead class="thead-dark">
                        <tr>
                            <th>BSJ</th>
                            <th>Jumlah</th>
                            <th>Kebutuhan Bahan</th>
                            <th>Total Biaya</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Diisi otomatis dengan JS -->
                    </tbody>
                </table>
                <div class="mt-3">
                    <h5>Total Biaya Bahan: <span id="total-biaya-bahan">Rp 0</span></h5>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header font-weight-bold">Distribusi BSJ ke Outlet</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Outlet</th>
                            <th>Kulit Kebab</th>
                            <th>Olahan Daging Ayam</th>
                            <th>Olahan Daging Sapi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($outlets as $outlet): ?>
                            <tr>
                                <td><?= esc($outlet['nama_outlet']) ?></td>
                                <td><input type="number" name="distribusi[<?= $outlet['id'] ?>][kulit]" class="form-control" min="0" value="0"></td>
                                <td><input type="number" name="distribusi[<?= $outlet['id'] ?>][ayam]" class="form-control" min="0" value="0"></td>
                                <td><input type="number" name="distribusi[<?= $outlet['id'] ?>][sapi]" class="form-control" min="0" value="0"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-success">Simpan Perintah Kerja</button>
            <a href="<?= base_url('admin/dashboard'); ?>" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<!-- Script kebutuhan bahan interaktif -->
<script>
    const komposisiData = <?= json_encode($komposisi_bsj); ?>;
    const bahanData = <?= json_encode($bahan_all); ?>;

    // Simpan daftar produksi sementara
    let daftarProduksi = [];

    // Ubah label jumlah sesuai BSJ

    document.getElementById('bsj_id').addEventListener('change', function() {
        const select = this;
        const satuan = select.options[select.selectedIndex].getAttribute('data-satuan');
        let label = 'Jumlah';
        if (satuan === 'pcs') label = 'Jumlah (pcs)';
        else if (satuan === 'porsi') label = 'Jumlah (porsi)';
        document.getElementById('label-jumlah').innerText = label;
        previewKebutuhan();
    });
    document.getElementById('input-jumlah').addEventListener('input', previewKebutuhan);

    function previewKebutuhan() {
        const bsjId = document.getElementById('bsj_id').value;
        const select = document.getElementById('bsj_id');
        const satuan = select.options[select.selectedIndex] ? select.options[select.selectedIndex].getAttribute('data-satuan') : '';
        const jumlah = parseInt(document.getElementById('input-jumlah').value) || 0;
        if (!bsjId || jumlah <= 0) {
            document.getElementById('preview-kebutuhan').innerHTML = '';
            return;
        }
        // Hitung kebutuhan bahan
        const gramPerPorsi = 45;
        let kebutuhan = [];
        let totalBiaya = 0;
        komposisiData.forEach(k => {
            if (k.id_bsj == bsjId) {
                let totalJumlahGram = 0;
                if (satuan === 'pcs') totalJumlahGram = jumlah * parseFloat(k.jumlah);
                else totalJumlahGram = jumlah * gramPerPorsi;
                const totalJumlahKg = totalJumlahGram / 1000;
                const bahan = bahanData.find(b => b.id == k.id_bahan);
                const harga = parseFloat(bahan.harga_satuan);
                const subtotal = totalJumlahKg * harga;
                totalBiaya += subtotal;
                kebutuhan.push({
                    nama: bahan.nama,
                    kategori: bahan.kategori,
                    jumlahKg: totalJumlahKg,
                    harga,
                    subtotal
                });
            }
        });
        let html = '<b>Kebutuhan Bahan:</b><ul>';
        kebutuhan.forEach(b => {
            html += `<li>${b.nama} (${b.kategori}): ${b.jumlahKg.toFixed(2)} kg, Rp ${b.subtotal.toLocaleString('id-ID')}</li>`;
        });
        html += `</ul><b>Total Biaya:</b> Rp ${totalBiaya.toLocaleString('id-ID')}`;
        document.getElementById('preview-kebutuhan').innerHTML = html;
    }

    document.getElementById('btn-add-bsj').addEventListener('click', function() {
        const bsjId = document.getElementById('bsj_id').value;
        const select = document.getElementById('bsj_id');
        const satuan = select.options[select.selectedIndex] ? select.options[select.selectedIndex].getAttribute('data-satuan') : '';
        const jumlah = parseInt(document.getElementById('input-jumlah').value) || 0;
        if (!bsjId || jumlah <= 0) return;
        // Hitung kebutuhan bahan
        const gramPerPorsi = 45;
        let kebutuhan = [];
        let totalBiaya = 0;
        komposisiData.forEach(k => {
            if (k.id_bsj == bsjId) {
                let totalJumlahGram = 0;
                if (satuan === 'pcs') totalJumlahGram = jumlah * parseFloat(k.jumlah);
                else totalJumlahGram = jumlah * gramPerPorsi;
                const totalJumlahKg = totalJumlahGram / 1000;
                const bahan = bahanData.find(b => b.id == k.id_bahan);
                const harga = parseFloat(bahan.harga_satuan);
                const subtotal = totalJumlahKg * harga;
                totalBiaya += subtotal;
                kebutuhan.push({
                    nama: bahan.nama,
                    kategori: bahan.kategori,
                    jumlahKg: totalJumlahKg,
                    harga,
                    subtotal
                });
            }
        });
        daftarProduksi.push({
            bsjId,
            satuan,
            jumlah,
            kebutuhan,
            totalBiaya
        });
        renderDaftarProduksi();
        // Reset input
        document.getElementById('bsj_id').value = '';
        document.getElementById('input-jumlah').value = '';
        document.getElementById('label-jumlah').innerText = 'Jumlah';
        document.getElementById('preview-kebutuhan').innerHTML = '';
    });

    function renderDaftarProduksi() {
        const tbody = document.querySelector('#tabel-daftar-bsj tbody');
        tbody.innerHTML = '';
        let totalBiayaAll = 0;
        daftarProduksi.forEach((item, idx) => {
            let kebutuhanHtml = '<ul>';
            item.kebutuhan.forEach(b => {
                kebutuhanHtml += `<li>${b.nama} (${b.kategori}): ${b.jumlahKg.toFixed(2)} kg, Rp ${b.subtotal.toLocaleString('id-ID')}</li>`;
            });
            kebutuhanHtml += '</ul>';
            totalBiayaAll += item.totalBiaya;
            // Cari nama BSJ dari data $bsj
            let namaBsj = '';
            <?php if (!empty($bsj)) : ?>
                const bsjArr = <?php echo json_encode($bsj); ?>;
                const found = bsjArr.find(b => b.id == item.bsjId);
                if (found) namaBsj = found.nama + ' (' + found.satuan + ')';
            <?php endif; ?>
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${namaBsj}</td>
                <td>${item.jumlah} ${item.satuan ? '('+item.satuan+')' : ''}</td>
                <td>${kebutuhanHtml}</td>
                <td>Rp ${item.totalBiaya.toLocaleString('id-ID')}</td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusProduksi(${idx})">Hapus</button></td>
            `;
            tbody.appendChild(tr);
        });
        document.getElementById('total-biaya-bahan').innerText = 'Rp ' + totalBiayaAll.toLocaleString('id-ID');
    }

    function hapusProduksi(idx) {
        daftarProduksi.splice(idx, 1);
        renderDaftarProduksi();
    }

    function ucwords(str) {
        return (str + '').replace(/^(.)|\s+(.)/g, function($1) {
            return $1.toUpperCase();
        });
    }
</script>

<?= $this->endSection(); ?>