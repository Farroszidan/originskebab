<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #ffffff;
        color: #6b7280;
        margin: 0;
        padding: 0;
    }

    h1.page-title {
        font-weight: 800;
        font-size: 3rem;
        color: #111827;
        margin-bottom: 2rem;
    }

    label.form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.375rem;
        display: block;
    }

    input.form-control,
    select.form-control {
        font-size: 1rem;
        color: #111827;
        border-radius: 0.5rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    input.form-control:focus,
    select.form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.3);
        background-color: #fff;
    }

    .btn-primary {
        background-color: #2563eb;
        border: none;
        font-weight: 600;
        border-radius: 0.5rem;
        font-size: 1rem;
        padding: 0.55rem 1.25rem;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover,
    .btn-primary:focus {
        background-color: #1d4ed8;
    }

    .btn-danger {
        background-color: #dc2626;
        border: none;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 1rem;
        transition: background-color 0.3s ease;
    }

    .btn-danger:hover,
    .btn-danger:focus {
        background-color: #b91c1c;
    }

    .btn-success {
        background-color: #16a34a;
        border: none;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 1rem;
        transition: background-color 0.3s ease;
    }

    .btn-success:hover,
    .btn-success:focus {
        background-color: #15803d;
    }

    .card {
        background-color: #f9fafb;
        border-radius: 0.75rem;
        box-shadow: 0 8px 24px rgb(0 0 0 / 0.08);
        padding: 2rem;
        margin-bottom: 2.5rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #d1d5db;
    }

    thead tr {
        background-color: #2563eb;
        color: white;
    }

    thead th,
    tbody td {
        padding: 1rem;
        text-align: center;
        border: 1px solid #d1d5db;
    }

    .top-right-total {
        background-color: #eff6ff;
        color: #16a34a;
        font-size: 1.2rem;
        font-weight: 700;
        padding: 0.75rem;
        border-radius: 0.75rem;
        text-align: center;
        user-select: none;
        max-width: 250px;
        margin-left: auto;
        margin-bottom: 1.5rem;
    }

    .bg-black {
        background-color: #111827;
        color: #fbbf24;
        font-size: 1.25rem;
        font-weight: 700;
        text-align: center;
        padding: 1rem;
        border-radius: 0.75rem;
        user-select: none;
        max-width: 600px;
        margin: 2rem auto 0;
    }

    @media (max-width: 768px) {
        .header-row {
            flex-direction: column !important;
        }

        .top-right-total {
            max-width: 100%;
        }
    }
</style>

<div class="container my-5" role="main" aria-label="Input Transaksi">
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-3" style="gap:1rem;">
        <div class="d-flex flex-wrap" style="gap: 1rem;">
            <div>
                <label for="nofaktur" class="form-label">No Faktur</label>
                <input type="text" id="nofaktur" class="form-control" value="<?= esc($no_faktur) ?>" readonly style="width: 140px;">
            </div>
            <div>
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="text" id="tanggal" class="form-control" value="<?= esc($tgl_jual) ?>" readonly style="width: 120px;">
            </div>
            <div>
                <label for="jam" class="form-label">Jam</label>
                <input type="text" id="jam" class="form-control" value="<?= esc($jam_jual) ?>" readonly style="width: 100px;">
            </div>
            <div>
                <label for="kasir" class="form-label">Kasir</label>
                <input type="text" id="kasir" class="form-control" value="<?= esc($nama_kasir) ?>" readonly style="width: 140px;">
            </div>
            <?php if (in_groups('admin')) : ?>
                <div style="min-width: 170px;">
                    <label for="select_outlet_id" class="form-label">Pilih Outlet</label>
                    <select id="select_outlet_id" class="form-control" name="outlet_id">
                        <option value="">-- Pilih Outlet --</option>
                        <?php foreach ($outlets as $outlet) : ?>
                            <option value="<?= esc($outlet['id']) ?>" <?= (isset($selected_outlet_id) && $selected_outlet_id == $outlet['id']) ? 'selected' : '' ?>>
                                <?= esc($outlet['nama_outlet']) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
            <?php else : ?>
                <input type="hidden" id="hidden_outlet_id" name="outlet_id" value="<?= esc(user()->outlet_id) ?>">
            <?php endif ?>

        </div>

        <!-- Total di kanan atas -->
        <div id="total-bayar" class="top-right-total mt-5 mt-md-0" style="min-width: 300px;">
            Rp 0,-
        </div>
    </div>

    <!-- Input Barang -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form id="formTambahMenu">
                <!-- Baris Pertama -->
                <div class="form-row">
                    <div class="col-md-2">
                        <label for="kode_menu" class="form-label">Kode Menu</label>
                        <input type="text" class="form-control" id="kode_menu" name="kode_menu[]" placeholder="Kode Menu" disabled>
                        <div id="suggestions" class="list-group position-absolute w-100" style="z-index: 9999; display: none;"></div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" id="btnF4" class="btn btn-primary w-100">F4</button>
                    </div>
                    <div class="col-md-3">
                        <label for="nama_menu" class="form-label">Nama Menu</label>
                        <input type="text" class="form-control" id="nama_menu" name="nama_menu[]" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <input type="text" class="form-control" id="kategori" name="kategori[]" readonly>
                    </div>
                </div>

                <!-- Baris Kedua -->
                <div class="form-row mt-3">
                    <div class="col-md-2">
                        <label for="add_ons" class="form-label">Add Ons</label>
                        <select class="form-control" id="add_ons" name="add_ons[]" disabled>
                            <option value="0">-</option>
                            <option value="4000">Omellete</option>
                            <option value="4000">Red Cheddar</option>
                            <option value="4000">Scramble Egg</option>
                            <option value="4000">Cheese Sauce</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="extra" class="form-label">Extra</label>
                        <select class="form-control" id="extra" name="extra[]" disabled>
                            <option value="0">-</option>
                            <option value="6000">Beef</option>
                            <option value="5000">Chicken</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="harga" class="form-label">Harga</label>
                        <input type="text" class="form-control" id="harga" name="harga[]" readonly>
                        <input type="hidden" id="harga_dasar" name="harga_dasar">
                    </div>
                    <div class="col-md-1">
                        <label for="qty" class="form-label">Qty</label>
                        <input type="number" class="form-control" id="qty" name="qty[]" min="1" value="1" disabled>
                    </div>
                    <div class="col-md-5 mt-3 d-flex flex-wrap align-items-end justify-content-start">
                        <button type="submit" class="btn btn-primary btn-sm mr-2 px-4">Add</button>
                        <button type="button" class="btn btn-danger btn-sm mr-2 px-3">Reset All</button>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalBayar">
                            <i class="fa fa-money-bill-wave"></i> Bayar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive mt-4" style="border-radius: 0.75rem;">
        <table id="tabel-menu" aria-label="Daftar menu transaksi" role="table" class="table table-borderless">
            <thead>
                <tr>
                    <th scope="col" style="width: 10%;">Kode</th>
                    <th scope="col" style="width: 12%;">Kategori</th>
                    <th scope="col">Nama Menu</th>
                    <th scope="col" style="width: 12%;">Harga</th>
                    <th scope="col" style="width: 8%;">Qty</th>
                    <th scope="col" style="width: 15%;">Sub Total</th>
                    <th scope="col" style="width: 10%;">Aksi</th>
                </tr>
            </thead>
            <tbody aria-live="polite" aria-relevant="all" aria-atomic="true" class="text-center align-middle">
                <!-- Dynamic data inserted here -->
            </tbody>
        </table>
    </div>

    <div class="bg-black" aria-live="polite" aria-atomic="true" id="terbilang">Nol Rupiah</div>
</div>

<!-- Modal Pembayaran -->
<div class="modal fade" id="modalBayar" tabindex="-1" role="dialog" aria-labelledby="modalBayarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formPembayaran" action="<?= base_url('manajemen-penjualan/simpanTransaksi') ?>" method="post" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="nofaktur" value="<?= esc($no_faktur) ?>">
            <input type="hidden" name="tgl_jual" value="<?= esc($tgl_jual) ?>">
            <input type="hidden" name="jam_jual" value="<?= esc($jam_jual) ?>">
            <input type="hidden" name="nama_kasir" value="<?= esc($nama_kasir) ?>">
            <input type="hidden" name="outlet_id" value="<?= esc($selected_outlet_id ?? user()->outlet_id) ?>">
            <input type="hidden" id="detail_transaksi" name="detail_transaksi" value="">

            <div class="modal-content rounded-lg">
                <div class="modal-header border-0 pb-2">
                    <h5 class="modal-title font-weight-bold text-dark" id="modalBayarLabel">Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true" class="h3 font-weight-bold">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <!-- Grand Total -->
                    <div class="form-group">
                        <label>Grand Total</label>
                        <div class="input-group">
                            <div class="input-group-prepend bg-light border rounded-left py-2 px-3 text-secondary">Rp</div>
                            <input type="text" id="grandTotalDisplay" class="form-control" disabled>
                            <input type="hidden" name="grand_total" id="grandTotal">
                        </div>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="form-group mt-3">
                        <label for="metode_pembayaran">Metode Pembayaran</label>
                        <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                            <option value="">-- Pilih Metode --</option>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="cashless">Non Tunai (Cashless)</option>
                        </select>
                    </div>

                    <!-- Jenis Cashless -->
                    <div class="form-group" id="jenis_cashless_group" style="display: none;">
                        <label for="jenis_cashless">Jenis Cashless</label>
                        <select name="jenis_cashless" id="jenis_cashless" class="form-control">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="gofood">GoFood</option>
                            <option value="grabfood">GrabFood</option>
                            <option value="shopeefood">ShopeeFood</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    <!-- Dibayar -->
                    <div class="form-group mb-3" id="dibayar_group" style="display: none;">
                        <label for="dibayar">Dibayar</label>
                        <div class="input-group">
                            <div class="input-group-prepend bg-light border rounded-left py-2 px-3 text-secondary">Rp</div>
                            <input type="text" name="dibayar" id="dibayar" class="form-control">
                        </div>
                    </div>

                    <!-- Kembalian -->
                    <div class="form-group" id="kembalian_group" style="display: none;">
                        <label for="kembalian">Kembalian</label>
                        <div class="input-group">
                            <div class="input-group-prepend bg-light border rounded-left py-2 px-3 text-secondary">Rp</div>
                            <input type="text" name="kembalian" id="kembalian" class="form-control" readonly>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-2 pb-3">
                    <button type="submit" class="btn btn-primary rounded-pill font-weight-bold px-4 py-2">Simpan Transaksi</button>
                    <button type="button" class="btn btn-secondary rounded-pill px-4 py-2" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(function() {
        // Saat ganti outlet, generate No Faktur baru
        $('#select_outlet_id').on('change', function() {
            var outletId = $(this).val();

            if (outletId) {
                $.ajax({
                    url: '/manajemen-penjualan/generate-nomor-faktur',
                    type: 'GET',
                    data: {
                        outlet_id: outletId
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#nofaktur').val(response.no_faktur);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Terjadi kesalahan saat mengambil No Faktur');
                    }
                });
            } else {
                $('#nofaktur').val('');
            }
        });

        // Sinkronisasi outlet ke hidden input di modal
        function syncOutletIdModal() {
            const val = $('#select_outlet_id').val() || "<?= esc(user()->outlet_id) ?>";
            $('#hidden_outlet_id_modal').val(val);
        }
        syncOutletIdModal();
        $('#select_outlet_id').on('change', syncOutletIdModal);

        // Tombol F4 aktifkan input menu
        $('#btnF4').on('click', function() {
            $('#kode_menu, #add_ons, #extra, #qty').prop('disabled', false).first().focus();
        });

        // Reset tabel menu
        $('.btn-danger').filter(function() {
            return $(this).text().trim() === 'Reset All';
        }).on('click', function() {
            $('#tabel-menu tbody').empty();
            updateTotal();
        });

        // Autocomplete kode menu
        $('#kode_menu').on('keyup', function() {
            const keyword = $(this).val().trim();
            const suggestionBox = $('#suggestions');
            if (keyword.length > 0) {
                $.ajax({
                    url: '<?= base_url('manajemen-penjualan/searchKodeMenuAutocomplete') ?>',
                    method: 'GET',
                    data: {
                        keyword
                    },
                    success: function(response) {
                        suggestionBox.empty();
                        if (response?.success && Array.isArray(response.data) && response.data.length) {
                            response.data.forEach(item => {
                                suggestionBox.append(`
                                    <a href="#" role="option" tabindex="0" class="list-group-item list-group-item-action" 
                                       data-kode="${item.kode_menu}" 
                                       data-nama="${item.nama_menu}" 
                                       data-kategori="${item.kategori}" 
                                       data-harga="${item.harga}">
                                       ${item.kode_menu} - ${item.nama_menu}</a>`);
                            });
                            suggestionBox.show().attr('aria-expanded', 'true');
                        } else {
                            suggestionBox.hide().attr('aria-expanded', 'false');
                        }
                    }
                });
            } else {
                suggestionBox.hide().attr('aria-expanded', 'false');
            }
        });

        // Pilih item dari hasil autocomplete
        $(document).on('click', '#suggestions a', function(e) {
            e.preventDefault();
            const $item = $(this);
            $('#kode_menu').val($item.data('kode'));
            $('#nama_menu').val($item.data('nama'));
            $('#kategori').val($item.data('kategori'));
            $('#harga_dasar').val($item.data('harga'));
            calculateHarga();
            $('#suggestions').hide().attr('aria-expanded', 'false');
        });

        // Sembunyikan suggestion saat klik di luar
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#kode_menu, #suggestions').length) {
                $('#suggestions').hide().attr('aria-expanded', 'false');
            }
        });

        // Hitung harga total per menu
        $('#add_ons, #extra, #qty').on('change', calculateHarga);

        function calculateHarga() {
            const basePrice = parseFloat($('#harga_dasar').val()) || 0;
            const addOns = parseFloat($('#add_ons').val()) || 0;
            const extra = parseFloat($('#extra').val()) || 0;
            const qty = parseInt($('#qty').val()) || 1;
            const total = (basePrice + addOns + extra) * qty;
            $('#harga').val('Rp ' + formatRupiah(total));
        }

        // Tambah menu ke tabel
        $('#formTambahMenu').on('submit', function(e) {
            e.preventDefault();

            const kode = $('#kode_menu').val().trim();
            const nama = $('#nama_menu').val().trim();
            const kategori = $('#kategori').val().trim();
            const add_ons_val = $('#add_ons').val();
            const add_ons_text = $('#add_ons option:selected').text();
            const extra_val = $('#extra').val();
            const extra_text = $('#extra option:selected').text();
            const qty = parseInt($('#qty').val());
            const hargaDasar = parseInt($('#harga_dasar').val()) || 0;
            const addOnPrice = parseInt(add_ons_val) || 0;
            const extraPrice = parseInt(extra_val) || 0;
            const hargaTotal = (hargaDasar + addOnPrice + extraPrice) * qty;

            if (!kode || !nama || !kategori || qty <= 0) {
                alert('Semua field harus terisi dan qty minimal 1!');
                return;
            }

            const newRow = `
            <tr>
                <td>${kode}</td>
                <td>${kategori}</td>
                <td>${nama}</td>
                <td>Rp ${hargaTotal.toLocaleString('id-ID')}</td>
                <td>${qty}</td>
                <td data-subtotal="${hargaTotal}">Rp ${hargaTotal.toLocaleString('id-ID')}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm btn-hapus" aria-label="Hapus item">×</button>
                    <input type="hidden" class="input-kode_menu" value="${kode}">
                    <input type="hidden" class="input-kategori" value="${kategori}">
                    <input type="hidden" class="input-nama_menu" value="${nama}">
                    <input type="hidden" class="input-harga" value="${hargaDasar + addOnPrice + extraPrice}">
                    <input type="hidden" class="input-qty" value="${qty}">
                    <input type="hidden" class="input-total_harga" value="${hargaTotal}">
                    <input type="hidden" class="input-add_ons" value="${add_ons_text !== '-' ? add_ons_text : ''}">
                    <input type="hidden" class="input-extra" value="${extra_text !== '-' ? extra_text : ''}">
                </td>
            </tr>`;

            $('#tabel-menu tbody').append(newRow);

            this.reset();
            $('#kode_menu, #add_ons, #extra, #qty').prop('disabled', true);
            $('#nama_menu, #kategori, #harga').val('');
            updateTotal();
        });

        // Hapus baris
        $(document).on('click', '.btn-hapus', function() {
            $(this).closest('tr').remove();
            updateTotal();
        });

        // Update total, grand total, dan terbilang
        function updateTotal() {
            let total = 0;
            $('#tabel-menu tbody tr').each(function() {
                total += parseInt($(this).find('td:eq(5)').data('subtotal')) || 0;
            });
            $('#total-bayar').text('Rp ' + formatRupiah(total));
            $('#grandTotal').val(total);
            $('#grandTotalDisplay').val(total.toLocaleString('id-ID'));
            updateTerbilang(total);
        }

        // Hitung kembalian saat input dibayar berubah
        $('#dibayar').on('input', function() {
            const dibayar = parseInt($(this).val().replace(/[^\d]/g, '')) || 0;
            const grandTotal = parseInt($('#grandTotal').val()) || 0;
            const kembali = dibayar - grandTotal;
            $('#kembalian').val(kembali >= 0 ? kembali.toLocaleString('id-ID') : '0');
        });

        // Sebelum submit transaksi
        $('#formPembayaran').on('submit', function(e) {
            const rows = $('#tabel-menu tbody tr');
            if (rows.length === 0) {
                alert('Tidak ada item di keranjang!');
                e.preventDefault();
                return;
            }

            let detail = [];
            rows.each(function() {
                const row = $(this);
                detail.push({
                    kode_menu: row.find('.input-kode_menu').val(),
                    kategori: row.find('.input-kategori').val(),
                    nama_menu: row.find('.input-nama_menu').val(),
                    harga: parseInt(row.find('.input-harga').val()) || 0,
                    qty: parseInt(row.find('.input-qty').val()) || 0,
                    total_harga: parseInt(row.find('.input-total_harga').val()) || 0,
                    add_ons: row.find('.input-add_ons').val() || '',
                    extra: row.find('.input-extra').val() || ''
                });
            });

            $('#detail_transaksi').val(JSON.stringify(detail));
        });

        // Modal Bayar muncul
        $('#modalBayar').on('show.bs.modal', function() {
            const grandTotalVal = $('#grandTotal').val() || 0;

            $('#grandTotalDisplay').val(parseInt(grandTotalVal).toLocaleString('id-ID'));

            // Reset semua pilihan pembayaran
            $('#metode_pembayaran').val('');
            $('#jenis_cashless').val('');
            $('#dibayar').val('');
            $('#kembalian').val('');

            $('#jenis_cashless_group').hide();
            $('#dibayar_group').hide();
            $('#kembalian_group').hide();
        });

        $('#metode_pembayaran').on('change', function() {
            const metode = $(this).val();

            if (metode === 'cashless') {
                $('#jenis_cashless_group').show();
                $('#dibayar_group').hide();
                $('#kembalian_group').hide();
                $('#dibayar').val('');
                $('#kembalian').val('');
            } else if (metode === 'cash') {
                $('#jenis_cashless_group').hide();
                $('#jenis_cashless').val('');
                $('#dibayar_group').show();
                $('#kembalian_group').show();
            } else {
                // Kosong atau tidak valid
                $('#jenis_cashless_group').hide();
                $('#dibayar_group').hide();
                $('#kembalian_group').hide();
                $('#jenis_cashless').val('');
                $('#dibayar').val('');
                $('#kembalian').val('');
            }
        });

        function formatRupiah(angka) {
            return parseInt(angka || 0).toLocaleString('id-ID');
        }

        function terbilang(angka) {
            const satuan = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
            angka = Math.floor(angka);
            if (angka === 0) return "Nol";
            if (angka < 12) return satuan[angka];
            if (angka < 20) return terbilang(angka - 10) + " Belas";
            if (angka < 100) return terbilang(Math.floor(angka / 10)) + " Puluh" + (angka % 10 ? " " + terbilang(angka % 10) : "");
            if (angka < 200) return "Seratus" + (angka > 100 ? " " + terbilang(angka - 100) : "");
            if (angka < 1000) return terbilang(Math.floor(angka / 100)) + " Ratus" + (angka % 100 ? " " + terbilang(angka % 100) : "");
            if (angka < 2000) return "Seribu" + (angka > 1000 ? " " + terbilang(angka - 1000) : "");
            if (angka < 1000000) return terbilang(Math.floor(angka / 1000)) + " Ribu" + (angka % 1000 ? " " + terbilang(angka % 1000) : "");
            if (angka < 1000000000) return terbilang(Math.floor(angka / 1000000)) + " Juta" + (angka % 1000000 ? " " + terbilang(angka % 1000000) : "");
            return "Angka terlalu besar";
        }

        function updateTerbilang(angka) {
            $('#terbilang').text(terbilang(angka) + " Rupiah");
        }

        // Inisialisasi total saat pertama load
        updateTotal();

        function updateJam() {
            const now = new Date();
            const jam = now.getHours().toString().padStart(2, '0');
            const menit = now.getMinutes().toString().padStart(2, '0');
            const detik = now.getSeconds().toString().padStart(2, '0');
            document.getElementById('jam').value = `${jam}:${menit}:${detik}`;
        }

        // Jalankan saat halaman dimuat
        updateJam();

        // Perbarui setiap detik
        setInterval(updateJam, 1000);
    });
</script>



<?= $this->endSection(); ?>