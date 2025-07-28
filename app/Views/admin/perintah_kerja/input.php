<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?php echo esc($tittle); ?></h1>
    <form id="form-perintah-kerja" action="<?php echo base_url('admin/perintah-kerja/simpan'); ?>" method="post">
        <!-- Input Produksi -->
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
                        <!-- Dropdown BSJ / Bahan Baku akan disisipkan oleh JS -->
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

        <!-- Daftar Produksi -->
        <div class="card mb-4">
            <div class="card-header font-weight-bold">Daftar Produksi & Kebutuhan Bahan</div>
            <div class="card-body">
                <table class="table table-bordered" id="tabel-daftar-bsj">
                    <thead class="thead-dark">
                        <tr>
                            <th>BSJ/Bahan Baku</th>
                            <th>Jumlah</th>
                            <th>Kebutuhan Bahan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Diisi JS -->
                    </tbody>
                </table>
                <div class="mt-3 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-info mr-2" id="btn-rangkum-bahan">Rangkum Kebutuhan Bahan</button>
                    <button type="button" class="btn btn-secondary mr-2" id="btn-tampilkan-stok">Tampilkan Stok</button>
                    <button type="button" class="btn btn-warning" id="btn-bandingkan">Bandingkan</button>
                </div>
                <div class="mt-3" id="tabel-rangkuman-bahan"></div>
                <div class="mt-3" id="tabel-stok-bahan"></div>
                <div class="mt-3" id="tabel-kekurangan-bahan"></div>
            </div>
        </div>

        <div class="form-group">
            <input type="hidden" name="produksi" id="input-produksi">
            <input type="hidden" name="rangkuman" id="input-rangkuman">
            <button type="submit" class="btn btn-success">Simpan Perintah Kerja</button>
            <a href="<?php echo base_url('admin/dashboard'); ?>" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
