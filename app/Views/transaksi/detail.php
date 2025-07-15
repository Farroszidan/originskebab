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
                <p><strong>Tanggal:</strong> <?= esc($data['tanggal']) ?></p>
                <p><strong>Catatan:</strong> <?= esc($data['catatan']) ?></p>

                <?php if (!empty($detail)): ?>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal Input</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detail as $i => $item): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= esc($item['nama'] ?? '-') ?></td>
                                        <td><?= esc($item['jumlah'] ?? '0') . ' ' . esc($item['satuan'] ?? '') ?></td>
                                        <td><?= isset($item['created_at']) ? date('d-m-Y H:i', strtotime($item['created_at'])) : '-' ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mt-3">Tidak ada barang yang diminta.</div>
                <?php endif; ?>

            <?php elseif ($jenis === 'pengiriman'): ?>
                <p><strong>Tanggal:</strong> <?= esc($data['tanggal']) ?></p>
                <p><strong>Jumlah:</strong> <?= esc($data['jumlah']) ?></p>
                <p><strong>Catatan:</strong> <?= esc($data['catatan']) ?></p>

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

            <?php else: ?>
                <div class="alert alert-danger">Data tidak tersedia.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>