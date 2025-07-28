<!-- app/Views/admin/perintah_pengiriman/input.php -->
<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>
    <form action="<?= base_url('admin/perintah-pengiriman/simpan') ?>" method="post" id="formPengiriman">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" class="form-control" name="tanggal" id="tanggal" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
            <label for="perintah_kerja_id">Perintah Kerja (Batch)</label>
            <select class="form-control" name="perintah_kerja_id" id="perintah_kerja_id" required>
                <option value="">Pilih Batch</option>
                <?php
                // Ambil unique admin_id dari $perintahKerjaData
                $uniqueAdminIds = [];
                foreach ($perintahKerjaData as $p) {
                    if (!empty($p['admin_id']) && !in_array($p['admin_id'], $uniqueAdminIds)) {
                        $uniqueAdminIds[] = $p['admin_id'];
                    }
                }
                foreach ($uniqueAdminIds as $adminId): ?>
                    <option value="<?= $adminId ?>" data-admin-id="<?= $adminId ?>">Batch <?= $adminId ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <input type="text" class="form-control" name="keterangan" id="keterangan">
        </div>
        <hr>
        <div id="bsj-table-block" style="display:none;">
            <h5>BSJ dan Bahan perintah kerja</h5>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nama BSJ</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                    </tr>
                </thead>
                <tbody id="bsj-table-body">
                </tbody>
            </table>
        </div>
        <h5>Outlet Tujuan & Item</h5>
        <div id="outlet-list">
            <!-- Dynamic outlet + item form will be inserted here by JS -->
        </div>
        <button type="button" class="btn btn-primary mb-3" id="bagiRataBtn">Bagi Rata</button>
        <button type="button" class="btn btn-success mb-3" id="addOutletBtn">+ Tambah Outlet Tujuan</button>
        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?= base_url('admin/perintah-pengiriman/batal') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<script>
    let outletCounter = 0;

    const outletList = document.getElementById('outlet-list');
    const addOutletBtn = document.getElementById('addOutletBtn');



    // Data outlet, bsj, bahan, dan perintah kerja dari backend
    const outletsData = <?= json_encode($outlets) ?>;
    const bsjData = <?= json_encode($bsj) ?>;
    const bahanData = <?= json_encode($bahan) ?>;

    // DataBarang: mapping jenis ke data barang
    const dataBarang = {
        'bsj': bsjData,
        'bahan': bahanData
    };

    // Perintah kerja data sudah diambil di PHP dan digunakan untuk dropdown utama

    // Fungsi AJAX untuk ambil detail perintah kerja (BSJ yang diproduksi dan jumlahnya)
    // Ambil semua perintah kerja dengan admin_id tertentu
    function fetchBSJByAdminId(adminId, cb) {
        fetch('<?= base_url('admin/bsj-by-admin-id-json') ?>?admin_id=' + adminId)
            .then(res => res.json())
            .then(data => cb(data.data || []));
    }

    // Fungsi untuk membuat elemen outlet + item
    function createOutletBlock(index) {
        const outletBlock = document.createElement('div');
        outletBlock.classList.add('card', 'mb-3');
        let outletOptions = outletsData.map(o => `<option value="${o.id}">${o.nama_outlet}</option>`).join('');
        outletBlock.innerHTML = `
        <div class="card-body" data-index="${index}">
            <div class="form-group">
                <label for="outlet_${index}">Outlet Tujuan</label>
                <select name="outlet[${index}][id_outlet]" class="form-control outlet-select" data-index="${index}" required>
                    <option value="">-- Pilih Outlet --</option>
                    ${outletOptions}
                </select>
            </div>
            <div class="form-group">
                <label for="outlet_ket_${index}">Keterangan (opsional)</label>
                <input type="text" name="outlet[${index}][keterangan]" class="form-control">
            </div>
            <hr>
            <h6>Item yang Dikirim</h6>
            <div class="item-list" data-index="${index}"></div>
            <button type="button" class="btn btn-sm btn-outline-success mt-2 add-item-btn" data-index="${index}">+ Tambah Item</button>
        </div>
    `;
        outletList.appendChild(outletBlock);
    }

    // Fungsi untuk membuat elemen item dalam outlet tertentu
    function createItemRow(outletIndex, itemIndex) {
        const itemList = document.querySelector(`.item-list[data-index="${outletIndex}"]`);
        const row = document.createElement('div');
        row.classList.add('row', 'mb-2', 'align-items-end');
        row.innerHTML = `
        <div class="col-md-2">
            <label>Jenis</label>
            <select name="outlet[${outletIndex}][items][${itemIndex}][jenis]" class="form-control jenis-select" required>
                <option value="">Pilih</option>
                <option value="bsj">BSJ</option>
                <option value="bahan">Bahan</option>
            </select>
        </div>
        <div class="col-md-3">
            <label>Nama Barang</label>
            <select name="outlet[${outletIndex}][items][${itemIndex}][id_barang]" class="form-control barang-select" required disabled>
                <option value="">Pilih Jenis Dulu</option>
            </select>
            <input type="hidden" name="outlet[${outletIndex}][items][${itemIndex}][nama_barang]" class="input-nama-barang">
        </div>
        <div class="col-md-2">
            <label>Jumlah</label>
            <input type="number" name="outlet[${outletIndex}][items][${itemIndex}][jumlah]" class="form-control" required min="1">
        </div>
        <div class="col-md-2">
            <label>Satuan</label>
            <input type="hidden" name="outlet[${outletIndex}][items][${itemIndex}][satuan]" class="input-satuan">
            <input type="text" class="form-control satuan" disabled>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm remove-item-btn">Hapus</button>
        </div>
    `;
        itemList.appendChild(row);
    }

    // Event: Tambah Outlet
    addOutletBtn.addEventListener('click', () => {
        createOutletBlock(outletCounter++);
    });

    // Event Delegation untuk Tambah Item, Hapus Item, dan Outlet/Perintah Kerja
    document.addEventListener('click', function(e) {
        // Tambah item ke outlet
        if (e.target.classList.contains('add-item-btn')) {
            const index = e.target.dataset.index;
            const itemList = document.querySelector(`.item-list[data-index="${index}"]`);
            const itemIndex = itemList.children.length;
            createItemRow(index, itemIndex);
        }

        // Hapus item
        if (e.target.classList.contains('remove-item-btn')) {
            e.target.closest('.row').remove();
        }
    });

    // Tidak perlu event khusus untuk outlet-select, dropdown perintah kerja selalu muncul

    // Event: Pilih Perintah Kerja (dropdown utama) => Tampilkan BSJ & bahan yang diproduksi, dan auto tambah blok bahan ke setiap outlet
    let lastPerintahData = [];
    document.getElementById('perintah_kerja_id').addEventListener('change', function(e) {
        const adminId = e.target.value;
        const bsjTableBlock = document.getElementById('bsj-table-block');
        const bsjTableBody = document.getElementById('bsj-table-body');
        bsjTableBody.innerHTML = '';

        if (!adminId) {
            bsjTableBlock.style.display = 'none';
            return;
        }

        // Ambil data BSJ & bahan dari perintah kerja (endpoint harus mengembalikan keduanya)
        fetch(`<?= base_url('admin/perintah-pengiriman/getBSJByAdminId') ?>/${adminId}`)
            .then(response => response.json())
            .then(data => {
                lastPerintahData = data;
                // Tampilkan semua jenis (bsj dan bahan) di tabel
                if (data.length > 0) {
                    bsjTableBlock.style.display = 'block';
                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                        <td>${item.nama}</td>
                        <td>${item.jumlah}</td>
                        <td>${item.satuan}</td>
                    `;
                        bsjTableBody.appendChild(row);
                    });
                } else {
                    bsjTableBlock.style.display = 'none';
                }

                // Tambahkan outlet block untuk semua outlet jika belum ada
                const existingIndexes = Array.from(document.querySelectorAll('.card-body')).map(cb => cb.dataset.index);
                outletsData.forEach((outlet, idx) => {
                    let alreadyExists = false;
                    document.querySelectorAll('.card-body').forEach(cb => {
                        const select = cb.querySelector('.outlet-select');
                        if (select && select.value == outlet.id) alreadyExists = true;
                    });
                    if (!alreadyExists) {
                        createOutletBlock(outletCounter);
                        const lastBlock = outletList.querySelector('.card-body[data-index="' + outletCounter + '"]');
                        if (lastBlock) {
                            const select = lastBlock.querySelector('.outlet-select');
                            if (select) select.value = outlet.id;
                        }
                        outletCounter++;
                    }
                });

                // Untuk setiap outlet block, update item-list: otomatis isi BSJ & bahan dari data perintah kerja
                document.querySelectorAll('.card-body').forEach(function(parent) {
                    const itemList = parent.querySelector('.item-list');
                    itemList.innerHTML = '';
                    let idx = 0;
                    data.forEach(item => {
                        const row = document.createElement('div');
                        row.classList.add('row', 'mb-2', 'align-items-end');
                        row.innerHTML = `
                            <div class="col-md-3">
                                <label>Nama ${item.jenis === 'bsj' ? 'BSJ' : 'Bahan'}</label>
                                <input type="hidden" name="outlet[${parent.dataset.index}][items][${idx}][jenis]" value="${item.jenis.toUpperCase()}">
                                <input type="hidden" name="outlet[${parent.dataset.index}][items][${idx}][id_barang]" value="${item.id}">
                                <input type="hidden" name="outlet[${parent.dataset.index}][items][${idx}][nama_barang]" value="${item.nama}">
                                <input type="text" class="form-control" value="${item.nama}" readonly>
                            </div>
                            <div class="col-md-2">
                                <label>Jumlah</label>
                                <input type="number" name="outlet[${parent.dataset.index}][items][${idx}][jumlah]" class="form-control" required min="1">
                            </div>
                            <div class="col-md-2">
                                <label>Satuan</label>
                                <input type="hidden" name="outlet[${parent.dataset.index}][items][${idx}][satuan]" value="${item.satuan}">
                                <input type="text" class="form-control" value="${item.satuan}" readonly>
                            </div>
                        `;
                        itemList.appendChild(row);
                        idx++;
                    });
                });
            })
            .catch(error => {
                console.error('Error fetching BSJ/bahan:', error);
                bsjTableBlock.style.display = 'none';
            });
        // Tombol Bagi Rata: membagi jumlah dari tabel BSJ & bahan ke semua outlet
        document.getElementById('bagiRataBtn').addEventListener('click', function() {
            // Ambil jumlah outlet aktif
            const outletBlocks = document.querySelectorAll('.card-body');
            if (outletBlocks.length === 0 || !lastPerintahData || lastPerintahData.length === 0) {
                alert('Pilih perintah kerja dan pastikan outlet sudah muncul!');
                return;
            }
            // Untuk setiap item (BSJ/bahan), bagi rata ke semua outlet
            lastPerintahData.forEach((item, itemIdx) => {
                const total = parseFloat(item.jumlah) || 0;
                const perOutlet = Math.floor(total / outletBlocks.length);
                const sisa = total % outletBlocks.length;
                // Untuk setiap outlet, cari input jumlah yang sesuai urutan item
                outletBlocks.forEach((block, idx) => {
                    const jumlahInputs = block.querySelectorAll('input[name^="outlet"][name*="[items]["][name$="[jumlah]"]');
                    if (jumlahInputs[itemIdx]) {
                        // Bagi rata, sisa didistribusi ke outlet pertama dst
                        let val = perOutlet;
                        if (idx < sisa) val += 1;
                        jumlahInputs[itemIdx].value = val;
                    }
                });
            });
        });
    });

    // Event: Pilih Jenis => Update Nama Barang
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('jenis-select')) {
            const jenis = e.target.value;
            const parentRow = e.target.closest('.row');
            const barangSelect = parentRow.querySelector('.barang-select');
            const satuanInput = parentRow.querySelector('.satuan');
            const namaBarangInput = parentRow.querySelector('.input-nama-barang');
            const satuanHiddenInput = parentRow.querySelector('.input-satuan');

            barangSelect.innerHTML = '<option value="">Pilih Barang</option>';
            barangSelect.disabled = !jenis;

            if (dataBarang[jenis]) {
                dataBarang[jenis].forEach(barang => {
                    const option = document.createElement('option');
                    option.value = barang.id;
                    option.textContent = barang.nama;
                    option.dataset.satuan = barang.satuan;
                    barangSelect.appendChild(option);
                });
            }

            satuanInput.value = '';
            namaBarangInput.value = '';
            satuanHiddenInput.value = '';
        }

        // Pilih Barang => Update Satuan dan nama_barang hidden
        if (e.target.classList.contains('barang-select')) {
            const selected = e.target.selectedOptions[0];
            const satuan = selected.dataset.satuan || '';
            const nama = selected.textContent || '';
            const parentRow = e.target.closest('.row');
            parentRow.querySelector('.satuan').value = satuan;
            const namaBarangInput = parentRow.querySelector('.input-nama-barang');
            const satuanHiddenInput = parentRow.querySelector('.input-satuan');
            if (namaBarangInput) namaBarangInput.value = nama;
            if (satuanHiddenInput) satuanHiddenInput.value = satuan;
        }
    });
</script>



<?= $this->endSection(); ?>