<script>
    // Data dari PHP
    const bahanBakuList = <?= json_encode($bahan_all); ?>;
    const bsjList = <?= json_encode($bsj); ?>;
    const komposisiData = <?= json_encode($komposisi_bsj); ?>;
    // Map bahanData agar nama, satuan, dan stok selalu sinkron
    const bahanData = <?= json_encode($bahan_all); ?>.map(b => ({
        id: b.id,
        nama: b.nama,
        satuan: b.satuan,
        kategori: b.kategori,
        stok: parseFloat(b.stok || 0)
    }));

    let daftarProduksi = [];

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
                let satuanTampil = item.satuan && item.satuan.toLowerCase() === 'kg' ? 'porsi' : item.satuan;
                html += `<option value="${item.id}" data-satuan="${item.satuan}">${item.nama} (${satuanTampil})</option>`;
            });
            html += '</select>';
        } else if (tipe === 'bahan') {
            html += '<label>Pilih Bahan Baku</label>';
            html += '<select id="produk-id" class="form-control">';
            html += '<option value="">-- Pilih Bahan Baku --</option>';
            bahanBakuList.forEach(item => {
                html += `<option value="${item.id}" data-satuan="${item.satuan}">${item.nama} (${item.satuan})</option>`;
            });
            html += '</select>';
        }
        container.innerHTML = html;
        document.getElementById('label-jumlah').innerText = 'Jumlah';
        document.getElementById('preview-kebutuhan').innerHTML = '';
    });

    // Event produk berubah
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'produk-id') {
            const tipe = document.getElementById('tipe-produk').value;
            const select = e.target;
            const satuan = select.options[select.selectedIndex]?.getAttribute('data-satuan') || '';
            // Jika tipe bsj dan satuan kg, label jumlah jadi porsi
            if (tipe === 'bsj' && satuan.toLowerCase() === 'kg') {
                document.getElementById('label-jumlah').innerText = 'Jumlah (porsi)';
            } else {
                document.getElementById('label-jumlah').innerText = satuan ? `Jumlah (${satuan})` : 'Jumlah';
            }
            previewKebutuhan();
        }
    });
    document.getElementById('input-jumlah').addEventListener('input', previewKebutuhan);

    // Preview kebutuhan
    function previewKebutuhan() {
        const tipe = document.getElementById('tipe-produk').value;
        const select = document.getElementById('produk-id');
        const jumlah = parseInt(document.getElementById('input-jumlah').value) || 0;
        if (!select || !select.value || jumlah <= 0) return;

        let kebutuhan = [];

        if (tipe === 'bsj') {
            const bsjId = select.value;
            const satuan = select.options[select.selectedIndex]?.getAttribute('data-satuan') || '';
            const bsjNama = select.options[select.selectedIndex]?.textContent.toLowerCase() || '';
            const isKulitKebab = bsjNama.includes('kulit kebab');
            const porsiPerKg = 1000 / 45;

            komposisiData.forEach(k => {
                if (k.id_bsj == bsjId) {
                    const bahan = bahanData.find(b => b.id == k.id_bahan);
                    let satuanBahan = bahan?.satuan || 'kg';
                    let totalJumlah = 0;

                    if (isKulitKebab) {
                        totalJumlah = (parseFloat(k.jumlah) / (satuanBahan === 'kg' || satuanBahan === 'liter' ? 1000 : 1)) * jumlah;
                    } else if (bahan.kategori === 'baku' && (bahan.nama.toLowerCase().includes('daging sapi') || bahan.nama.toLowerCase().includes('daging ayam'))) {
                        totalJumlah = jumlah * 0.045;
                    } else {
                        let komposisiPerKg = parseFloat(k.jumlah) / 1000;
                        totalJumlah = (komposisiPerKg / porsiPerKg) * jumlah;
                    }

                    kebutuhan.push({
                        nama: bahan.nama,
                        kategori: bahan.kategori,
                        jumlah: totalJumlah,
                        satuan: satuanBahan
                    });
                }
            });
        } else if (tipe === 'bahan') {
            const bahanId = select.value;
            const bahan = bahanBakuList.find(b => b.id == bahanId);
            kebutuhan.push({
                nama: bahan.nama,
                kategori: bahan.kategori,
                jumlah: jumlah,
                satuan: bahan.satuan
            });
        }

        let html = '<b>Kebutuhan Bahan:</b> <ul>';
        kebutuhan.forEach(b => {
            html += `<li>${b.nama} (${b.kategori}): ${b.jumlah.toFixed(2)} ${b.satuan}</li>`;
        });
        html += '</ul>';
        document.getElementById('preview-kebutuhan').innerHTML = html;
    }

    // Tambah ke daftar produksi
    document.getElementById('btn-add-produk').addEventListener('click', function() {
        const tipe = document.getElementById('tipe-produk').value;
        const select = document.getElementById('produk-id');
        let jumlah = parseInt(document.getElementById('input-jumlah').value) || 0;
        if (!select || !select.value || jumlah <= 0) return;

        let nama = select.options[select.selectedIndex].textContent;
        let satuan = select.options[select.selectedIndex].getAttribute('data-satuan');

        // Jika tipe bsj dan satuan kg, ubah field jumlah dan satuan jadi porsi
        let tampilJumlah = jumlah;
        let tampilSatuan = satuan;
        if (tipe === 'bsj' && satuan && satuan.toLowerCase() === 'kg') {
            tampilSatuan = 'porsi';
        }

        // Hitung kebutuhan bahan langsung di sini (copy logic dari previewKebutuhan)
        let kebutuhan = [];
        if (tipe === 'bsj') {
            const bsjId = select.value;
            const satuan = select.options[select.selectedIndex]?.getAttribute('data-satuan') || '';
            const bsjNama = select.options[select.selectedIndex]?.textContent.toLowerCase() || '';
            const isKulitKebab = bsjNama.includes('kulit kebab');
            const porsiPerKg = 1000 / 45;

            komposisiData.forEach(k => {
                if (k.id_bsj == bsjId) {
                    const bahan = bahanData.find(b => b.id == k.id_bahan);
                    let satuanBahan = bahan?.satuan || 'kg';
                    let totalJumlah = 0;

                    if (isKulitKebab) {
                        totalJumlah = (parseFloat(k.jumlah) / (satuanBahan === 'kg' || satuanBahan === 'liter' ? 1000 : 1)) * jumlah;
                    } else if (bahan.kategori === 'baku' && (bahan.nama.toLowerCase().includes('daging sapi') || bahan.nama.toLowerCase().includes('daging ayam'))) {
                        totalJumlah = jumlah * 0.045;
                    } else {
                        let komposisiPerKg = parseFloat(k.jumlah) / 1000;
                        totalJumlah = (komposisiPerKg / porsiPerKg) * jumlah;
                    }

                    kebutuhan.push({
                        nama: bahan.nama,
                        kategori: bahan.kategori,
                        jumlah: totalJumlah,
                        satuan: satuanBahan
                    });
                }
            });
        } else if (tipe === 'bahan') {
            const bahanId = select.value;
            const bahan = bahanBakuList.find(b => b.id == bahanId);
            kebutuhan.push({
                nama: bahan.nama,
                kategori: bahan.kategori,
                jumlah: jumlah,
                satuan: bahan.satuan
            });
        }

        daftarProduksi.push({
            tipe,
            nama,
            jumlah: tampilJumlah,
            satuan: tampilSatuan,
            kebutuhan
        });
        renderDaftarProduksi();
        document.getElementById('input-produksi').value = JSON.stringify(daftarProduksi);

        // Reset
        document.getElementById('tipe-produk').value = '';
        document.getElementById('container-pilihan').innerHTML = '';
        document.getElementById('input-jumlah').value = '';
        document.getElementById('label-jumlah').innerText = 'Jumlah';
        document.getElementById('preview-kebutuhan').innerHTML = '';
    });

    function getKebutuhanFromPreview() {
        return daftarProduksi.length > 0 ? daftarProduksi[daftarProduksi.length - 1].kebutuhan : [];
    }

    function renderDaftarProduksi() {
        const tbody = document.querySelector('#tabel-daftar-bsj tbody');
        tbody.innerHTML = '';
        daftarProduksi.forEach((item, idx) => {
            let kebutuhanHtml = '<ul>';
            item.kebutuhan.forEach(b => {
                kebutuhanHtml += `<li>${b.nama} (${b.kategori}): ${b.jumlah.toFixed(2)} ${b.satuan}</li>`;
            });
            kebutuhanHtml += '</ul>';
            const tr = document.createElement('tr');
            tr.innerHTML = `
        <td>${item.nama}</td>
        <td>${item.jumlah} ${item.satuan}</td>
        <td>${kebutuhanHtml}</td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusProduksi(${idx})">Hapus</button></td>
        `;
            tbody.appendChild(tr);
        });
        // Update hidden input setiap render
        document.getElementById('input-produksi').value = JSON.stringify(daftarProduksi);
    }

    // Hapus produksi dari daftar
    function hapusProduksi(idx) {
        daftarProduksi.splice(idx, 1);
        renderDaftarProduksi();
        document.getElementById('input-produksi').value = JSON.stringify(daftarProduksi);
    }

    // Rangkuman bahan (gabungan)
    function getRangkumanBahan() {
        let bahanGabungan = {};
        daftarProduksi.forEach(item => {
            item.kebutuhan.forEach(bahan => {
                const key = bahan.nama + '|' + bahan.satuan;
                if (!bahanGabungan[key]) {
                    bahanGabungan[key] = {
                        nama: bahan.nama,
                        kategori: bahan.kategori,
                        jumlah: 0,
                        satuan: bahan.satuan
                    };
                }
                bahanGabungan[key].jumlah += parseFloat(bahan.jumlah);
            });
        });
        return Object.values(bahanGabungan);
    }

    // Event: Rangkum Bahan
    document.getElementById('btn-rangkum-bahan').addEventListener('click', function() {
        const rangkuman = getRangkumanBahan();
        let html = '<h5>Rangkuman Kebutuhan Bahan</h5>';
        html += '<table class="table table-bordered">';
        html += '<thead><tr>' +
            '<th>Nama Bahan</th>' +
            '<th>Kategori</th>' +
            '<th>Jumlah Total</th>' +
            '<th>Satuan</th>' +
            '</tr></thead><tbody>';

        rangkuman.forEach(b => {
            html += `<tr>
                <td>${b.nama}</td>
                <td>${b.kategori}</td>
                <td>${b.jumlah.toFixed(2)}</td>
                <td>${b.satuan}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        document.getElementById('tabel-rangkuman-bahan').innerHTML = html;
        document.getElementById('input-rangkuman').value = JSON.stringify(rangkuman);
    });

    // Event: Tampilkan Stok
    document.getElementById('btn-tampilkan-stok').addEventListener('click', function() {
        let html = '<h5>Stok Bahan Saat Ini</h5>';
        html += '<table class="table table-bordered">';
        html += '<thead><tr>' +
            '<th>Nama Bahan</th>' +
            '<th>Kategori</th>' +
            '<th>Stok</th>' +
            '<th>Satuan</th>' +
            '</tr></thead><tbody>';
        bahanData.forEach(b => {
            let stokTampil = b.stok ?? 0;
            if (b.satuan && (b.satuan.toLowerCase() === 'kg' || b.satuan.toLowerCase() === 'liter')) {
                stokTampil = stokTampil / 1000;
            }
            html += `<tr>
                <td>${b.nama}</td>
                <td>${b.kategori}</td>
                <td>${stokTampil.toFixed(2)}</td>
                <td>${b.satuan}</td>
            </tr>`;
        });
        html += '</tbody></table>';
        document.getElementById('tabel-stok-bahan').innerHTML = html;
    });

    // Event: Bandingkan (tampilkan tabel bahan yang perlu dibeli)
    document.getElementById('btn-bandingkan').addEventListener('click', function() {
        const rangkuman = getRangkumanBahan();
        let kekuranganHTML = '<h5>Kebutuhan Bahan yang Perlu Dibeli</h5>';
        kekuranganHTML += '<table class="table table-bordered">';
        kekuranganHTML += '<thead><tr>' +
            '<th>Nama</th>' +
            '<th>Kategori</th>' +
            '<th>Dibutuhkan</th>' +
            '<th>Stok</th>' +
            '<th>Kurang</th>' +
            '<th>Pembulatan</th>' +
            '</tr></thead><tbody>';

        let adaYangKurang = false;
        // Simpan juga pembulatan ke rangkuman
        rangkuman.forEach(b => {
            // Ambil stok bahan dari tabel bahan (bahanData)
            const stokData = bahanData.find(x => x.nama === b.nama && x.satuan === b.satuan);
            let stok = 0;
            if (stokData && stokData.stok !== undefined && stokData.stok !== null && stokData.stok !== '') {
                stok = parseFloat(stokData.stok);
                if (isNaN(stok)) stok = 0;
                if (b.satuan && (b.satuan.toLowerCase() === 'kg' || b.satuan.toLowerCase() === 'liter')) {
                    stok = stok / 1000;
                }
            }
            const kurang = b.jumlah - stok;
            const pembulatan = kurang > 0 ? Math.ceil(kurang) : 0;
            b.pembulatan = pembulatan; // tambahkan ke rangkuman
            if (kurang > 0.0001) {
                adaYangKurang = true;
                kekuranganHTML += `<tr>
                    <td>${b.nama}</td>
                    <td>${b.kategori}</td>
                    <td>${b.jumlah.toFixed(2)} ${b.satuan}</td>
                    <td>${stok.toFixed(2)} ${b.satuan}</td>
                    <td><b>${kurang.toFixed(2)} ${b.satuan}</b></td>
                    <td><b>${pembulatan} ${b.satuan}</b></td>
                </tr>`;
            }
        });

        if (!adaYangKurang) {
            kekuranganHTML += '<tr><td colspan="6" class="text-center">Semua bahan tersedia.</td></tr>';
        }
        kekuranganHTML += '</tbody></table>';
        document.getElementById('tabel-kekurangan-bahan').innerHTML = kekuranganHTML;
        // Update input hidden rangkuman agar pembulatan ikut tersimpan
        document.getElementById('input-rangkuman').value = JSON.stringify(rangkuman);
    });

    // Form submit: pastikan hidden input sudah terisi (fallback)
    document.getElementById('form-perintah-kerja').addEventListener('submit', function(e) {
        if (!document.getElementById('input-produksi').value) {
            document.getElementById('input-produksi').value = JSON.stringify(daftarProduksi);
        }
        if (!document.getElementById('input-rangkuman').value) {
            document.getElementById('input-rangkuman').value = JSON.stringify(getRangkumanBahan());
        }
    });
</script>
<?= $this->endSection(); ?>