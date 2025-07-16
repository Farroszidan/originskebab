<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container mt-4">
    <div class="container mt-4 text-center">
        <h2 class="mb-0">Sistem Informasi Laporan Keuangan</h2>
        <h2 class="mb-0">Origins Kebab</h2> <br>
        <h3 class="mb-4">Laporan Arus Kas</h3> <br>
    </div>
    <form method="get" class="mb-4">
        <div class="form-row align-items-end">
            <div class="col-md-2">
                <label>Filter</label>
                <select name="filter" id="filterSelect" class="form-control">
                    <option value="bulan" <?= $filter === 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
                    <option value="triwulan" <?= $filter === 'triwulan' ? 'selected' : '' ?>>Per Triwulan</option>
                    <option value="semester" <?= $filter === 'semester' ? 'selected' : '' ?>>Per Semester</option>
                    <option value="tahun" <?= $filter === 'tahun' ? 'selected' : '' ?>>Per Tahun</option>
                    <option value="rentang" <?= $filter === 'rentang' ? 'selected' : '' ?>>Rentang Tanggal</option>
                </select>
            </div>

            <!-- Bulan -->
            <div class="col-md-2 filter-input filter-bulan" style="display:none">
                <label>Bulan</label>
                <select name="bulan" class="form-control">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $bulan ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $i, 1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Triwulan -->
            <div class="col-md-2 filter-input filter-triwulan" style="display:none">
                <label>Triwulan</label>
                <select name="triwulan" class="form-control">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $triwulan ? 'selected' : '' ?>>Triwulan <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Semester -->
            <div class="col-md-2 filter-input filter-semester" style="display:none">
                <label>Semester</label>
                <select name="semester" class="form-control">
                    <option value="1" <?= $semester == 1 ? 'selected' : '' ?>>Semester 1</option>
                    <option value="2" <?= $semester == 2 ? 'selected' : '' ?>>Semester 2</option>
                </select>
            </div>

            <!-- Tahun -->
            <div class="col-md-2 filter-input filter-bulan filter-triwulan filter-semester filter-tahun" style="display:none">
                <label>Tahun</label>
                <select name="tahun" class="form-control">
                    <?php for ($y = date('Y') - 5; $y <= date('Y') + 1; $y++): ?>
                        <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Rentang Tanggal -->
            <div class="col-md-3 filter-input filter-rentang" style="display:none">
                <label>Tanggal Awal</label>
                <input type="date" name="start_date" class="form-control" value="<?= esc($start_date) ?>">
            </div>
            <div class="col-md-3 filter-input filter-rentang" style="display:none">
                <label>Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control" value="<?= esc($end_date) ?>">
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-block">Tampilkan</button>
            </div>
        </div>
    </form>

    <script>
        // Tampilkan input sesuai filter
        function toggleFilterInputs() {
            const selected = document.getElementById('filterSelect').value;
            const inputs = document.querySelectorAll('.filter-input');
            inputs.forEach(el => el.style.display = 'none');

            document.querySelectorAll('.filter-' + selected).forEach(el => {
                el.style.display = 'block';
            });
        }

        document.addEventListener('DOMContentLoaded', toggleFilterInputs);
        document.getElementById('filterSelect').addEventListener('change', toggleFilterInputs);
    </script>

    <h5>Arus Kas dari Aktivitas Operasi</h5>
    <table class="table table-bordered">
        <tbody>
            <?php foreach ($arusKas['operasi'] as $item): ?>
                <tr>
                    <td><?= esc($item['akun']) ?></td>
                    <td class="text-right"><?= number_format($item['jumlah'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h5>Arus Kas dari Aktivitas Investasi</h5>
    <table class="table table-bordered">
        <tbody>
            <?php foreach ($arusKas['investasi'] as $item): ?>
                <tr>
                    <td><?= esc($item['akun']) ?></td>
                    <td class="text-right"><?= number_format($item['jumlah'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h5>Arus Kas dari Aktivitas Pendanaan</h5>
    <table class="table table-bordered">
        <tbody>
            <?php foreach ($arusKas['pendanaan'] as $item): ?>
                <tr>
                    <td><?= esc($item['akun']) ?></td>
                    <td class="text-right"><?= number_format($item['jumlah'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h4 class="mt-4">Kenaikan/Penurunan Kas: <strong>Rp <?= number_format($arusKas['total'], 2, ',', '.') ?></strong></h4>

    <a href="<?= base_url('keuangan/arus_kas_pdf?' . http_build_query([
                    'filter' => $filter,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'triwulan' => $triwulan,
                    'semester' => $semester,
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ])) ?>" class="btn btn-danger mt-3">Export PDF</a>


</div>

<?= $this->endSection(); ?>