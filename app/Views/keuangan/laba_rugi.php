<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content') ?>

<div class="container mt-4">
    <h4 class="mb-4"><?= esc($tittle ?? 'Laporan Laba Rugi') ?></h4>
    <form method="get" class="form-inline mb-3">
        <select name="filter" class="form-control mr-2" id="filter-select">
            <option value="rentang" <?= $filter == 'rentang' ? 'selected' : '' ?>>Per Rentang Tanggal</option>
            <option value="bulan" <?= $filter == 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
            <option value="tanggal" <?= $filter == 'tanggal' ? 'selected' : '' ?>>Per Tanggal</option>
            <option value="triwulan" <?= $filter == 'triwulan' ? 'selected' : '' ?>>Per Triwulan</option>
            <option value="semester" <?= $filter == 'semester' ? 'selected' : '' ?>>Per Semester</option>
            <option value="tahun" <?= $filter == 'tahun' ? 'selected' : '' ?>>Per Tahun</option>
        </select>

        <!-- Rentang Tanggal -->
        <div id="rentang-filter" class="form-inline d-none">
            <label class="mr-2">Dari</label>
            <input type="date" name="start_date" class="form-control mr-2" value="<?= $start_date ?? '' ?>">
            <label class="mr-2">Sampai</label>
            <input type="date" name="end_date" class="form-control mr-2" value="<?= $end_date ?? '' ?>">
        </div>

        <div id="bulan-filter" class="form-inline">
            <select name="bulan" class="form-control mr-2">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>" <?= $i == $bulan ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $i, 1)) ?></option>
                <?php endfor; ?>
            </select>
            <select name="tahun" class="form-control mr-2">
                <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div id="tanggal-filter" class="form-inline d-none">
            <input type="date" name="tanggal" class="form-control mr-2" value="<?= $tanggal ?? '' ?>">
        </div>

        <div id="triwulan-filter" class="form-inline d-none">
            <select name="triwulan" class="form-control mr-2">
                <option value="1" <?= $triwulan == 1 ? 'selected' : '' ?>>Triwulan 1 (Jan-Mar)</option>
                <option value="2" <?= $triwulan == 2 ? 'selected' : '' ?>>Triwulan 2 (Apr-Jun)</option>
                <option value="3" <?= $triwulan == 3 ? 'selected' : '' ?>>Triwulan 3 (Jul-Sep)</option>
                <option value="4" <?= $triwulan == 4 ? 'selected' : '' ?>>Triwulan 4 (Okt-Des)</option>
            </select>
            <select name="tahun" class="form-control mr-2">
                <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div id="semester-filter" class="form-inline d-none">
            <select name="semester" class="form-control mr-2">
                <option value="1" <?= $semester == 1 ? 'selected' : '' ?>>Semester 1 (Jan-Jun)</option>
                <option value="2" <?= $semester == 2 ? 'selected' : '' ?>>Semester 2 (Jul-Des)</option>
            </select>
            <select name="tahun" class="form-control mr-2">
                <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div id="tahun-filter" class="form-inline d-none">
            <select name="tahun" class="form-control mr-2">
                <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('filter-select');
            const allFilters = ['bulan', 'tanggal', 'triwulan', 'semester', 'tahun', 'rentang'];

            function toggleFilter() {
                allFilters.forEach(f => {
                    document.getElementById(f + '-filter').classList.add('d-none');
                });
                const selected = select.value;
                document.getElementById(selected + '-filter').classList.remove('d-none');
            }

            select.addEventListener('change', toggleFilter);
            toggleFilter();
        });
    </script>

    <!-- Pendapatan -->
    <h5>Pendapatan</h5>
    <table class="table table-bordered">
        <tbody>
            <?php $totalPendapatan = 0; ?>
            <?php if (!empty($Pendapatan)): ?>
                <?php foreach ($Pendapatan as $p): ?>
                    <tr>
                        <td><?= esc($p['nama_akun']) ?></td>
                        <td class="text-right"><?= number_format($p['jumlah'], 2, ',', '.') ?></td>
                    </tr>
                    <?php $totalPendapatan += $p['jumlah']; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="text-center">Tidak ada data pendapatan.</td>
                </tr>
            <?php endif; ?>
            <tr>
                <th>Total Pendapatan</th>
                <th class="text-right"><?= number_format($totalPendapatan, 2, ',', '.') ?></th>
            </tr>
        </tbody>
    </table>

    <!-- Beban -->
    <h5>Beban</h5>
    <table class="table table-bordered">
        <tbody>
            <?php $totalBeban = 0; ?>
            <?php if (!empty($Beban)): ?>
                <?php foreach ($Beban as $b): ?>
                    <tr>
                        <td><?= esc($b['nama_akun']) ?></td>
                        <td class="text-right"><?= number_format($b['jumlah'], 2, ',', '.') ?></td>
                    </tr>
                    <?php $totalBeban += $b['jumlah']; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="text-center">Tidak ada data beban.</td>
                </tr>
            <?php endif; ?>
            <tr>
                <th>Total Beban</th>
                <th class="text-right"><?= number_format($totalBeban, 2, ',', '.') ?></th>
            </tr>
        </tbody>
    </table>

    <!-- Laba Bersih -->
    <h5 class="mt-4">
        <strong>Laba Bersih Bulan Ini: <?= number_format($totalPendapatan - $totalBeban, 2, ',', '.') ?></strong>
    </h5>
    <?php
    $query = http_build_query([
        'filter' => $filter,
        'bulan' => $bulan,
        'tahun' => $tahun,
        'tanggal' => $tanggal,
        'triwulan' => $triwulan,
        'semester' => $semester,
        'start_date' => $start_date ?? '',
        'end_date' => $end_date ?? '',
    ]);

    ?>
    <a href="<?= base_url('keuangan/laba_rugi/pdf?' . http_build_query([
                    'filter' => $filter,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'tanggal' => $tanggal,
                    'triwulan' => $triwulan,
                    'semester' => $semester,
                    'start_date' => $start_date ?? '',
                    'end_date' => $end_date ?? '',
                ])) ?>" class="btn btn-danger">Export PDF</a>


</div>

<?= $this->endSection() ?>