<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Input Perintah Kerja Produksi BSJ</h1>
    <form id="form-perintah-kerja" action="<?= base_url('admin/perintah-kerja/simpan'); ?>" method="post">
        <div class="card mb-4">
            <div class="card-header font-weight-bold">Input Produksi BSJ</div>
            <div class="card-body">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-3">
                        <label>Tipe</label>
                        <select id="tipe-produk" class="form-control">
                            <option value="">-- Tipe --</option>
                            <option value="bsj">BSJ</option>
                            <option value="bahan">Bahan Baku</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4" id="container-pilihan">
                        <!-- Pilihan BSJ atau Bahan Baku akan muncul di sini -->
                    </div>
                    <div class="form-group col-md-3">
                        <label id="label-jumlah">Jumlah</label>
                        <input type="number" id="input-jumlah" class="form-control" min="1">
                    </div>
                    <div class="form-group col-md-2">
                        <button type="button" class="btn btn-primary" id="btn-add-produk">Add</button>
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
                            <th>BSJ/Bahan Baku</th>
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
                <div class="mt-3">
                    <button type="button" class="btn btn-info" id="btn-rangkum-bahan">Rangkum Kebutuhan Bahan</button>
                </div>
                <div class="mt-3" id="tabel-rangkuman-bahan"></div>
            </div>
        </div>

        <!-- Distribusi BSJ ke Outlet section removed as requested -->

        <div class="form-group">
            <button type="submit" class="btn btn-success">Simpan Perintah Kerja</button>
            <a href="<?= base_url('admin/dashboard'); ?>" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<!-- Script kebutuhan bahan interaktif -->
