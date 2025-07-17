<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Detail Perintah Kerja Produksi BSJ</h1>
    <?php if (!empty($perintah)): ?>
        <div class="card mb-4">
            <div class="card-header font-weight-bold">Info Perintah Kerja</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>Tanggal</th>
                        <td><?= date('d-m-Y', strtotime($perintah['tanggal'])); ?></td>
                    </tr>
                    <tr>
                        <th>Jumlah Kulit</th>
                        <td><?= esc($perintah['jumlah_kulit'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Jumlah Ayam</th>
                        <td><?= esc($perintah['jumlah_ayam'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Jumlah Sapi</th>
                        <td><?= esc($perintah['jumlah_sapi'] ?? '-'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header font-weight-bold">Daftar Produksi</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>BSJ</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($produksi)) {
                            foreach ($produksi as $d) { ?>
                                <tr>
                                    <td><?= esc($d['jenis_bsj']) ?></td>
                                    <td><?= esc($d['jumlah']) ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="2">Belum ada data.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header font-weight-bold">Kebutuhan Bahan Gabungan</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Bahan</th>
                            <th>Kategori</th>
                            <th>Jumlah Total</th>
                            <th>Satuan</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Gabungkan kebutuhan bahan dari semua produksi
                        $bahanGabungan = [];
                        $totalBiayaBahan = 0;
                        if (!empty($kebutuhan_bahan)) {
                            foreach ($kebutuhan_bahan as $item) {
                                $key = $item['nama'] . '|' . $item['satuan'];
                                if (!isset($bahanGabungan[$key])) {
                                    $bahanGabungan[$key] = [
                                        'nama' => $item['nama'],
                                        'kategori' => $item['kategori'],
                                        'jumlah' => 0,
                                        'satuan' => $item['satuan'],
                                        'harga' => $item['harga'],
                                        'subtotal' => 0
                                    ];
                                }
                                $bahanGabungan[$key]['jumlah'] += $item['jumlah'];
                                $bahanGabungan[$key]['subtotal'] += $item['subtotal'];
                                $totalBiayaBahan += $item['subtotal'];
                            }
                        }
                        if (!empty($bahanGabungan)) {
                            foreach ($bahanGabungan as $b) { ?>
                                <tr>
                                    <td><?= esc($b['nama']) ?></td>
                                    <td><?= esc($b['kategori']) ?></td>
                                    <td><?= number_format($b['jumlah'], 2, ',', '.') ?></td>
                                    <td><?= esc($b['satuan']) ?></td>
                                    <td>Rp <?= number_format($b['harga'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($b['subtotal'], 0, ',', '.') ?></td>
                                </tr>
                            <?php }
                            ?>
                            <tr>
                                <td colspan="5" class="text-right"><b>Total Biaya Bahan</b></td>
                                <td><b>Rp <?= number_format($totalBiayaBahan, 0, ',', '.') ?></b></td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6">Belum ada data kebutuhan bahan.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <a href="<?= base_url('admin/perintah-kerja') ?>" class="btn btn-secondary">Kembali</a>
        <a href="<?= base_url('admin/perintah-kerja/hapus/' . $perintah['id']) ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus perintah kerja ini?');">Hapus Perintah Kerja</a>
    <?php else: ?>
        <div class="alert alert-warning">Data perintah kerja tidak ditemukan.</div>
        <a href="<?= base_url('admin/perintah-kerja') ?>" class="btn btn-secondary">Kembali</a>
    <?php endif; ?>
</div>
<?= $this->endSection(); ?>