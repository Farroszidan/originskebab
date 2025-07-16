<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid mt-4">
    <h3 class="mb-4 font-weight-bold text-dark">Laporan Shift Harian</h3>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('manajemen-penjualan/simpanLaporanShift') ?>" method="post" id="formLaporanShift">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="tanggal">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label for="outlet_id">Outlet</label>
                <?php if (in_groups('admin')) : ?>
                    <select id="outlet_id" name="outlet_id" class="form-control" required>
                        <option value="">-- Pilih Outlet --</option>
                        <?php foreach ($outlets as $outlet) : ?>
                            <option value="<?= $outlet['id'] ?>"><?= esc($outlet['nama_outlet']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else : ?>
                    <input type="hidden" id="outlet_id" name="outlet_id" value="<?= user()->outlet_id ?>">
                    <input type="text" class="form-control" value="<?= esc($nama_outlet ?? 'Outlet Tidak Diketahui') ?>" readonly>
                <?php endif; ?>
            </div>

            <div class="form-group col-md-4">
                <label for="shift_id">Shift</label>
                <select id="shift_id" name="shift_id" class="form-control" required>
                    <option value="">-- Pilih Shift --</option>
                    <?php foreach ($shifts as $shift) : ?>
                        <option value="<?= $shift['id'] ?>">
                            <?= esc($shift['nama_shift']) ?> (<?= $shift['jam_mulai'] ?> - <?= $shift['jam_selesai'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Total Penjualan</label>
                <input type="text" id="total_penjualan_display" class="form-control" readonly placeholder="Rp0">
                <input type="hidden" id="total_penjualan_raw" name="total_penjualan">
            </div>
            <div class="form-group col-md-6">
                <label>Total Pengeluaran</label>
                <input type="text" id="total_pengeluaran_display" class="form-control" readonly placeholder="Rp0">
                <input type="hidden" id="total_pengeluaran_raw" name="total_pengeluaran">
            </div>
        </div>

        <div class="form-group">
            <label>Rincian Pengeluaran</label>
            <textarea id="keterangan_pengeluaran" name="keterangan_pengeluaran" class="form-control" rows="3" readonly placeholder="Tidak ada pengeluaran"></textarea>
        </div>

        <div class="form-group text-right">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan Laporan
            </button>
        </div>
    </form>

    <!-- Rincian Penjualan -->
    <div id="rincianPenjualanContainer" style="display:none">
        <h5 class="mt-5 font-weight-bold">📋 Rincian Penjualan</h5>
        <div class="table-responsive">
            <table class="table table-sm table-bordered text-sm">
                <thead class="thead-light">
                    <tr>
                        <th>Jenis Pembayaran</th>
                        <th>Total</th>
                        <th>Potongan (%)</th>
                        <th>Nilai Potongan</th>
                        <th>Setelah Potongan</th>
                    </tr>
                </thead>
                <tbody id="rincianPenjualanBody"></tbody>
            </table>
        </div>
    </div>

    <!-- Rincian Pengeluaran -->
    <div id="rincianPengeluaranContainer" style="display:none">
        <h5 class="mt-4 font-weight-bold">🧾 Rincian Pengeluaran</h5>
        <div class="table-responsive">
            <table class="table table-sm table-bordered text-sm">
                <thead class="thead-light">
                    <tr>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Total</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody id="rincianPengeluaranBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- AJAX Script -->
<script>
    $(document).ready(function() {
        function formatRupiah(angka) {
            if (!angka || isNaN(angka)) return 'Rp0';
            return 'Rp' + parseInt(angka).toLocaleString('id-ID');
        }

        function fetchLaporanData() {
            const tanggal = $('#tanggal').val();
            const outlet_id = $('#outlet_id').val();
            const shift_id = $('#shift_id').val();

            if (tanggal && outlet_id && shift_id) {
                $.ajax({
                    url: '<?= base_url('manajemen-penjualan/getDataShift') ?>',
                    type: 'GET',
                    data: {
                        tanggal,
                        outlet_id,
                        shift_id
                    },
                    success: function(res) {
                        // Tampilkan Total Penjualan & Pengeluaran
                        $('#total_penjualan_display').val(formatRupiah(res.total_netto));
                        $('#total_pengeluaran_display').val(formatRupiah(res.total_pengeluaran));
                        $('#total_penjualan_raw').val(res.total_netto);
                        $('#total_pengeluaran_raw').val(res.total_pengeluaran);
                        $('#keterangan_pengeluaran').val(res.keterangan_pengeluaran || 'Tidak ada pengeluaran');

                        // Reset dan tampilkan Rincian Penjualan
                        const $penjualanBody = $('#rincianPenjualanBody');
                        $penjualanBody.empty(); // Hapus semua baris lama
                        $('#rincianPenjualanContainer').hide(); // Sembunyikan dulu

                        if (Array.isArray(res.rincian_penjualan) && res.rincian_penjualan.length > 0) {
                            let totalBruto = 0;
                            let totalPotongan = 0;
                            let totalNetto = 0;

                            res.rincian_penjualan.forEach(item => {
                                totalBruto += parseFloat(item.bruto);
                                totalPotongan += parseFloat(item.potongan);
                                totalNetto += parseFloat(item.netto);

                                const row = `
                                    <tr>
                                        <td>${item.jenis}</td>
                                        <td>${formatRupiah(item.bruto)}</td>
                                        <td>${parseFloat(item.persen).toFixed(1)}%</td>
                                        <td>${formatRupiah(item.potongan)}</td>
                                        <td>${formatRupiah(item.netto)}</td>
                                    </tr>`;
                                $penjualanBody.append(row);
                            });

                            const totalRow = `
                                <tr class="font-weight-bold bg-light">
                                    <td>Total</td>
                                    <td>${formatRupiah(totalBruto)}</td>
                                    <td>-</td>
                                    <td>${formatRupiah(totalPotongan)}</td>
                                    <td>${formatRupiah(totalNetto)}</td>
                                </tr>`;
                            $penjualanBody.append(totalRow);

                            $('#rincianPenjualanContainer').show(); // Tampilkan container
                        }

                        // Reset dan tampilkan Rincian Pengeluaran
                        const $pengeluaranBody = $('#rincianPengeluaranBody');
                        $pengeluaranBody.empty();
                        $('#rincianPengeluaranContainer').hide();

                        if (Array.isArray(res.rincian_pengeluaran) && res.rincian_pengeluaran.length > 0) {
                            res.rincian_pengeluaran.forEach(item => {
                                const row = `
                                    <tr>
                                        <td>${item.nama_barang}</td>
                                        <td>${item.jumlah}</td>
                                        <td>${formatRupiah(item.harga)}</td>
                                        <td>${formatRupiah(item.total)}</td>
                                        <td>${item.keterangan ?? '-'}</td>
                                    </tr>`;
                                $pengeluaranBody.append(row);
                            });

                            $('#rincianPengeluaranContainer').show();
                        }
                    },
                    error: function() {
                        alert("Gagal memuat data laporan.");
                    }
                });
            }
        }

        // Panggil ulang saat ada perubahan input
        $('#tanggal, #outlet_id, #shift_id').on('change', fetchLaporanData);
    });
</script>


<?= $this->endSection(); ?>