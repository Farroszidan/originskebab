<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <div class="container mt-4 text-center">
        <h2 class="mb-0">Sistem Informasi Laporan Keuangan</h2>
        <h2 class="mb-0">Origins Kebab</h2> <br>
        <h3 class="mb-4">Laporan Perubahan Ekuitas</h3> <br>
    </div>
    <form method="get" class="form-inline mb-3">
        <select name="filter" class="form-control mr-2" id="filterSelect" onchange="toggleFilterOptions()">
            <option value="rentang" <?= ($filter == 'rentang') ? 'selected' : '' ?>>Per Rentang Tanggal</option>
            <option value="bulan" <?= ($filter == 'bulan') ? 'selected' : '' ?>>Per Bulan</option>
            <option value="triwulan" <?= ($filter == 'triwulan') ? 'selected' : '' ?>>Per Triwulan</option>
            <option value="semester" <?= ($filter == 'semester') ? 'selected' : '' ?>>Per Semester</option>
            <option value="tahun" <?= ($filter == 'tahun') ? 'selected' : '' ?>>Per Tahun</option>
        </select>

        <div id="filterRentang" style="display: none;">
            <label class="mr-2">Dari</label>
            <input type="date" name="start_date" class="form-control mr-2" value="<?= esc($start_date ?? '') ?>">
            <label class="mr-2">Sampai</label>
            <input type="date" name="end_date" class="form-control mr-2" value="<?= esc($end_date ?? '') ?>">
        </div>

        <select name="bulan" class="form-control mr-2" id="filterBulan">
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= ($i == $bulan) ? 'selected' : '' ?>>
                    <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                </option>
            <?php endfor; ?>
        </select>

        <select name="triwulan" class="form-control mr-2" id="filterTriwulan" style="display: none;">
            <?php for ($i = 1; $i <= 4; $i++): ?>
                <option value="<?= $i ?>" <?= ($triwulan == $i) ? 'selected' : '' ?>>Triwulan <?= $i ?></option>
            <?php endfor; ?>
        </select>

        <select name="semester" class="form-control mr-2" id="filterSemester" style="display: none;">
            <option value="1" <?= ($semester == 1) ? 'selected' : '' ?>>Semester 1</option>
            <option value="2" <?= ($semester == 2) ? 'selected' : '' ?>>Semester 2</option>
        </select>

        <select name="tahun" class="form-control mr-2">
            <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                <option value="<?= $y ?>" <?= ($y == $tahun) ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>

        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    <script>
        function toggleFilterOptions() {
            const selected = document.getElementById('filterSelect').value;
            document.getElementById('filterRentang').style.display = selected === 'rentang' ? 'inline-block' : 'none';
            document.getElementById('filterBulan').style.display = selected === 'bulan' ? 'inline-block' : 'none';
            document.getElementById('filterTriwulan').style.display = selected === 'triwulan' ? 'inline-block' : 'none';
            document.getElementById('filterSemester').style.display = selected === 'semester' ? 'inline-block' : 'none';
        }
        window.addEventListener('DOMContentLoaded', toggleFilterOptions);
    </script>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Keterangan</th>
                            <th class="text-right">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ekuitas as $index => $item): ?>
                            <?php if ($item['keterangan'] === 'Total Ekuitas Akhir'): ?>
                                <tr class="font-weight-bold">
                                    <td><?= esc($item['keterangan']) ?></td>
                                    <td class="text-right"><?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td><?= esc($item['keterangan']) ?></td>
                                    <td class="text-right <?= $item['jumlah'] < 0 ? 'text-danger' : 'text-success' ?>">
                                        <?= number_format($item['jumlah'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php
    // Buat query string untuk export PDF berdasarkan filter
    $params = [
        'filter' => $filter,
        'bulan' => $bulan,
        'tahun' => $tahun,
        'triwulan' => $triwulan,
        'semester' => $semester,
        'start_date' => $start_date,
        'end_date' => $end_date,
    ];

    $queryString = http_build_query($params);
    ?>

    <a href="<?= base_url('keuangan/perubahan_ekuitas_pdf?' . http_build_query([
                    'filter' => $filter,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'triwulan' => $triwulan,
                    'semester' => $semester
                ])) ?>" class="btn btn-danger">Export PDF</a>

</div>

<?= $this->endSection(); ?>