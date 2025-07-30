<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container-fluid mt-4">
    <h4 class="mb-4 text-gray-800 font-weight-bold">Form Pengiriman Barang ke Outlet</h4>

    <form action="<?= base_url('produksi/pengiriman/form-pengiriman/store') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="perintah_pengiriman_id">Perintah Pengiriman</label>
                <select name="perintah_pengiriman_id" id="perintah_pengiriman_id" class="form-control" required>
                    <option value="">-- Pilih Perintah Pengiriman --</option>
                    <?php if (isset($perintah_pengiriman) && is_array($perintah_pengiriman)): ?>
                        <?php foreach ($perintah_pengiriman as $pp): ?>
                            <option value="<?= $pp['id'] ?>">[<?= $pp['id'] ?>] <?= date('d-m-Y', strtotime($pp['tanggal'])) ?> <?= esc($pp['keterangan']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="tanggal">Tanggal Pengiriman</label>
                <input type="date" name="tanggal" class="form-control" required>
            </div>
            <div class="form-group col-md-4">
                <label for="catatan">Catatan</label>
                <input type="text" name="catatan" class="form-control">
            </div>
        </div>

        <div id="outlet-list">
            <!-- Blok outlet & item akan diisi otomatis oleh JS -->
        </div>
        <button type="button" class="btn btn-success mb-3" id="addOutletBtn">+ Tambah Outlet Tujuan</button>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim</button>
            <a href="<?= base_url('produksi/pengiriman') ?>" class="btn btn-secondary ml-2"><i class="fas fa-times"></i> Batal</a>
        </div>
    </form>
</div>

<script>
    // Data dari backend
    const outletsData = <?= json_encode($outlets) ?>;
    const bsjData = <?= json_encode($barang_bsj) ?>;
    const bahanData = <?= json_encode($bahan) ?>;
    let outletCounter = 0;

    // Tambah outlet manual
    document.getElementById('addOutletBtn').addEventListener('click', function() {
        createOutletBlock(outletCounter++);
    });

    // Saat pilih perintah pengiriman, ambil data outlet & item
    document.getElementById('perintah_pengiriman_id').addEventListener('change', function(e) {
        const id = e.target.value;
        if (!id) return;
        fetch('<?= base_url('produksi/pengiriman/get-perintah-pengiriman-detail') ?>/' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    renderOutletsFromPerintah(data.data);
                }
            });
    });

    function renderOutletsFromPerintah(data) {
        // data: [{outlet_id, nama_outlet, keterangan, items: [{tipe, barang_id, nama_barang, jumlah, satuan}]}]
        const outletList = document.getElementById('outlet-list');
        outletList.innerHTML = '';
        outletCounter = 0;
        data.forEach(outlet => {
            createOutletBlock(outletCounter, outlet);
            outletCounter++;
        });
    }

    function createOutletBlock(index, outletData = null) {
        const outletList = document.getElementById('outlet-list');
        const outletBlock = document.createElement('div');
        outletBlock.classList.add('card', 'mb-3');
        let outletOptions = outletsData.map(o => `<option value="${o.id}" ${outletData && outletData.outlet_id == o.id ? 'selected' : ''}>${o.nama_outlet}</option>`).join('');
        outletBlock.innerHTML = `
        <div class="card-body" data-index="${index}">
            <div class="form-group">
                <label>Outlet Tujuan</label>
                <select name="outlet[${index}][id_outlet]" class="form-control outlet-select" required>
                    <option value="">-- Pilih Outlet --</option>
                    ${outletOptions}
                </select>
            </div>
            <div class="form-group">
                <label>Keterangan (opsional)</label>
                <input type="text" name="outlet[${index}][keterangan]" class="form-control" value="${outletData && outletData.keterangan ? outletData.keterangan : ''}">
            </div>
            <hr>
            <h6>Item yang Dikirim</h6>
            <div class="item-list" data-index="${index}"></div>
        </div>
        `;
        outletList.appendChild(outletBlock);
        // Jika ada data item, render
        if (outletData && Array.isArray(outletData.items)) {
            outletData.items.forEach((item, idx) => {
                createItemRow(index, idx, item);
            });
        }
    }

    // Event delegation untuk tambah item dan hapus item
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-item-btn')) {
            const index = e.target.dataset.index;
            const itemList = document.querySelector(`.item-list[data-index="${index}"]`);
            const itemIndex = itemList.children.length;
            createItemRow(index, itemIndex);
        }
        if (e.target.classList.contains('remove-item-btn')) {
            e.target.closest('.row').remove();
        }
    });

    function createItemRow(outletIndex, itemIndex, itemData = null) {
        const itemList = document.querySelector(`.item-list[data-index="${outletIndex}"]`);
        const row = document.createElement('div');
        row.classList.add('row', 'mb-2', 'align-items-end');

        // Cari id_barang yang benar dari master data
        let idBarang = '';
        let satuan = '';
        if (itemData) {
            if (itemData.tipe === 'bsj') {
                const found = bsjData.find(b => b.nama === itemData.nama_barang);
                if (found) {
                    idBarang = found.id;
                    satuan = found.satuan;
                } else if (itemData.barang_id) {
                    idBarang = itemData.barang_id;
                }
            } else if (itemData.tipe === 'bahan') {
                const found = bahanData.find(b => b.nama === itemData.nama_barang);
                if (found) {
                    idBarang = found.id;
                    satuan = found.satuan;
                } else if (itemData.barang_id) {
                    idBarang = itemData.barang_id;
                }
            }
        }

        row.innerHTML = `
        <div class="col-md-2">
            <label>Jenis</label>
            <input type="hidden" name="outlet[${outletIndex}][items][${itemIndex}][jenis]" value="${itemData ? itemData.tipe : ''}">
            <input type="text" class="form-control" value="${itemData ? (itemData.tipe === 'bsj' ? 'BSJ' : (itemData.tipe === 'bahan' ? 'Bahan' : '')) : ''}" readonly>
        </div>
        <div class="col-md-3">
            <label>Nama Barang</label>
            <input type="hidden" name="outlet[${outletIndex}][items][${itemIndex}][id_barang]" value="${idBarang}">
            <input type="hidden" name="outlet[${outletIndex}][items][${itemIndex}][nama_barang]" value="${itemData ? itemData.nama_barang : ''}">
            <input type="text" class="form-control" value="${itemData ? itemData.nama_barang : ''}" readonly>
        </div>
        <div class="col-md-2">
            <label>Jumlah</label>
            <input type="number" name="outlet[${outletIndex}][items][${itemIndex}][jumlah]" class="form-control" required min="1" value="${itemData ? itemData.jumlah : ''}" readonly>
        </div>
        <div class="col-md-2">
            <label>Satuan</label>
            <input type="hidden" name="outlet[${outletIndex}][items][${itemIndex}][satuan]" value="${satuan || (itemData ? itemData.satuan : '')}">
            <input type="text" class="form-control" value="${satuan || (itemData ? itemData.satuan : '')}" readonly>
        </div>
        <?php if (in_groups('admin')) : ?>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm remove-item-btn">Hapus</button>
        </div>
        <?php endif; ?>
        `;
        itemList.appendChild(row);
    }
</script>

<?= $this->endSection() ?>