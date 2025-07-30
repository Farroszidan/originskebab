<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h4 class="mb-4">Kekurangan Bahan per Outlet</h4>
    <form id="form-kekurangan-per-outlet" action="<?php echo base_url('admin/perintah-kerja/simpan-rangkuman'); ?>" method="post">
        <input type="hidden" name="gabungan" id="input-gabungan">
        <input type="hidden" name="perOutlet" id="input-perOutlet">
        <input type="hidden" name="tanggal" id="input-tanggal">
        <?php foreach ($kekurangan_per_outlet as $outletData): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Outlet: <?= esc($outletData['outlet']) ?></strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Kode Bahan</th>
                                    <th>Nama Bahan</th>
                                    <th>Satuan</th>
                                    <th>Kebutuhan</th>
                                    <th>Stok</th>
                                    <th>Kekurangan</th>
                                    <th>Pembulatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($outletData['data'] as $row): ?>
                                    <?php $pembulatan = ($row['kurang'] > 0) ? ceil($row['kurang']) : 0; ?>
                                    <tr>
                                        <td><?= esc($row['kode_bahan']) ?></td>
                                        <td><?= esc($row['nama_bahan'] ?? '-') ?></td>
                                        <td><?= esc($row['satuan']) ?></td>
                                        <td><?= number_format($row['kebutuhan'], 2) ?></td>
                                        <td><?= number_format($row['stok'], 2) ?></td>
                                        <td class="text-danger font-weight-bold">
                                            <?= number_format($row['kurang'], 2) ?>
                                        </td>
                                        <td class="text-center text-primary font-weight-bold">
                                            <?= $pembulatan ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="text-center mt-4">
            <button type="button" id="btn-rangkum" class="btn btn-warning">Rangkum kebutuhan outlet</button>
        </div>

        <div id="tabel-rangkuman-gabungan" class="mt-4" style="display:none;"></div>
        <div class="text-center mt-3" id="container-btn-simpan" style="display:none;">
            <button type="submit" class="btn btn-success">Simpan Rangkuman Kekurangan</button>
        </div>
    </form>
</div>

<script>
    let rangkumanGabunganData = [];
    let rangkumanPerOutletData = [];
    let tanggalRangkuman = (new Date()).toISOString().slice(0, 10);

    document.getElementById('btn-rangkum').addEventListener('click', function() {
        // Gabungkan kekurangan seluruh outlet
        const kekuranganPerOutlet = <?= json_encode($kekurangan_per_outlet) ?>;
        const gabungan = {};
        rangkumanPerOutletData = [];
        kekuranganPerOutlet.forEach(outlet => {
            outlet.data.forEach(row => {
                const kode = row.kode_bahan;
                const nama = row.nama_bahan || '-';
                const satuan = row.satuan || '-';
                const kebutuhan = parseFloat(row.kebutuhan) || 0;
                const stok = parseFloat(row.stok) || 0;
                let kurang = kebutuhan - stok;
                if (kurang < 0.0001) kurang = 0;
                let tipe = kode.startsWith('BSJ') ? 'bsj' : 'bahan';
                if (!gabungan[kode]) {
                    gabungan[kode] = {
                        tipe,
                        nama,
                        satuan,
                        kurang: 0
                    };
                }
                gabungan[kode].kurang += kurang;

                // Per outlet data for saving
                let pembulatan = kurang > 0.0001 ? Math.ceil(kurang) : 0;
                rangkumanPerOutletData.push({
                    outlet: outlet.outlet,
                    kode_bahan: kode,
                    tipe,
                    nama_barang: nama,
                    satuan,
                    kekurangan: kurang,
                    pembulatan,
                });
            });
        });
        // Buat tabel
        let html = '<h5 class="text-center">Rangkuman Kekurangan Gabungan Semua Outlet</h5>';
        html += '<div class="table-responsive"><table class="table table-bordered table-striped">';
        html += '<thead class="table-secondary text-center"><tr>' +
            '<th>Tipe</th>' +
            '<th>Nama Barang</th>' +
            '<th>Satuan</th>' +
            '<th>Kekurangan</th>' +
            '<th>Pembulatan</th>' +
            '</tr></thead><tbody>';
        // Hitung pembulatan gabungan dari masing-masing outlet
        const pembulatanGabungan = {};
        kekuranganPerOutlet.forEach(outlet => {
            outlet.data.forEach(row => {
                const kode = row.kode_bahan;
                const kebutuhan = parseFloat(row.kebutuhan) || 0;
                const stok = parseFloat(row.stok) || 0;
                let kurang = kebutuhan - stok;
                if (Math.abs(kurang) < 0.0001) kurang = 0;
                let pembulatan = kurang > 0.0001 ? Math.ceil(kurang) : 0;
                if (!pembulatanGabungan[kode]) pembulatanGabungan[kode] = 0;
                if (pembulatan > 0) {
                    pembulatanGabungan[kode] += pembulatan;
                }
            });
        });

        rangkumanGabunganData = [];
        Object.entries(gabungan).forEach(([kode, item]) => {
            if (item.kurang > 0) {
                rangkumanGabunganData.push({
                    kode_bahan: kode,
                    tipe: item.tipe,
                    nama_barang: item.nama,
                    satuan: item.satuan,
                    kekurangan: item.kurang,
                    pembulatan: pembulatanGabungan[kode]
                });
                html += `<tr>
                    <td class="text-center">${item.tipe}</td>
                    <td>${item.nama}</td>
                    <td class="text-center">${item.satuan}</td>
                    <td class="text-danger fw-bold text-end">${item.kurang.toFixed(2)}</td>
                    <td class="text-center text-primary fw-bold">${pembulatanGabungan[kode]}</td>
                </tr>`;
            }
        });
        html += '</tbody></table></div>';
        const rangkumanDiv = document.getElementById('tabel-rangkuman-gabungan');
        rangkumanDiv.innerHTML = html;
        rangkumanDiv.style.display = 'block';
        document.getElementById('container-btn-simpan').style.display = 'block';
    });

    document.getElementById('form-kekurangan-per-outlet').addEventListener('submit', function(e) {
        if (!rangkumanGabunganData.length || !rangkumanPerOutletData.length) {
            alert('Data rangkuman belum tersedia. Silakan klik tombol rangkum terlebih dahulu.');
            e.preventDefault();
            return;
        }
        // Data is only sent to backend on submit (Simpan button)
        document.getElementById('input-gabungan').value = JSON.stringify(rangkumanGabunganData);
        document.getElementById('input-perOutlet').value = JSON.stringify(rangkumanPerOutletData);
        document.getElementById('input-tanggal').value = tanggalRangkuman;
    });
</script>

<?= $this->endSection(); ?>