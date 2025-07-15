<?php $this->extend('templates/index_templates_general'); ?>

<?php $this->section('page-content'); ?>
<div class="container-fluid mt-4">

    <!-- Heading -->
    <h4 class="mb-4 font-weight-bold text-gray-800">
        <i class="fas fa-utensils mr-2"></i> Master Data Menu
    </h4>

    <!-- Tombol Tambah Menu -->
    <button class="btn btn-outline-primary mb-3" data-toggle="modal" data-target="#modalTambahMenu">
        <i class="fas fa-plus mr-1"></i> Tambah Menu Penjualan
    </button>

    <!-- Table Menu -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white font-weight-bold">
            <i class="fas fa-list-ul mr-2"></i> Daftar Menu Penjualan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered m-0 table-hover text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Kode</th>
                            <th>Nama Menu</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        foreach ($menu as $item): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= esc($item['kode_menu']) ?></td>
                                <td><?= esc($item['nama_menu']) ?></td>
                                <td><?= esc($item['kategori']) ?></td>
                                <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalDetailMenu<?= $item['id'] ?>" title="Detail">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalEditMenu<?= $item['id'] ?>" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="<?= base_url('manajemen-penjualan/hapusMenuPenjualan/' . $item['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus menu ini?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($menu)): ?>
                            <tr>
                                <td colspan="6" class="text-muted">Belum ada data menu.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</div>

<!-- ========== MODAL TAMBAH ========== -->
<div class="modal fade" id="modalTambahMenu" tabindex="-1" role="dialog" aria-labelledby="modalTambahMenuLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form action="<?= base_url('manajemen-penjualan/simpanMenuPenjualan') ?>" method="post">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTambahMenuLabel">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Menu Penjualan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="kode_menu">Kode Menu</label>
                            <input type="text" class="form-control" id="kode_menu" name="kode_menu" placeholder="Contoh: PHE001" value="<?= old('kode_menu') ?>" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="kategori">Kategori</label>
                            <input type="text" class="form-control" id="kategori" name="kategori" readonly required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="nama_menu">Nama Menu</label>
                            <select class="form-control" id="nama_menu" name="nama_menu" required>
                                <option value="">-- Pilih Nama Menu --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Komposisi Dinamis -->
                    <div id="komposisi-container" style="display: none;" class="mt-3">
                        <label>Komposisi Bahan:</label>
                        <div id="komposisi-fields" class="row"></div>
                    </div>

                    <div class="form-group mt-3">
                        <label for="harga">Harga</label>
                        <input type="number" class="form-control" id="harga" name="harga" required>
                    </div>
                </div>


                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========== MODAL TAMBAH ========== -->






<!-- ========== MODAL EDIT ========== -->
<?php foreach ($menu as $item): ?>
    <?php $komposisi = json_decode($item['komposisi'], true); ?>
    <div class="modal fade" id="modalEditMenu<?= $item['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalEditMenuLabel<?= $item['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <form action="<?= base_url('manajemen-penjualan/editMenuPenjualan/' . $item['id']) ?>" method="post">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalEditMenuLabel<?= $item['id'] ?>">Edit Menu Penjualan</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body px-4">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="kode_menu_edit_<?= $item['id'] ?>">Kode Menu</label>
                                <input type="text" class="form-control" id="kode_menu_edit_<?= $item['id'] ?>" name="kode_menu" value="<?= esc($item['kode_menu']) ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="kategori_edit_<?= $item['id'] ?>">Kategori</label>
                                <input type="text" class="form-control" id="kategori_edit_<?= $item['id'] ?>" name="kategori" value="<?= esc($item['kategori']) ?>" readonly required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nama_menu_edit_<?= $item['id'] ?>">Nama Menu</label>
                            <select class="form-control" id="nama_menu_edit_<?= $item['id'] ?>" name="nama_menu" data-selected="<?= esc($item['nama_menu']) ?>" required>
                                <!-- Akan diisi via JS -->
                            </select>
                        </div>

                        <hr>
                        <label><strong>Komposisi</strong></label>
                        <div class="row">
                            <?php if (isset($komposisi) && is_array($komposisi) && count($komposisi) > 0): ?>
                                <?php foreach ($komposisi as $namaBahan => $data): ?>
                                    <div class="form-group col-md-6">
                                        <label><?= ucfirst(str_replace('_', ' ', $namaBahan)) ?></label>
                                        <div class="input-group">
                                            <input type="text" name="komposisi[<?= esc($namaBahan) ?>][jumlah]" class="form-control" value="<?= esc($data['jumlah'] ?? '') ?>" placeholder="Jumlah" required>
                                            <div class="input-group-append">
                                                <input type="text" name="komposisi[<?= esc($namaBahan) ?>][satuan]" class="form-control" style="width: 80px;" value="<?= esc($data['satuan'] ?? '') ?>" placeholder="Satuan" required>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-muted">Tidak ada komposisi.</div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="harga_edit_<?= $item['id'] ?>">Harga</label>
                            <input type="number" class="form-control" id="harga_edit_<?= $item['id'] ?>" name="harga" value="<?= esc($item['harga']) ?>" required>
                        </div>
                    </div>

                    <div class="modal-footer px-4">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Perbarui</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>



