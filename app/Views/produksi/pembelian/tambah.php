<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>

    <form action="<?= base_url('produksi/pembelian/simpan'); ?>" method="post" enctype="multipart/form-data" id="form-pembelian">
        <div class="card mb-4">
            <div class="card-header">Informasi Umum</div>
            <div class="card-body">
                <div class="form-group">
                    <label>Tanggal Pembelian</label>
                    <input type="date" name="tanggal" class="form-control" required>
                </div>
                <!-- Dropdown Perintah Kerja -->
                <div class="form-group">
                    <label for="perintah_kerja">Perintah Kerja</label>
                    <select name="perintah_kerja_id" id="perintah_kerja" class="form-control">
                        <option value="">-- Pilih Perintah Kerja --</option>
                        <?php foreach ($perintah_kerja as $pk) : ?>
                            <option value="<?= $pk['id']; ?>" <?= ($perintah_kerja_id == $pk['id']) ? 'selected' : '' ?>>
                                <?= date('d-m-Y', strtotime($pk['tanggal'])) ?> - ID <?= $pk['id'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Field pemasok, tipe pembayaran, dan upload bukti transaksi dihapus sesuai permintaan -->
            </div>
        </div>

        <div id="container-bukti-transaksi"></div>
        <div class="card mb-4">
            <div class="card-header">Detail Bahan yang Dibeli</div>
            <div class="card-body" id="container-bahan">
                <!-- Baris bahan akan di-generate JS -->
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-secondary" onclick="addRow()">+ Tambah Bahan</button>
            </div>
        </div>

        <div class="form-group">
            <label>Total Pembelian</label>
            <input type="text" id="total-pembelian" name="total_harga" class="form-control" readonly>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="<?= base_url('produksi/pembelian'); ?>" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<script>
    let bahanAll = <?= json_encode($bahan_all); ?>;
    let bahanDariPerintah = <?= json_encode($bahan_dari_perintah ?? []); ?>;
    let count = 0;

    // Fungsi untuk menambahkan baris baru manual
    function addRow(preset) {
        const wrapper = document.getElementById('container-bahan');
        const options = bahanAll.map(b =>
            `<option value="${b.id}" data-nama="${b.nama}" data-satuan="${b.satuan}" data-kategori="${b.kategori}">
                ${b.nama}
            </option>`
        ).join('');

        // Pemasok, tipe pembayaran, dan upload bukti transaksi per baris
        const pemasokArr = <?= json_encode(array_map(function ($s) {
                                return ['id' => $s['id'], 'nama' => $s['nama']];
                            }, $pemasok)); ?>;
        const pemasokOptions = pemasokArr.map(s =>
            `<option value="${s.id}">${s.nama}</option>`
        ).join('');

        const html = `
        <div class="form-row border p-2 mb-2" data-index="${count}">
            <div class="col-md-3">
                <label>Nama Bahan</label>
                <select name="bahan_id[]" class="form-control bahan-select" required>
                    <option value="">-- Pilih --</option>
                    ${options}
                </select>
                <input type="hidden" name="nama_bahan[]" class="nama-bahan">
                <input type="hidden" name="kategori[]" class="kategori">
                <input type="hidden" name="satuan[]" class="satuan">
            </div>
            <div class="col-md-2">
                <label>Jumlah</label>
                <input type="number" name="jumlah[]" class="form-control jumlah" min="0" step="0.01" required>
            </div>
            <div class="col-md-2">
                <label>Harga Satuan</label>
                <input type="number" name="harga_satuan[]" class="form-control harga" min="0" step="1" required>
            </div>
            <div class="col-md-2">
                <label>Subtotal</label>
                <input type="text" name="subtotal[]" class="form-control subtotal" readonly>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.form-row').remove(); hitungTotal();">Hapus</button>
            </div>
            <div class="col-md-2">
                <label>Pemasok</label>
                <select name="pemasok_id[]" class="form-control pemasok-select">
                    <option value="">-- Pilih Pemasok --</option>
                    ${pemasokOptions}
                </select>
                <label class="mt-2">Tipe Pembayaran</label>
                <select name="tipe_pembayaran[]" class="form-control tipe-pembayaran-select">
                    <option value="">-- Pilih --</option>
                    <option value="tunai">Tunai</option>
                    <option value="kredit">Kredit</option>
                </select>
                
            </div>
        </div>`;

        wrapper.insertAdjacentHTML('beforeend', html);
        // Set pemasok & tipe pembayaran otomatis jika preset tersedia
        if (preset && preset.nama) {
            setPemasokTipe(count, preset.nama);
        }
        count++;
    }

    // Fungsi set pemasok & tipe pembayaran otomatis berdasarkan nama bahan
    function setPemasokTipe(idx, namaBahan) {
        const row = document.querySelector(`#container-bahan .form-row[data-index="${idx}"]`);
        if (!row) return;
        const pemasokSelect = row.querySelector('.pemasok-select');
        const tipeSelect = row.querySelector('.tipe-pembayaran-select');
        let pemasokNama = '';
        let tipe = '';
        const nama = namaBahan.trim().toLowerCase();
        if (nama === 'daging ayam' || nama === 'daging sapi') {
            pemasokNama = 'syarif';
            tipe = 'kredit';
        } else if (nama === 'tepung terigu') {
            pemasokNama = 'zidan';
            tipe = 'kredit';
        } else {
            pemasokNama = 'pasar';
            tipe = 'tunai';
        }
        // Pilih pemasok berdasarkan nama
        for (let opt of pemasokSelect.options) {
            if (opt.textContent.trim().toLowerCase() === pemasokNama) {
                pemasokSelect.value = opt.value;
                break;
            }
        }
        tipeSelect.value = tipe;
    }

    // Fungsi untuk tambah baris dengan data preset (dari perintah kerja)
    function addRowWithData(data) {
        addRow(data);
        const row = document.querySelector(`#container-bahan .form-row[data-index="${count - 1}"]`);
        const select = row.querySelector('.bahan-select');
        select.value = data.bahan_id || '';
        row.querySelector('.nama-bahan').value = data.nama || '';
        row.querySelector('.kategori').value = data.kategori || '';
        row.querySelector('.satuan').value = data.satuan || '';
        // Jumlah diisi dari field pembulatan jika ada, jika tidak ada baru jumlah
        let jumlahValue = (typeof data.pembulatan !== 'undefined' && data.pembulatan !== null) ?
            parseFloat(data.pembulatan) :
            (typeof data.jumlah !== 'undefined' ? parseFloat(data.jumlah) : '');
        row.querySelector('.jumlah').value = jumlahValue !== '' ? jumlahValue.toFixed(2) : '';
        row.querySelector('.harga').value = '';
        row.querySelector('.subtotal').value = '';
        // Set pemasok & tipe pembayaran otomatis
        if (data.nama) setPemasokTipe(count - 1, data.nama);
        renderBuktiTransaksi();
    }

    // Inisialisasi bahan dari perintah kerja (jika ada)
    document.addEventListener('DOMContentLoaded', () => {
        if (bahanDariPerintah.length > 0) {
            bahanDariPerintah.forEach(b => addRowWithData(b));
        } else {
            addRow();
        }
    });

    // Event: saat dropdown perintah kerja berubah, ambil detail bahan via AJAX
    document.getElementById('perintah_kerja').addEventListener('change', function() {
        const id = this.value;
        // Hapus semua baris bahan
        document.getElementById('container-bahan').innerHTML = '';
        count = 0;
        if (!id) {
            addRow();
            return;
        }
        fetch('<?= base_url('produksi/pembelian/get_detail_perintah_kerja'); ?>/' + id)
            .then(res => res.json())
            .then(data => {
                // Filter hanya bahan dengan pembulatan > 0
                const filtered = Array.isArray(data) ? data.filter(b => b.pembulatan && parseFloat(b.pembulatan) > 0) : [];
                if (filtered.length > 0) {
                    filtered.forEach(b => addRowWithData(b));
                } else {
                    addRow();
                }
            })
            .catch(() => addRow());
    });

    // Event: perubahan dropdown bahan atau jumlah/harga
    document.addEventListener('change', function(e) {
        const row = e.target.closest('.form-row');
        if (!row) return;

        if (e.target.classList.contains('bahan-select')) {
            const selected = e.target.selectedOptions[0];
            row.querySelector('.nama-bahan').value = selected.dataset.nama;
            row.querySelector('.kategori').value = selected.dataset.kategori;
            row.querySelector('.satuan').value = selected.dataset.satuan;
            // Set pemasok & tipe pembayaran otomatis saat bahan dipilih manual
            if (selected.dataset.nama) setPemasokTipe(row.getAttribute('data-index'), selected.dataset.nama);
        }

        if (e.target.classList.contains('jumlah') || e.target.classList.contains('harga')) {
            const jumlah = parseFloat(row.querySelector('.jumlah').value) || 0;
            const harga = parseFloat(row.querySelector('.harga').value) || 0;
            const subtotal = jumlah * harga;
            row.querySelector('.subtotal').value = formatRupiah(subtotal);
            hitungTotal();
        }
        if (e.target.classList.contains('pemasok-select')) {
            renderBuktiTransaksi();
        }
    });

    // Render input bukti transaksi sesuai pemasok unik yang dipilih
    function renderBuktiTransaksi() {
        // Ambil semua pemasok yang dipilih
        const pemasokIds = Array.from(document.querySelectorAll('.pemasok-select')).map(sel => sel.value).filter(v => v);
        // Ambil nama pemasok dari option
        const pemasokNames = Array.from(document.querySelectorAll('.pemasok-select')).map(sel => {
            const opt = sel.options[sel.selectedIndex];
            return opt ? opt.textContent.trim() : '';
        });
        // Gabungkan id dan nama
        let pemasokUnik = [];
        pemasokIds.forEach((id, i) => {
            if (id && !pemasokUnik.some(p => p.id === id)) {
                pemasokUnik.push({
                    id,
                    nama: pemasokNames[i]
                });
            }
        });
        // Tampilkan input file hanya untuk pemasok unik
        const container = document.getElementById('container-bukti-transaksi');
        container.innerHTML = '';
        pemasokUnik.forEach((p, idx) => {
            container.innerHTML += `
                <div class="form-group">
                    <label>Bukti Transaksi untuk Pemasok <strong>${p.nama}</strong></label>
                    <input type="file" name="bukti_transaksi_${p.id}" class="form-control-file">
                </div>
            `;
        });
    }

    // Render awal input bukti transaksi
    document.addEventListener('DOMContentLoaded', function() {
        renderBuktiTransaksi();
    });

    // Hitung total pembelian keseluruhan
    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(el => {
            // Ambil angka dari format Rp
            let val = el.value.replace(/[^\d]/g, '');
            total += parseInt(val) || 0;
        });
        document.getElementById('total-pembelian').value = formatRupiah(total);
    }

    // Format angka ke Rupiah: Rp 1.234.567
    function formatRupiah(angka) {
        angka = Math.floor(angka);
        return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
</script>


<?= $this->endSection(); ?>