<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container">
    <h3 class="mb-4">Neraca Saldo</h3>

    <form class="form-inline mb-3" method="get" action="">
        <label class="mr-2" for="bulan">Bulan</label>
        <select name="bulan" id="bulan" class="form-control mr-2">
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= (isset($bulan) && $bulan == $i) ? 'selected' : ((date('n') == $i && !isset($bulan)) ? 'selected' : '') ?>><?= date('F', mktime(0, 0, 0, $i, 1)) ?></option>
            <?php endfor; ?>
        </select>
        <label class="mr-2" for="tahun">Tahun</label>
        <select name="tahun" id="tahun" class="form-control mr-2">
            <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                <option value="<?= $y ?>" <?= (isset($tahun) && $tahun == $y) ? 'selected' : ((date('Y') == $y && !isset($tahun)) ? 'selected' : '') ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" class="btn btn-primary mr-2">Tampilkan</button>
        <a href="<?= base_url('keuangan/neraca_saldo_pdf?bulan=' . (isset($bulan) ? $bulan : date('n')) . '&tahun=' . (isset($tahun) ? $tahun : date('Y'))) ?>" target="_blank" class="btn btn-danger">Export PDF</a>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th class="text-center">Kode Akun</th>
                <th class="text-center">Nama Akun</th>
                <th class="text-center">Debit (Rp)</th>
                <th class="text-center">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($akun as $a): ?>
                <tr>
                    <td><?= esc($a['kode_akun']) ?></td>
                    <td><?= esc($a['nama_akun']) ?></td>
                    <td class="text-right">
                        <?= $a['tipe'] === 'debit' ? number_format($a['saldo'], 2, ',', '.') : '0,00' ?>
                    </td>
                    <td class="text-right">
                        <?= $a['tipe'] === 'kredit' ? number_format($a['saldo'], 2, ',', '.') : '0,00' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">Total </th>
                <th class="text-right text-success"><?= number_format($total_debet, 2, ',', '.') ?></th>
                <th class="text-right text-danger"><?= number_format($total_kredit, 2, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>
</div>

<?= $this->endSection() ?>