<!-- ========== MODAL EDIT ========== -->

<!-- ========== MODAL DETAIL ======== -->
<?php foreach ($menu as $item): ?>
    <?php $komposisi = json_decode($item['komposisi'], true); ?>
    <div class="modal fade" id="modalDetailMenu<?= $item['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalDetailMenuLabel<?= $item['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalDetailMenuLabel<?= $item['id'] ?>">Detail Komposisi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 py-3">
                    <strong>Nama Menu:</strong> <?= esc($item['nama_menu']) ?><br>
                    <strong>Kategori:</strong> <?= esc($item['kategori']) ?><br>
                    <strong>Harga:</strong> Rp <?= number_format($item['harga'], 0, ',', '.') ?><br><br>

                    <strong>Komposisi:</strong>
                    <ul>
                        <?php if (isset($komposisi) && is_array($komposisi) && count($komposisi) > 0): ?>
                            <?php foreach ($komposisi as $bahan => $data): ?>
                                <li><?= ucwords(str_replace('_', ' ', $bahan)) ?>: <?= esc($data['jumlah']) ?> <?= esc($data['satuan']) ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><em>Tidak ada komposisi</em></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="modal-footer px-4 py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>


<!-- =========== SCRIPT TAMBAH MENU, KODE BARANG UNTUK KATEGORI DAN UKURAN MUNCUL OTOMATIS =========== -->
<!-- Script Ajax Auto Lengkap -->
<script>
    document.getElementById('kode_menu').addEventListener('input', function() {
        const kode = this.value.toUpperCase();
        const prefix = kode.substring(0, 3);

        const kategoriInput = document.getElementById('kategori');
        const namaMenuSelect = document.getElementById('nama_menu');

        if (prefix.length === 3) {
            fetch(`<?= base_url('manajemen-penjualan/getVarianByKode/') ?>${prefix}`)
                .then(response => response.json())
                .then(data => {
                    namaMenuSelect.innerHTML = '<option value="">-- Pilih Nama Menu --</option>';

                    if (data && data.length > 0) {
                        kategoriInput.value = data[0].kategori;

                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.nama_menu;
                            option.textContent = item.nama_menu;
                            namaMenuSelect.appendChild(option);
                        });
                    } else {
                        kategoriInput.value = '';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    kategoriInput.value = '';
                    namaMenuSelect.innerHTML = '<option value="">-- Pilih Nama Menu --</option>';
                });
        } else {
            kategoriInput.value = '';
            namaMenuSelect.innerHTML = '<option value="">-- Pilih Nama Menu --</option>';
        }
    });
</script>

<!-- =========== SCRIPT TAMBAH MENU, KODE BARANG UNTUK KATEGORI DAN UKURAN MUNCUL OTOMATIS =========== -->


<!-- =========== SCRIPT EDIT TAMBAH MENU, KODE BARANG UNTUK KATEGORI DAN UKURAN MUNCUL OTOMATIS =========== -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($menu as $item): ?>
            const kodeInput<?= $item['id'] ?> = document.getElementById('kode_menu_edit_<?= $item['id'] ?>');
            const kategoriInput<?= $item['id'] ?> = document.getElementById('kategori_edit_<?= $item['id'] ?>');
            const namaMenuSelect<?= $item['id'] ?> = document.getElementById('nama_menu_edit_<?= $item['id'] ?>');
            const selectedNama<?= $item['id'] ?> = namaMenuSelect<?= $item['id'] ?>.getAttribute('data-selected');

            // Saat user menginput manual kode
            kodeInput<?= $item['id'] ?>.addEventListener('input', function() {
                loadNamaMenu<?= $item['id'] ?>(this.value.toUpperCase());
            });

            // Saat modal edit dibuka
            $('#modalEditMenu<?= $item['id'] ?>').on('shown.bs.modal', function() {
                loadNamaMenu<?= $item['id'] ?>(kodeInput<?= $item['id'] ?>.value.toUpperCase());
            });

            function loadNamaMenu<?= $item['id'] ?>(kode) {
                const prefix = kode.substring(0, 3);
                if (prefix.length !== 3) return;

                fetch(`<?= base_url('manajemen-penjualan/getVarianByKode/') ?>${prefix}`)
                    .then(response => response.json())
                    .then(data => {
                        namaMenuSelect<?= $item['id'] ?>.innerHTML = '<option value="">-- Pilih Nama Menu --</option>';

                        if (data && data.length > 0) {
                            kategoriInput<?= $item['id'] ?>.value = data[0].kategori;

                            data.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.nama_menu;
                                option.textContent = item.nama_menu;

                                if (item.nama_menu === selectedNama<?= $item['id'] ?>) {
                                    option.selected = true;
                                }

                                namaMenuSelect<?= $item['id'] ?>.appendChild(option);
                            });
                        }
                    })
                    .catch(err => {
                        console.error("Gagal fetch varian menu", err);
                        namaMenuSelect<?= $item['id'] ?>.innerHTML = '<option value="">-- Pilih Nama Menu --</option>';
                    });
            }
        <?php endforeach; ?>
    });
