<?php $this->extend('templates/index_templates_general'); ?>
<?php $this->section('page-content'); ?>
<div class="container mt-4">
    <a href="<?= base_url('produksi/persediaan/bsj'); ?>" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Kembali</a>
    <h4 class="mb-3"><?= esc($tittle); ?></h4>

    <form method="get" action="<?= base_url('produksi/persediaan/kartu/bsj'); ?>" class="row g-3 mb-4">
        <div class="col-md-5">
            <label for="bsj_id">Pilih BSJ</label>
            <select class="form-control" id="bsj_id" name="bsj_id" required>
                <option value="">-- Pilih BSJ --</option>
                <?php foreach ($bsj as $b) : ?>
                    <option value="<?= esc($b['id']); ?>" <?= (isset($bsjId) && $bsjId == $b['id']) ? 'selected' : '' ?>>
                        <?= esc($b['nama']); ?> (<?= esc($b['kode']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="tanggal_mulai">Tanggal Mulai</label>
            <input type="date" class="form-control" name="tanggal_mulai" value="<?= esc($tanggalMulai ?? '') ?>" required>
        </div>
        <div class="col-md-3">
            <label for="tanggal_selesai">Tanggal Selesai</label>
            <input type="date" class="form-control" name="tanggal_selesai" value="<?= esc($tanggalSelesai ?? '') ?>" required>
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
                        <th colspan="3">Masuk</th>
                        <th colspan="3">Keluar</th>
                        <th colspan="3">Saldo</th>
                    </tr>
                    <tr>
                        <th>qty</th>
                        <th>Harga</th>
                        <th>Total</th>
                        <th>qty</th>
                        <th>Harga</th>
                        <th>Total</th>
                        <th>qty</th>
                        <th>Harga</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $saldo_qty = 0;
                    $saldo_harga = 0;
                    foreach ($kartu as $row) :
                        $masuk_qty = $row['jenis'] == 'masuk' ? $row['masuk_qty'] : 0;
                        $keluar_qty = $row['jenis'] == 'keluar' ? $row['keluar_qty'] : 0;
                        $harga = $row['harga_satuan'] ?? 0;
                        if ($row['jenis'] == 'masuk') {
                            $saldo_qty += $masuk_qty;
                            $saldo_harga += $masuk_qty * $harga;
                        } else {
                            $saldo_qty -= $keluar_qty;
                            $saldo_harga -= $keluar_qty * $harga;
                        }
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= esc($row['tanggal']); ?></td>
                            <td><?= esc($row['keterangan']); ?></td>
                            <td><?= number_format($masuk_qty); ?></td>
                            <td><?= number_format($row['harga_satuan'], 0, ',', '.'); ?></td>
                            <td><?= number_format($masuk_qty * $row['harga_satuan'], 0, ',', '.'); ?></td>
                            <td><?= number_format($keluar_qty); ?></td>
                            <td><?= number_format($row['harga_satuan'], 0, ',', '.'); ?></td>
                            <td><?= number_format($keluar_qty * $row['harga_satuan'], 0, ',', '.'); ?></td>
                            <td><?= number_format($saldo_qty); ?></td>
                            <td><?= number_format($row['harga_satuan'], 0, ',', '.'); ?></td>
                            <td><?= number_format($saldo_harga, 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection(); ?>