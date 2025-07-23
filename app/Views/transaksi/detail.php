<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container mt-4">
    <a href="<?= base_url('notifikasi/pesan_masuk') ?>" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Notifikasi
    </a>

    <h4 class="mb-4">
        <i class="fas fa-info-circle mr-2 text-primary"></i>
        Detail <?= ucwords(str_replace('_', ' ', $jenis)) ?>
    </h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($jenis === 'permintaan'): ?>
                <p><strong>Tanggal:</strong> <?= esc(date('d-m-Y', strtotime($data['tanggal']))) ?></p>
                <p><strong>Catatan:</strong> <?= esc($data['catatan']) ?></p>

                <?php if (!empty($detail)): ?>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detail as $i => $item): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= esc($item['nama'] ?? '-') ?></td>
                                        <td><?= esc($item['jumlah'] ?? '0') . ' ' . esc($item['satuan'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mt-3">Tidak ada barang yang diminta.</div>
                <?php endif; ?>

            <?php elseif ($jenis === 'pengiriman'): ?>
                <h6 class="text-warning">Detail Pengiriman</h6>
                <p><strong>Tanggal:</strong> <?= date('d-m-Y', strtotime($data['tanggal'])) ?></p>

                <p><strong>Catatan:</strong> <?= $data['catatan'] ?></p>

                <?php if (!empty($detail)): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detail as $item): ?>
                                <tr>
                                    <td><?= $item['nama_barang'] ?></td>
                                    <td><?= $item['jumlah'] ?></td>
                                    <td><?= $item['satuan'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-danger">Tidak ada detail pengiriman.</p>
                <?php endif; ?>

            <?php elseif ($jenis === 'bukti_pembelian'): ?>
                <p><strong>Tanggal:</strong> <?= esc($data['tanggal']) ?></p>
                <p><strong>Keterangan:</strong> <?= esc($data['keterangan']) ?></p>
                <p><strong>Nama Penginput:</strong> <?= esc($data['nama']) ?></p>
                <p><strong>Outlet:</strong> <?= outlet_nama($data['outlet_id']) ?></p>

                <?php
                $detailBarang = json_decode($data['detail'], true);
                $grandTotal = 0;
                ?>

                <?php if (!empty($detailBarang)): ?>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detailBarang as $i => $item):
                                    $subtotal = $item['jumlah'] * $item['harga'];
                                    $grandTotal += $subtotal;
                                ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= esc($item['nama']) ?></td>
                                        <td><?= esc($item['jumlah']) ?></td>
                                        <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-info font-weight-bold">
                                    <td colspan="4" class="text-right">Grand Total</td>
                                    <td>Rp <?= number_format($grandTotal, 0, ',', '.') ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mt-3">Tidak ada detail pembelian.</div>
                <?php endif; ?>

                <?php if (!empty($data['gambar'])): ?>
                    <div class="mt-4">
                        <p><strong>Bukti Pembelian:</strong></p>
                        <img src="<?= base_url('uploads/bukti_pembelian/' . $data['gambar']) ?>"
                            alt="Bukti Pembelian"
                            class="img-thumbnail"
                            style="max-width: 300px;">
                        <br>
                        <a href="<?= base_url('uploads/bukti_pembelian/' . $data['gambar']) ?>"
                            download
                            class="btn btn-outline-primary btn-sm mt-2">
                            <i class="fas fa-download mr-1"></i> Download Gambar
                        </a>
                    </div>
                <?php endif; ?>

            <?php elseif ($jenis === 'perintah_kerja'): ?>
                <p><strong>Tanggal:</strong> <?= esc($data['tanggal']) ?></p>
                <p><strong>Catatan:</strong> <?= esc($data['catatan'] ?? '-') ?></p>

                <div class="card mb-4">
                    <div class="card-header font-weight-bold">Daftar Produksi</div>
                    <div class="card-body">
                        <?php if (!empty($data['daftar_produksi'])): ?>
                            <table class="table table-bordered table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Jumlah</th>
                                        <th>Satuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['daftar_produksi'] as $i => $prod): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= esc($prod['nama'] ?? '-') ?></td>
                                            <td><?= esc($prod['jumlah'] ?? '0') ?></td>
                                            <td><?= esc($prod['satuan'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="alert alert-warning mt-3">Tidak ada data produksi.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header font-weight-bold">Kebutuhan Bahan yang Perlu Dibeli</div>
                    <div class="card-body">
                        <?php
                        $bahanModel = new \App\Models\BahanModel();
                        $kekuranganList = [];
                        if (!empty($data['rangkuman_bahan'])) {
                            foreach ($data['rangkuman_bahan'] as $bahan) {
                                $stok = 0;
                                $bahanDb = $bahanModel->where('nama', $bahan['nama'])->where('satuan', $bahan['satuan'])->first();
                                if ($bahanDb && isset($bahanDb['stok'])) {
                                    $stok = $bahanDb['stok'];
                                    // Jika satuan kg/liter, tampilkan stok dalam satuan kg/liter (bukan gram/ml)
                                    $satuan_lc = strtolower($bahan['satuan']);
                                    if ($satuan_lc === 'kg' || $satuan_lc === 'liter' || $satuan_lc === 'ltr') {
                                        $stok = $stok / 1000;
                                    }
                                }
                                $kurang = $bahan['jumlah'] - $stok;
                                if ($kurang > 0) {
                                    $pembulatan = ceil($kurang);
                                    $kekuranganList[] = [
                                        'nama' => $bahan['nama'],
                                        'kategori' => $bahan['kategori'],
                                        'jumlah' => $bahan['jumlah'],
                                        'satuan' => $bahan['satuan'],
                                        'stok' => $stok,
                                        'kurang' => $kurang,
                                        'pembulatan' => $pembulatan
                                    ];
                                }
                            }
                        }
                        ?>
                        <?php if (!empty($kekuranganList)): ?>
                            <table class="table table-bordered table-hover table-sm">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Nama</th>
                                        <th>Kategori</th>
                                        <th>Jumlah Dibutuhkan</th>
                                        <th>Satuan</th>
                                        <th>Stok Tersedia</th>
                                        <th>Kekurangan</th>
                                        <th>Pembulatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kekuranganList as $row): ?>
                                        <tr>
                                            <td><?= esc($row['nama']) ?></td>
                                            <td><?= esc($row['kategori']) ?></td>
                                            <td><?= esc(number_format($row['jumlah'], 2)) ?></td>
                                            <td><?= esc($row['satuan']) ?></td>
                                            <td><?= esc(number_format($row['stok'], 2)) ?></td>
                                            <td><strong><?= esc(number_format($row['kurang'], 2)) ?></strong></td>
                                            <td><strong><?= esc(number_format($row['pembulatan'], 0)) ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="alert alert-success mt-3">Semua kebutuhan bahan tersedia, tidak ada kekurangan.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">Data tidak tersedia.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>