</script>


<!-- =========== SCRIPT EDIT TAMBAH MENU, KODE BARANG UNTUK KATEGORI DAN UKURAN MUNCUL OTOMATIS =========== -->


<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($menu as $item): ?>
            $('#modalEditMenu<?= $item['id'] ?>').on('shown.bs.modal', function() {
                const kodeInput = document.getElementById('kode_menu_edit_<?= $item['id'] ?>');
                const kategoriInput = document.getElementById('kategori_edit_<?= $item['id'] ?>');
                const namaMenuSelect = document.getElementById('nama_menu_edit_<?= $item['id'] ?>');
                const komposisiContainer = document.getElementById('komposisi-edit-container-<?= $item['id'] ?>');

                const kode = kodeInput.value.toUpperCase();
                const prefix = kode.substring(0, 3);
                const selectedValue = namaMenuSelect.getAttribute('data-selected');
                const komposisiData = <?= json_encode($item['komposisi']) ?>;

                // Reset komposisi field
                komposisiContainer.innerHTML = '';

                try {
                    const parsed = JSON.parse(komposisiData);
                    for (const [key, val] of Object.entries(parsed)) {
                        const group = document.createElement('div');
                        group.className = 'form-group col-md-6';

                        const label = document.createElement('label');
                        label.textContent = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                        const input = document.createElement('input');
                        input.type = 'text';
                        input.name = `komposisi[${key}]`;
                        input.value = val;
                        input.className = 'form-control';
                        input.required = true;

                        group.appendChild(label);
                        group.appendChild(input);
                        komposisiContainer.appendChild(group);
                    }
                } catch (e) {
                    komposisiContainer.innerHTML = '<div class="col-12 text-muted">Data komposisi tidak valid</div>';
                }

                // Kategori & nama_menu AJAX
                if (prefix.length === 3) {
                    fetch(`<?= base_url('manajemen-penjualan/getVarianByKode/') ?>${prefix}`)
                        .then(response => response.json())
                        .then(data => {
                            namaMenuSelect.innerHTML = '<option value="">-- Pilih Nama Menu --</option>';

                            if (data && data.length > 0) {
                                kategoriInput.value = data[0].kategori;

                                data.forEach(item => {
                                    const option = document.createElement('option');
                                    option.value = item.nama_menu;
                                    option.textContent = item.nama_menu;

                                    if (item.nama_menu === selectedValue) {
                                        option.selected = true;
                                    }

                                    namaMenuSelect.appendChild(option);
                                });
                            } else {
                                kategoriInput.value = '';
                            }
                        })
                        .catch(() => {
                            kategoriInput.value = '';
                            namaMenuSelect.innerHTML = '<option value="">-- Pilih Nama Menu --</option>';
                        });
                } else {
                    kategoriInput.value = '';
                    namaMenuSelect.innerHTML = '<option value="">-- Pilih Nama Menu --</option>';
                }
            });
        <?php endforeach; ?>
    });
</script>


