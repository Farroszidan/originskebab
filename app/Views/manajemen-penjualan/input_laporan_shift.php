<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid mt-4">
    <h3 class="mb-4 font-weight-bold text-gray-800">Input Laporan Shift</h3>

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
                <?php elseif (in_groups('penjualan')) : ?>
                    <input type="hidden" name="outlet_id" id="outlet_id" value="<?= user()->outlet_id ?>">
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
                <label for="total_penjualan_display">Total Penjualan</label>
                <input type="text" id="total_penjualan_display" class="form-control" readonly placeholder="Rp0">
                <input type="hidden" id="total_penjualan_raw" name="total_penjualan">
            </div>
            <div class="form-group col-md-6">
                <label for="total_pengeluaran_display">Total Pengeluaran</label>
                <input type="text" id="total_pengeluaran_display" class="form-control" readonly placeholder="Rp0">
                <input type="hidden" id="total_pengeluaran_raw" name="total_pengeluaran">
            </div>
        </div>

        <div class="form-group">
            <label for="keterangan_pengeluaran">Rincian Pengeluaran</label>
            <textarea id="keterangan_pengeluaran" name="keterangan_pengeluaran" class="form-control" rows="3" readonly placeholder="Tidak ada pengeluaran"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Simpan Laporan
        </button>
    </form>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- AJAX Script -->
<script>
    $(document).ready(function() {
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
                        // Format dan tampilkan
                        $('#total_penjualan_display').val(formatRupiah(res.total_penjualan));
                        $('#total_pengeluaran_display').val(formatRupiah(res.total_pengeluaran));
                        $('#total_penjualan_raw').val(res.total_penjualan);
                        $('#total_pengeluaran_raw').val(res.total_pengeluaran);
                        $('#keterangan_pengeluaran').val(res.keterangan_pengeluaran || 'Tidak ada pengeluaran');
                    },
                    error: function() {
                        alert("Gagal memuat data laporan.");
                    }
                });
            }
        }

        function formatRupiah(angka) {
            if (!angka || isNaN(angka)) return 'Rp0';
            return 'Rp' + parseInt(angka).toLocaleString('id-ID');
        }

        $('#tanggal, #outlet_id, #shift_id').on('change', fetchLaporanData);
    });
</script>

<?= $this->endSection(); ?>