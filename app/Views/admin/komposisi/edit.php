<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Komposisi BSJ</h1>

    <form action="<?= base_url('admin/komposisi/update'); ?>" method="post">
        <input type="hidden" name="id_bsj" value="<?= esc($id_bsj); ?>">
        <div class="card mb-4">
            <div class="card-body">
                <div class="form-group">
                    <label for="id_bsj">Pilih BSJ</label>
                    <select name="id_bsj_display" id="id_bsj" class="form-control" disabled>
                        <?php foreach ($bsj as $item) : ?>
                            <option value="<?= $item['id']; ?>" <?= $item['id'] == $id_bsj ? 'selected' : '' ?>><?= $item['nama']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row align-items-end">
                    <div class="form-group col-md-6">
                        <label for="bahan_id">Pilih Bahan</label>
                        <select id="bahan_id" class="form-control">
                            <option value="">-- Pilih Bahan --</option>
                            <?php foreach ($bahan as $b) : ?>
                                <option value="<?= $b['id']; ?>" data-nama="<?= $b['nama']; ?>" data-kategori="<?= $b['kategori']; ?>" data-satuan="<?= $b['satuan']; ?>">
                                    <?= $b['nama']; ?> (<?= $b['kategori']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="jumlah">Jumlah <span id="label-satuan">(satuan)</span></label>
                        <input type="number" id="jumlah" class="form-control" placeholder="Jumlah" min="1">
                    </div>
                    <div class="form-group col-md-3">
                        <button type="button" class="btn btn-success" onclick="tambahBahan()">Tambah Bahan</button>
                    </div>
                </div>

                <table class="table table-bordered mt-3">
                    <thead class="thead-dark">
                        <tr>
                            <th>Nama Bahan</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-bahan">
                        <!-- Diisi oleh JS -->
                    </tbody>
                </table>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="<?= base_url('admin/komposisi'); ?>" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </form>
</div>

<script>
    let daftarBahan = [];
    // Isi awal dari PHP
    <?php if (!empty($komposisi)) : ?>
        daftarBahan = <?= json_encode(array_map(function ($k) {
                            return [
                                'id' => $k['id_bahan'],
                                'nama' => $k['nama_bahan'],
                                'kategori' => $k['kategori'],
                                'jumlah' => $k['jumlah'],
                                'satuan' => $k['satuan'] ?? ''
                            ];
                        }, $komposisi)); ?>;
    <?php endif; ?>

    // Update label satuan saat bahan dipilih
    document.getElementById('bahan_id').addEventListener('change', function() {
        const satuanAsli = this.options[this.selectedIndex].dataset.satuan || '';
        let satuanTampil = '(satuan)';
        if (satuanAsli.toLowerCase() === 'kg') {
            satuanTampil = '(gram)';
        } else if (satuanAsli.toLowerCase() === 'liter') {
            satuanTampil = '(ml)';
        } else if (satuanAsli) {
            satuanTampil = `(${satuanAsli})`;
        }
        document.getElementById('label-satuan').textContent = satuanTampil;
    });

    function tambahBahan() {
        const bahanSelect = document.getElementById('bahan_id');
        const bahanId = bahanSelect.value;
        const bahanNama = bahanSelect.options[bahanSelect.selectedIndex].dataset.nama;
        const bahanKategori = bahanSelect.options[bahanSelect.selectedIndex].dataset.kategori;
        const bahanSatuanAsli = bahanSelect.options[bahanSelect.selectedIndex].dataset.satuan;
        let jumlah = document.getElementById('jumlah').value;
        let satuanTampil = '';

        if (!bahanId || !jumlah || jumlah <= 0) {
            alert('Isi bahan dan jumlah dengan benar');
            return;
        }

        // Cegah duplikat
        if (daftarBahan.find(item => item.id == bahanId)) {
            alert('Bahan ini sudah ditambahkan');
            return;
        }

        // Konversi satuan
        let jumlahTampil = jumlah;
        if (bahanSatuanAsli.toLowerCase() === 'kg') {
            satuanTampil = 'gram';
        } else if (bahanSatuanAsli.toLowerCase() === 'liter') {
            satuanTampil = 'ml';
        } else if (bahanSatuanAsli.toLowerCase() === 'pcs') {
            satuanTampil = 'pcs';
        } else {
            satuanTampil = bahanSatuanAsli;
        }

        daftarBahan.push({
            id: bahanId,
            nama: bahanNama,
            kategori: bahanKategori,
            jumlah: jumlahTampil,
            satuan: satuanTampil
        });
        renderTabel();

        // reset form
        bahanSelect.value = "";
        document.getElementById('jumlah').value = "";
        document.getElementById('label-satuan').textContent = '(satuan)';
    }

    function renderTabel() {
        const tbody = document.getElementById('tabel-bahan');
        tbody.innerHTML = '';

        daftarBahan.forEach((item, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
            <td>
                ${item.nama}
                <input type="hidden" name="bahan_id[]" value="${item.id}">
            </td>
            <td>${item.kategori}</td>
            <td>
                ${item.jumlah} ${item.satuan}
                <input type="hidden" name="jumlah[]" value="${item.jumlah}">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="hapusBahan(${index})">Hapus</button>
            </td>
        `;
            tbody.appendChild(row);
        });
    }

    function hapusBahan(index) {
        daftarBahan.splice(index, 1);
        renderTabel();
    }

    // Render tabel awal jika ada data
    renderTabel();
</script>

<?= $this->endSection(); ?>