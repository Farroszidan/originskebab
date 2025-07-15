<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container-fluid mt-4">
    <h4 class="mb-4 text-gray-800 font-weight-bold">Manajemen Transaksi & Operasional</h4>

    <form action="<?= base_url('transaksi/store') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="jenis_form">Jenis Form</label>
                <select id="jenis_form" name="jenis_form" class="form-control" required>
                    <option value="">-- Pilih Jenis Form --</option>
                    <option value="permintaan">Permintaan</option>
                    <option value="pengiriman">Pengiriman</option>
                    <option value="bukti_pembelian">Bukti Pembelian</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="tujuan_role">Kirim Ke</label>
                <select id="tujuan_role" name="tujuan_role" class="form-control" required>
                    <option value="">-- Pilih Role Tujuan --</option>
                    <?php
                    $roles = ['admin', 'penjualan', 'produksi', 'keuangan'];
                    foreach ($roles as $role) {
                        if (in_groups($role) && $role !== 'admin') continue;
                        echo "<option value='$role'>" . ucfirst($role) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Kontainer Form Dinamis -->
        <div id="form-dinamis" class="mt-3"></div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            <button type="reset" class="btn btn-secondary ml-2"><i class="fas fa-undo"></i> Reset</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jenisForm = document.getElementById('jenis_form');
        const tujuanRole = document.getElementById('tujuan_role');
        const formDinamis = document.getElementById('form-dinamis');

        jenisForm.addEventListener('change', renderForm);
        tujuanRole.addEventListener('change', renderForm);

        function renderForm() {
            const jenis = jenisForm.value;
            const tujuan = tujuanRole.value;
            let html = '';

            if (jenis === 'permintaan') {
                html += `
                    <div class="form-group">
                        <label for="tanggal">Tanggal Permintaan</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="catatan">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3"></textarea>
                    </div>
                `;

                if (tujuan === 'penjualan') {
                    html += `
                        <div class="form-group">
                            <label for="outlet_id">Outlet Tujuan</label>
                            <select name="outlet_id" id="outlet_id" class="form-control" required>
                                <option value="">-- Pilih Outlet --</option>
                                <?php foreach ($outlets as $outlet): ?>
                                    <option value="<?= $outlet['id'] ?>"><?= $outlet['nama_outlet'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    `;
                }

                html += `
                    <label>Detail Barang</label>
                    <div id="barang-container"></div>
                    <button type="button" class="btn btn-sm btn-success my-2" onclick="tambahBarang()">+ Tambah Barang</button>
                `;
            } else if (jenis === 'pengiriman') {
                const bahanList = [
                    'Ayam', 'Sapi', 'Kulit', 'Signature sauce',
                    'Saos Sambal sachet', 'Saos Demiglace', 'Saos Mentai',
                    'Mayonaise', 'Red cheddar', 'Selada',
                    'Aluminium foil', 'Stiker', 'Plastik kresek'
                ];

                html += `
                    <div class="form-group">
                        <label for="tanggal">Tanggal Pengiriman</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <label>Detail Pengiriman</label>
                    <div class="row">
                        ${bahanList.map(bahan => `
                            <div class="form-group col-md-6">
                                <label>${bahan}</label>
                                <input type="number" step="0.01" name="jumlah[${bahan}]" class="form-control" placeholder="Jumlah">
                            </div>
                        `).join('')}
                    </div>
                    <div class="form-group">
                        <label for="catatan">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3"></textarea>
                    </div>
                `;
            } else if (jenis === 'bukti_pembelian') {
                html += `
                    <div class="form-group">
                        <label for="tanggal">Tanggal Bukti Pembelian</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama Penginput</label>
                        <input type="text" name="nama" class="form-control" value="<?= user()->username ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="outlet">Outlet</label>
                        <input type="text" class="form-control" value="<?= outlet_nama(user()->outlet_id) ?>" readonly>
                        <input type="hidden" name="outlet_id" value="<?= user()->outlet_id ?>">
                    </div>
                    <div class="form-group">
                        <label for="gambar">Upload Bukti Transaksi</label>
                        <input type="file" name="gambar" class="form-control-file" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3"></textarea>
                    </div>
                    <label>Detail Barang yang Dibeli</label>
                    <div id="barang-container"></div>
                    <button type="button" class="btn btn-sm btn-success my-2" onclick="tambahBarang()">+ Tambah Barang</button>
                `;
            }

            formDinamis.innerHTML = html;
        }
    });

    let indexBarang = 0;

    function tambahBarang() {
        const container = document.getElementById('barang-container');
        const div = document.createElement('div');
        div.className = "form-row mb-2";
        div.innerHTML = `
            <div class="col-md-8">
                <input type="text" name="barang[${indexBarang}][nama]" class="form-control" placeholder="Nama Barang" required>
            </div>
            <div class="col-md-4">
                <input type="number" name="barang[${indexBarang}][jumlah]" class="form-control" placeholder="Jumlah" required>
            </div>
        `;
        container.appendChild(div);
        indexBarang++;
    }
</script>

<?= $this->endSection() ?>