<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>
<div class="container mt-4">
    <a href="<?= base_url('produksi/persediaan'); ?>" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Kembali</a>
    <h4 class="mb-3"><?= esc($tittle); ?></h4>
    <form method="get" action="<?= base_url('produksi/persediaan/kartu/bahan'); ?>" class="row g-3 mb-4">
        <div class="col-md-5">
            <label for="bahan_id">Pilih Bahan</label>
            <select class="form-control" id="bahan_id" name="bahan_id" required>
                <option value="">-- Pilih Bahan --</option>
                <?php if (!empty($bahan)) : foreach ($bahan as $b) : ?>
                        <option value="<?= esc($b['id']); ?>" <?= (isset($bahanId) && $bahanId == $b['id']) ? 'selected' : '' ?>><?= esc($b['nama']); ?> (<?= esc($b['kode']); ?>)</option>
                <?php endforeach;
                endif; ?>
            </select>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-success">Tampilkan</button>
        </div>
    </form>
    <?php if (isset($kartu) && !empty($kartu)) : ?>
        <div class="table-responsive mt-4">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2">Tanggal</th>
                        <th rowspan="2">Keterangan</th>
                        <th colspan="3" class="text-center">Masuk</th>
                        <th colspan="3" class="text-center">Keluar</th>
                        <th colspan="3" class="text-center">Saldo</th>
                    </tr>
                    <tr>
                        <th>qty</th>
                        <th>Harga</th>
                        <th>Saldo</th>
                        <th>qty</th>
                        <th>Harga</th>
                        <th>Saldo</th>
                        <th>qty</th>
                        <th>Harga</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $saldo_qty = 0;
                    $saldo_harga = 0;
                    $qty_rata2 = 0;
                    $harga_rata2 = 0;
                    foreach ($kartu as $row) :
                        $satuan = $row['satuan'] ?? '';
                        $masuk_qty_raw = ($row['jenis'] == 'masuk') ? ($row['jumlah'] ?? 0) : 0;
                        $keluar_qty_raw = ($row['jenis'] == 'keluar') ? ($row['jumlah'] ?? 0) : 0;
                        if (in_array(strtolower($satuan), ['kg', 'liter'])) {
                            $masuk_qty = $masuk_qty_raw / 1000;
                            $keluar_qty = $keluar_qty_raw / 1000;
                        } else {
                            $masuk_qty = $masuk_qty_raw;
                            $keluar_qty = $keluar_qty_raw;
                        }
                        if ($row['jenis'] == 'masuk') {
                            $qty_rata2 += $masuk_qty;
                            $harga_rata2 += $masuk_qty * ($row['harga_satuan'] ?? 0);
                            $harga_satuan_masuk = ($row['harga_satuan'] ?? 0);
                            $harga_satuan_keluar = 0;
                            $saldo_qty += $masuk_qty;
                            $saldo_harga += $masuk_qty * $harga_satuan_masuk;
                        } else {
                            $harga_satuan_masuk = 0;
                            $harga_satuan_keluar = ($qty_rata2 > 0) ? ($harga_rata2 / $qty_rata2) : 0;
                            $saldo_qty -= $keluar_qty;
                            $saldo_harga -= $keluar_qty * $harga_satuan_keluar;
                        }
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= esc($row['tanggal'] ?? '-'); ?></td>
                            <td><?= esc($row['keterangan'] ?? '-'); ?></td>
                            <td><?= number_format($masuk_qty, 2, ',', '.'); ?> <?= $masuk_qty > 0 ? strtoupper($satuan) : ''; ?></td>
                            <td><?= number_format($harga_satuan_masuk, 0, ',', '.'); ?></td>
                            <td><?= number_format($masuk_qty * $harga_satuan_masuk, 0, ',', '.'); ?></td>
                            <td><?= number_format($keluar_qty, 2, ',', '.'); ?> <?= $keluar_qty > 0 ? strtoupper($satuan) : ''; ?></td>
                            <td><?= number_format($harga_satuan_keluar, 0, ',', '.'); ?></td>
                            <td><?= number_format($keluar_qty * $harga_satuan_keluar, 0, ',', '.'); ?></td>
                            <td><?= number_format($saldo_qty, 2, ',', '.'); ?> <?= $saldo_qty > 0 ? strtoupper($satuan) : ''; ?></td>
                            <td>
                                <?php
                                $harga_saldo = ($saldo_qty > 0) ? ($saldo_harga / $saldo_qty) : 0;
                                echo number_format($harga_saldo, 0, ',', '.');
                                ?>
                            </td>
                            <td><?= number_format($saldo_harga, 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection(); ?>