<script>
    // Data bahan baku dan bsj
    const bahanBakuList = <?= json_encode($bahan_all); ?>;
    const bsjList = <?= json_encode($bsj); ?>;

    // Event tipe produk
    document.getElementById('tipe-produk').addEventListener('change', function() {
        const tipe = this.value;
        const container = document.getElementById('container-pilihan');
        let html = '';
        if (tipe === 'bsj') {
            html += '<label>Pilih BSJ</label>';
            html += '<select id="produk-id" class="form-control">';
            html += '<option value="">-- Pilih BSJ --</option>';
            bsjList.forEach(item => {
                html += `<option value="${item.id}" data-satuan="${item.satuan}">${item.nama} (${item.satuan})</option>`;
            });
            html += '</select>';
        } else if (tipe === 'bahan') {
            html += '<label>Pilih Bahan Baku</label>';
            html += '<select id="produk-id" class="form-control">';
            html += '<option value="">-- Pilih Bahan Baku --</option>';
            bahanBakuList.forEach(item => {
                html += `<option value="${item.id}" data-satuan="${item.satuan}" data-harga="${item.harga_satuan}">${item.nama} (${item.satuan})</option>`;
            });
            html += '</select>';
        }
        container.innerHTML = html;
        document.getElementById('label-jumlah').innerText = 'Jumlah';
        document.getElementById('preview-kebutuhan').innerHTML = '';
    });

    // Event perubahan produk
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'produk-id') {
            const tipe = document.getElementById('tipe-produk').value;
            const select = e.target;
            const satuan = select.options[select.selectedIndex] ? select.options[select.selectedIndex].getAttribute('data-satuan') : '';
            let label = 'Jumlah';
            if (satuan === 'pcs') label = 'Jumlah (pcs)';
            else if (satuan === 'porsi') label = 'Jumlah (porsi)';
            document.getElementById('label-jumlah').innerText = label;
            previewKebutuhan();
        }
    });
    document.getElementById('input-jumlah').addEventListener('input', previewKebutuhan);

    function previewKebutuhan() {
        const tipe = document.getElementById('tipe-produk').value;
        const select = document.getElementById('produk-id');
        const jumlah = parseInt(document.getElementById('input-jumlah').value) || 0;
        if (!select || !select.value || jumlah <= 0) {
            document.getElementById('preview-kebutuhan').innerHTML = '';
            return;
        }
        if (tipe === 'bsj') {
            const bsjId = select.value;
            const satuan = select.options[select.selectedIndex] ? select.options[select.selectedIndex].getAttribute('data-satuan') : '';
            // Hitung kebutuhan bahan berdasarkan input jumlah porsi
            let kebutuhan = [];
            let totalBiaya = 0;
            const porsiPerKg = 1000 / 45;
            const bsjNama = select.options[select.selectedIndex] ? select.options[select.selectedIndex].textContent.toLowerCase() : '';
            const isKulitKebab = bsjNama.includes('kulit kebab');
            komposisiData.forEach(k => {
                if (k.id_bsj == bsjId) {
                    const bahan = bahanData.find(b => b.id == k.id_bahan);
                    let satuanBahan = bahan && bahan.satuan ? bahan.satuan : 'kg';
                    let totalJumlah = 0;
                    if (isKulitKebab) {
                        if (satuanBahan === 'kg' || satuanBahan === 'liter') {
                            totalJumlah = (parseFloat(k.jumlah) / 1000) * jumlah;
                        } else {
                            totalJumlah = parseFloat(k.jumlah) * jumlah;
                        }
                    } else if (bahan.kategori === 'baku' && (bahan.nama.toLowerCase().includes('daging sapi') || bahan.nama.toLowerCase().includes('daging ayam'))) {
                        satuanBahan = 'kg';
                        totalJumlah = jumlah * 0.045;
                    } else {
                        let komposisiPerKg = parseFloat(k.jumlah) / 1000;
                        let kebutuhanPerPorsi = komposisiPerKg / porsiPerKg;
                        totalJumlah = kebutuhanPerPorsi * jumlah;
                    }
                    let subtotal = 0;
                    if (satuanBahan === 'liter' || satuanBahan === 'kg') {
                        subtotal = totalJumlah * parseFloat(bahan.harga_satuan);
                    } else if (satuanBahan === 'ml') {
                        subtotal = (totalJumlah / 1000) * parseFloat(bahan.harga_satuan);
                    } else if (satuanBahan === 'pcs' || satuanBahan === 'butir') {
                        subtotal = totalJumlah * parseFloat(bahan.harga_satuan);
                    } else {
                        subtotal = totalJumlah * parseFloat(bahan.harga_satuan);
                    }
                    totalBiaya += subtotal;
                    kebutuhan.push({
                        nama: bahan.nama,
                        kategori: bahan.kategori,
                        jumlah: totalJumlah,
                        satuan: satuanBahan,
                        harga: bahan.harga_satuan,
                        subtotal
                    });
                }
            });
            let html = '<b>Kebutuhan Bahan:</b><ul>';
            kebutuhan.forEach(b => {
                html += `<li>${b.nama} (${b.kategori}): ${b.jumlah.toFixed(2)} ${b.satuan}, Rp ${b.subtotal.toLocaleString('id-ID')}</li>`;
            });
            html += `</ul><b>Total Biaya:</b> Rp ${totalBiaya.toLocaleString('id-ID')}`;
            document.getElementById('preview-kebutuhan').innerHTML = html;
        } else if (tipe === 'bahan') {
            // Untuk bahan baku, langsung tampilkan nama dan subtotal
            const bahanId = select.value;
            const bahan = bahanBakuList.find(b => b.id == bahanId);
            if (!bahan) return;
            let subtotal = jumlah * parseFloat(bahan.harga_satuan);
            let html = `<b>Kebutuhan Bahan:</b><ul>`;
            html += `<li>${bahan.nama} (baku): ${jumlah} ${bahan.satuan}, Rp ${subtotal.toLocaleString('id-ID')}</li>`;
            html += `</ul><b>Total Biaya:</b> Rp ${subtotal.toLocaleString('id-ID')}`;
            document.getElementById('preview-kebutuhan').innerHTML = html;
        }
    }

    document.getElementById('btn-add-produk').addEventListener('click', function() {
        const tipe = document.getElementById('tipe-produk').value;
        const select = document.getElementById('produk-id');
        const jumlah = parseInt(document.getElementById('input-jumlah').value) || 0;
        if (!select || !select.value || jumlah <= 0) return;
        if (tipe === 'bsj') {
            const bsjId = select.value;
            const satuan = select.options[select.selectedIndex] ? select.options[select.selectedIndex].getAttribute('data-satuan') : '';
            let kebutuhan = [];
            let totalBiaya = 0;
            const porsiPerKg = 1000 / 45;
            const bsjNama = select.options[select.selectedIndex] ? select.options[select.selectedIndex].textContent.toLowerCase() : '';
            const isKulitKebab = bsjNama.includes('kulit kebab');
            komposisiData.forEach(k => {
                if (k.id_bsj == bsjId) {
                    const bahan = bahanData.find(b => b.id == k.id_bahan);
                    let satuanBahan = bahan && bahan.satuan ? bahan.satuan : 'kg';
                    let totalJumlah = 0;
                    if (isKulitKebab) {
                        if (satuanBahan === 'kg' || satuanBahan === 'liter') {
                            totalJumlah = (parseFloat(k.jumlah) / 1000) * jumlah;
                        } else {
                            totalJumlah = parseFloat(k.jumlah) * jumlah;
                        }
                    } else if (bahan.kategori === 'baku' && (bahan.nama.toLowerCase().includes('daging sapi') || bahan.nama.toLowerCase().includes('daging ayam'))) {
                        satuanBahan = 'kg';
                        totalJumlah = jumlah * 0.045;
                    } else {
                        let komposisiPerKg = parseFloat(k.jumlah) / 1000;
                        let kebutuhanPerPorsi = komposisiPerKg / porsiPerKg;
                        totalJumlah = kebutuhanPerPorsi * jumlah;
                    }
                    let subtotal = 0;
                    if (satuanBahan === 'liter' || satuanBahan === 'kg') {
                        subtotal = totalJumlah * parseFloat(bahan.harga_satuan);
                    } else if (satuanBahan === 'ml') {
                        subtotal = (totalJumlah / 1000) * parseFloat(bahan.harga_satuan);
                    } else if (satuanBahan === 'pcs' || satuanBahan === 'butir') {
                        subtotal = totalJumlah * parseFloat(bahan.harga_satuan);
                    } else {
                        subtotal = totalJumlah * parseFloat(bahan.harga_satuan);
                    }
                    totalBiaya += subtotal;
                    kebutuhan.push({
                        nama: bahan.nama,
                        kategori: bahan.kategori,
                        jumlah: totalJumlah,
                        satuan: satuanBahan,
                        harga: bahan.harga_satuan,
                        subtotal
                    });
                }
            });
            daftarProduksi.push({
                tipe: 'bsj',
                nama: select.options[select.selectedIndex].textContent,
                jumlah,
                satuan,
                kebutuhan,
                totalBiaya
            });
        } else if (tipe === 'bahan') {
            const bahanId = select.value;
            const bahan = bahanBakuList.find(b => b.id == bahanId);
            if (!bahan) return;
            let subtotal = jumlah * parseFloat(bahan.harga_satuan);
            daftarProduksi.push({
                tipe: 'bahan',
                nama: bahan.nama,
                jumlah,
                satuan: bahan.satuan,
                kebutuhan: [{
                    nama: bahan.nama,
                    kategori: 'baku',
                    jumlah,
                    satuan: bahan.satuan,
                    harga: bahan.harga_satuan,
                    subtotal
                }],
                totalBiaya: subtotal
            });
        }
        renderDaftarProduksi();
        // Reset input
        document.getElementById('container-pilihan').innerHTML = '';
        document.getElementById('input-jumlah').value = '';
        document.getElementById('label-jumlah').innerText = 'Jumlah';
        document.getElementById('preview-kebutuhan').innerHTML = '';
        document.getElementById('tipe-produk').value = '';
    });
    // Fungsi untuk merangkum kebutuhan bahan dari semua produksi
    function getRangkumanBahan() {
        let bahanGabungan = {};
        daftarProduksi.forEach(item => {
            item.kebutuhan.forEach(bahan => {
                let key = bahan.nama + '|' + bahan.satuan;
                if (!bahanGabungan[key]) {
                    bahanGabungan[key] = {
                        nama: bahan.nama,
                        kategori: bahan.kategori,
                        jumlah: 0,
                        satuan: bahan.satuan,
                        harga: bahan.harga,
                        subtotal: 0
                    };
                }
                bahanGabungan[key].jumlah += parseFloat(bahan.jumlah);
                bahanGabungan[key].subtotal += parseFloat(bahan.subtotal);
            });
        });
        return Object.values(bahanGabungan);
    }

    // Event tombol rangkum
    document.getElementById('btn-rangkum-bahan').addEventListener('click', function() {
        const rangkuman = getRangkumanBahan();
        let html = '<h5>Rangkuman Kebutuhan Bahan</h5>';
        html += '<table class="table table-bordered">';
        html += '<thead><tr>' +
            '<th>Nama Bahan</th>' +
            '<th>Kategori</th>' +
            '<th>Jumlah Total</th>' +
            '<th>Satuan</th>' +
            '<th>Harga Satuan</th>' +
            '<th>Subtotal</th>' +
            '</tr></thead><tbody>';
        let totalBiaya = 0;
        if (rangkuman.length > 0) {
            rangkuman.forEach(b => {
                html += `<tr>
                    <td>${b.nama}</td>
                    <td>${b.kategori}</td>
                    <td>${b.jumlah.toFixed(2)}</td>
                    <td>${b.satuan}</td>
                    <td>Rp ${parseInt(b.harga).toLocaleString('id-ID')}</td>
                    <td>Rp ${parseInt(b.subtotal).toLocaleString('id-ID')}</td>
                </tr>`;
                totalBiaya += b.subtotal;
            });
            html += `<tr><td colspan="5" class="text-right"><b>Total Biaya Bahan</b></td><td><b>Rp ${parseInt(totalBiaya).toLocaleString('id-ID')}</b></td></tr>`;
        } else {
            html += '<tr><td colspan="6">Belum ada data kebutuhan bahan.</td></tr>';
        }
        html += '</tbody></table>';
        document.getElementById('tabel-rangkuman-bahan').innerHTML = html;
    });
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
        // Hitung kebutuhan bahan berdasarkan input jumlah porsi
        let kebutuhan = [];
        let totalBiaya = 0;
        // Komposisi per 1kg, 1 porsi = 45g, 1kg = 1000g => 22.2222 porsi per 1kg
        const porsiPerKg = 1000 / 45;
        // Deteksi BSJ kulit kebab dari nama BSJ
        const bsjNama = select.options[select.selectedIndex] ? select.options[select.selectedIndex].textContent.toLowerCase() : '';
        const isKulitKebab = bsjNama.includes('kulit kebab');
        komposisiData.forEach(k => {
            if (k.id_bsj == bsjId) {
                const bahan = bahanData.find(b => b.id == k.id_bahan);
                let satuanBahan = bahan && bahan.satuan ? bahan.satuan : 'kg';
                let totalJumlah = 0;
                if (isKulitKebab) {
                    // Untuk kulit kebab, kebutuhan = komposisi asli * jumlah (tidak dibagi 22,22)
                    if (satuanBahan === 'kg' || satuanBahan === 'liter') {
                        totalJumlah = (parseFloat(k.jumlah) / 1000) * jumlah;
                    } else {
                        totalJumlah = parseFloat(k.jumlah) * jumlah;
                    }
                } else if (bahan.kategori === 'baku' && (bahan.nama.toLowerCase().includes('daging sapi') || bahan.nama.toLowerCase().includes('daging ayam'))) {
                    satuanBahan = 'kg';
                    totalJumlah = jumlah * 0.045;
                } else {
                    // Bahan lain: (komposisi/1000)/22.2222 * jumlah porsi
                    let komposisiPerKg = parseFloat(k.jumlah) / 1000;
                    let kebutuhanPerPorsi = komposisiPerKg / porsiPerKg;
                    totalJumlah = kebutuhanPerPorsi * jumlah;
                }
                // Hitung subtotal
                let subtotal = 0;
                if (satuanBahan === 'liter' || satuanBahan === 'kg') {
                    subtotal = totalJumlah * parseFloat(bahan.harga_satuan);
                } else if (satuanBahan === 'ml') {
                    subtotal = (totalJumlah / 1000) * parseFloat(bahan.harga_satuan);
                } else if (satuanBahan === 'pcs' || satuanBahan === 'butir') {
                    subtotal = totalJumlah * parseFloat(bahan.harga_satuan);
                } else {
                    subtotal = totalJumlah * parseFloat(bahan.harga_satuan);
                }
                totalBiaya += subtotal;
                kebutuhan.push({
                    nama: bahan.nama,
                    kategori: bahan.kategori,
                    jumlah: totalJumlah,
                    satuan: satuanBahan,
                    harga: bahan.harga_satuan,
                    subtotal
                });
            }
        });
        let html = '<b>Kebutuhan Bahan:</b><ul>';
        kebutuhan.forEach(b => {
            html += `<li>${b.nama} (${b.kategori}): ${b.jumlah.toFixed(2)} ${b.satuan}, Rp ${b.subtotal.toLocaleString('id-ID')}</li>`;
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
        // Hitung kebutuhan bahan berdasarkan input jumlah porsi
        let kebutuhan = [];
        let totalBiaya = 0;
        const porsiPerKg = 1000 / 45;
        const bsjNama = select.options[select.selectedIndex] ? select.options[select.selectedIndex].textContent.toLowerCase() : '';
        const isKulitKebab = bsjNama.includes('kulit kebab');
        komposisiData.forEach(k => {
            if (k.id_bsj == bsjId) {
                const bahan = bahanData.find(b => b.id == k.id_bahan);
                let satuanBahan = bahan && bahan.satuan ? bahan.satuan : 'kg';
                let totalJumlah = 0;
                if (isKulitKebab) {
                    if (satuanBahan === 'kg' || satuanBahan === 'liter') {
                        totalJumlah = (parseFloat(k.jumlah) / 1000) * jumlah;
                    } else {
                        totalJumlah = parseFloat(k.jumlah) * jumlah;
                    }
                } else if (bahan.kategori === 'baku' && (bahan.nama.toLowerCase().includes('daging sapi') || bahan.nama.toLowerCase().includes('daging ayam'))) {
                    satuanBahan = 'kg';
                    totalJumlah = jumlah * 0.045;
                } else {
                    let komposisiPerKg = parseFloat(k.jumlah) / 1000;
                    let kebutuhanPerPorsi = komposisiPerKg / porsiPerKg;
                    totalJumlah = kebutuhanPerPorsi * jumlah;
                }
                let subtotal = 0;
                if (satuanBahan === 'liter' || satuanBahan === 'kg') {
                    subtotal = totalJumlah * parseFloat(bahan.harga_satuan);
                } else if (satuanBahan === 'ml') {
                    subtotal = (totalJumlah / 1000) * parseFloat(bahan.harga_satuan);
                } else if (satuanBahan === 'pcs' || satuanBahan === 'butir') {
                    subtotal = totalJumlah * parseFloat(bahan.harga_satuan);
                } else {
                    subtotal = totalJumlah * parseFloat(bahan.harga_satuan);
                }
                totalBiaya += subtotal;
                kebutuhan.push({
                    nama: bahan.nama,
                    kategori: bahan.kategori,
                    jumlah: totalJumlah,
                    satuan: satuanBahan,
                    harga: bahan.harga_satuan,
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

    // Validasi submit: jika daftarProduksi kosong, cegah submit
    document.getElementById('form-perintah-kerja').addEventListener('submit', function(e) {
        if (daftarProduksi.length === 0) {
            alert('Silakan tambahkan minimal satu item produksi ke daftar sebelum menyimpan!');
            e.preventDefault();
            return false;
        }
        // Gabungkan kebutuhan bahan yang sama dari semua daftarProduksi
        let bahanGabungan = {};
        daftarProduksi.forEach(item => {
            item.kebutuhan.forEach(bahan => {
                let key = bahan.nama + '|' + bahan.satuan;
                if (!bahanGabungan[key]) {
                    bahanGabungan[key] = {
                        nama: bahan.nama,
                        kategori: bahan.kategori,
                        jumlah: 0,
                        satuan: bahan.satuan,
                        harga: bahan.harga,
                        subtotal: 0
                    };
                }
                bahanGabungan[key].jumlah += parseFloat(bahan.jumlah);
                bahanGabungan[key].subtotal += parseFloat(bahan.subtotal);
            });
        });
        // Hapus input hidden lama
        document.querySelectorAll('.input-daftar-produksi').forEach(el => el.remove());
        // Simpan data utama produksi
        daftarProduksi.forEach((item, idx) => {
            let inputBsj = document.createElement('input');
            inputBsj.type = 'hidden';
            inputBsj.name = `daftarProduksi[${idx}][bsjId]`;
            inputBsj.value = item.bsjId;
            inputBsj.className = 'input-daftar-produksi';
            this.appendChild(inputBsj);
            let inputJumlah = document.createElement('input');
            inputJumlah.type = 'hidden';
            inputJumlah.name = `daftarProduksi[${idx}][jumlah]`;
            inputJumlah.value = item.jumlah;
            inputJumlah.className = 'input-daftar-produksi';
            this.appendChild(inputJumlah);
        });
        // Simpan kebutuhan bahan gabungan
        let idx = 0;
        for (let key in bahanGabungan) {
            let bahan = bahanGabungan[key];
            let inputNama = document.createElement('input');
            inputNama.type = 'hidden';
            inputNama.name = `kebutuhanBahanGabungan[${idx}][nama]`;
            inputNama.value = bahan.nama;
            inputNama.className = 'input-daftar-produksi';
            this.appendChild(inputNama);
            let inputKategori = document.createElement('input');
            inputKategori.type = 'hidden';
            inputKategori.name = `kebutuhanBahanGabungan[${idx}][kategori]`;
            inputKategori.value = bahan.kategori;
            inputKategori.className = 'input-daftar-produksi';
            this.appendChild(inputKategori);
            let inputJumlah = document.createElement('input');
            inputJumlah.type = 'hidden';
            inputJumlah.name = `kebutuhanBahanGabungan[${idx}][jumlah]`;
            inputJumlah.value = bahan.jumlah;
            inputJumlah.className = 'input-daftar-produksi';
            this.appendChild(inputJumlah);
            let inputSatuan = document.createElement('input');
            inputSatuan.type = 'hidden';
            inputSatuan.name = `kebutuhanBahanGabungan[${idx}][satuan]`;
            inputSatuan.value = bahan.satuan;
            inputSatuan.className = 'input-daftar-produksi';
            this.appendChild(inputSatuan);
            let inputHarga = document.createElement('input');
            inputHarga.type = 'hidden';
            inputHarga.name = `kebutuhanBahanGabungan[${idx}][harga]`;
            inputHarga.value = bahan.harga;
            inputHarga.className = 'input-daftar-produksi';
            this.appendChild(inputHarga);
            let inputSubtotal = document.createElement('input');
            inputSubtotal.type = 'hidden';
            inputSubtotal.name = `kebutuhanBahanGabungan[${idx}][subtotal]`;
            inputSubtotal.value = bahan.subtotal;
            inputSubtotal.className = 'input-daftar-produksi';
            this.appendChild(inputSubtotal);
            idx++;
        }
    });

    function renderDaftarProduksi() {
        const tbody = document.querySelector('#tabel-daftar-bsj tbody');
        tbody.innerHTML = '';
        let totalBiayaAll = 0;
        daftarProduksi.forEach((item, idx) => {
            let kebutuhanHtml = '<ul>';
            item.kebutuhan.forEach(b => {
                kebutuhanHtml += `<li>${b.nama} (${b.kategori}): ${b.jumlah.toFixed(2)} ${b.satuan}, Rp ${b.subtotal.toLocaleString('id-ID')}</li>`;
            });
            kebutuhanHtml += '</ul>';
            totalBiayaAll += item.totalBiaya;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.nama}</td>
                <td>${item.jumlah} ${item.satuan}</td>
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