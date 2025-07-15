<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-4 text-gray-800">Tambah Pembelian</h1>
        </div>
        <div class="card-body">
            <form action="<?= base_url('manajemen-penjualan/pembelian-operasional/simpan') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- Outlet -->
                <div class="form-group">
                    <label for="outlet">Outlet</label>
                    <input type="text" class="form-control" value="<?= esc($outlet['nama_outlet'] ?? 'Outlet Tidak Diketahui') ?>" readonly>
                    <input type="hidden" name="outlet_id" value="<?= esc($outlet['id'] ?? '') ?>">
                </div>

                <!-- Tanggal Pembelian -->
                <div class="form-group">
                    <label for="tanggal">Tanggal Pembelian</label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control" required>
                </div>

                <!-- Daftar Barang Dinamis -->
                <div id="barang-container">
                    <div class="form-row barang-item mb-2">
                        <div class="col-md-4">
                            <input type="text" name="nama_barang[]" class="form-control" placeholder="Nama Barang" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="jumlah[]" class="form-control" placeholder="Jumlah" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="total[]" class="form-control" placeholder="Total Harga (Rp)" required>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm remove-barang">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-secondary btn-sm mb-3" id="tambah-barang">
                    <i class="fas fa-plus"></i> Tambah Barang
                </button>

                <!-- Upload Bukti -->
                <div class="form-group">
                    <label for="bukti">Upload Bukti Pembelian</label>
                    <input type="file" name="bukti" id="bukti" class="form-control-file" accept="image/*" required>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
                <a href="<?= base_url('manajemen-penjualan/pembelian-operasional') ?>" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<!-- JS untuk Tambah/Hapus Barang -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('barang-container');
        const tambahBtn = document.getElementById('tambah-barang');

        tambahBtn.addEventListener('click', function() {
            const item = document.querySelector('.barang-item');
            const clone = item.cloneNode(true);
            clone.querySelectorAll('input').forEach(input => input.value = '');
            container.appendChild(clone);
        });

        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-barang') || e.target.closest('.remove-barang')) {
                if (container.querySelectorAll('.barang-item').length > 1) {
                    e.target.closest('.barang-item').remove();
                }
            }
        });
    });
</script>

<?= $this->endSection(); ?>