<script>
    document.getElementById('kode_menu').addEventListener('input', function() {
        const kode = this.value.toUpperCase();
        const prefix = kode.substring(0, 3);

        const kategoriInput = document.getElementById('kategori');
        const namaMenuSelect = document.getElementById('nama_menu');

        if (prefix.length === 3) {
            fetch(`<?= base_url('manajemen-penjualan/getVarianByKode/') ?>${prefix}`)
                .then(response => response.json())
                .then(data => {
                    namaMenuSelect.innerHTML = '<option value="">-- Pilih Nama Menu --</option>';

                    if (data && data.length > 0) {
                        const kategori = capitalizeFirst(data[0].kategori);
                        kategoriInput.value = kategori;

                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.nama_menu;
                            option.textContent = item.nama_menu;
                            namaMenuSelect.appendChild(option);
                        });

                        tampilkanInputKomposisi(kategori);
                    } else {
                        kategoriInput.value = '';
                        tampilkanInputKomposisi('');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    kategoriInput.value = '';
                    namaMenuSelect.innerHTML = '<option value="">-- Pilih Nama Menu --</option>';
                    tampilkanInputKomposisi('');
                });
        } else {
            kategoriInput.value = '';
            namaMenuSelect.innerHTML = '<option value="">-- Pilih Nama Menu --</option>';
            tampilkanInputKomposisi('');
        }
    });

    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }

    function tampilkanInputKomposisi(kategori) {
        const komposisi = {
            'Phenomenal': [
                ['kulit', 'pcs'],
                ['daging', 'gr'],
                ['telur', 'butir'],
                ['mayo', 'gr'],
                ['saus_signature', 'gr'],
                ['red_chedar', 'slice'],
                ['aluminium_foil', 'cm'],
                ['stiker', 'pcs'],
                ['saus_sambal_sachet', 'sachet'],
                ['plastik_kresek', 'pcs']
            ],
            'Original': [
                ['kulit', 'pcs'],
                ['daging', 'gr'],
                ['selada', 'gr'],
                ['mayo', 'gr'],
                ['saus_signature', 'gr'],
                ['aluminium_foil', 'cm'],
                ['stiker', 'pcs'],
                ['saus_sambal_sachet', 'sachet'],
                ['plastik_kresek', 'pcs']
            ],
            'Mentai': [
                ['kulit', 'pcs'],
                ['daging', 'gr'],
                ['telur', 'butir'],
                ['saus_mentai', 'gr'],
                ['aluminium_foil', 'cm'],
                ['stiker', 'pcs'],
                ['saus_sambal_sachet', 'sachet'],
                ['plastik_kresek', 'pcs']
            ],
            'Meatlovers': [
                ['kulit', 'pcs'],
                ['daging', 'gr'],
                ['sosis', 'potong'],
                ['smoke_beef', 'potong'],
                ['mayo', 'gr'],
                ['saus_demiglace', 'gr'],
                ['aluminium_foil', 'cm'],
                ['stiker', 'pcs'],
                ['saus_sambal_sachet', 'sachet'],
                ['plastik_kresek', 'pcs']
            ],
            'Curry': [
                ['kulit', 'pcs'],
                ['daging', 'gr'],
                ['telur', 'butir'],
                ['saus_curry', 'gr'],
                ['aluminium_foil', 'cm'],
                ['stiker', 'pcs'],
                ['saus_sambal_sachet', 'sachet'],
                ['plastik_kresek', 'pcs']
            ]
        };

        const labelMap = {
            kulit: 'Kulit',
            daging: 'Daging',
            telur: 'Telur',
            mayo: 'Mayo',
            saus_signature: 'Saus Signature',
            red_chedar: 'Red Chedar',
            aluminium_foil: 'Aluminium Foil',
            stiker: 'Stiker',
            saus_sambal_sachet: 'Saus Sambal Sachet',
            plastik_kresek: 'Plastik Kresek',
            selada: 'Selada',
            saus_mentai: 'Saus Mentai',
            sosis: 'Sosis',
            smoke_beef: 'Smoke Beef',
            saus_demiglace: 'Saus Demiglace',
            saus_curry: 'Saus Curry'
        };

        const container = document.getElementById('komposisi-fields');
        const wrapper = document.getElementById('komposisi-container');
        container.innerHTML = '';

        if (komposisi[kategori]) {
            komposisi[kategori].forEach(([field, satuan]) => {
                const col = document.createElement('div');
                col.className = 'form-group col-md-6';

                const label = document.createElement('label');
                label.textContent = labelMap[field] || field;

                const inputGroup = document.createElement('div');
                inputGroup.className = 'input-group';

                const jumlahInput = document.createElement('input');
                jumlahInput.type = 'text';
                jumlahInput.name = `komposisi[${field}][jumlah]`;
                jumlahInput.className = 'form-control';
                jumlahInput.placeholder = `Jumlah ${label.textContent}`;
                jumlahInput.required = true;

                const satuanInput = document.createElement('input');
                satuanInput.type = 'text';
                satuanInput.name = `komposisi[${field}][satuan]`;
                satuanInput.className = 'form-control';
                satuanInput.placeholder = `Satuan`;
                satuanInput.value = satuan;
                satuanInput.required = true;

                inputGroup.appendChild(jumlahInput);
                inputGroup.appendChild(satuanInput);
                col.appendChild(label);
                col.appendChild(inputGroup);
                container.appendChild(col);
            });

            wrapper.style.display = 'block';
        } else {
            wrapper.style.display = 'none';
        }
    }
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        <?php if (session()->getFlashdata('show_modal') == 'tambah_menu'): ?>
            $('#modalTambahMenu').modal('show');
        <?php endif; ?>
    });
</script>

<?php $this->endSection(); ?>