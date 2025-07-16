<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <div class="container mt-4 text-center">
        <h2 class="mb-0">Sistem Informasi Laporan Keuangan</h2>
        <h2 class="mb-0">Origins Kebab</h2> <br>
        <h3 class="mb-4">Laporan Neraca</h3> <br>
    </div>
    <form method="get" class="form-inline mb-3">
        <select name="filter" class="form-control mr-2" id="filterSelect" onchange="toggleFilterOptions()">
            <option value="bulan" <?= ($filter == 'bulan') ? 'selected' : '' ?>>Per Bulan</option>
            <option value="triwulan" <?= ($filter == 'triwulan') ? 'selected' : '' ?>>Per Triwulan</option>
            <option value="semester" <?= ($filter == 'semester') ? 'selected' : '' ?>>Per Semester</option>
            <option value="tahun" <?= ($filter == 'tahun') ? 'selected' : '' ?>>Per Tahun</option>
            <option value="rentang" <?= ($filter == 'rentang') ? 'selected' : '' ?>>Per Rentang Tanggal</option>
        </select>

        <!-- Input dinamis sesuai filter -->
        <select name="bulan" class="form-control mr-2" id="filterBulan">
            <?php for ($b = 1; $b <= 12; $b++): ?>
                <option value="<?= $b ?>" <?= ($b == $bulan) ? 'selected' : '' ?>>
                    <?= date('F', mktime(0, 0, 0, $b, 10)) ?>
                </option>
            <?php endfor; ?>
        </select>

        <select name="triwulan" class="form-control mr-2" id="filterTriwulan" style="display: none;">
            <?php for ($t = 1; $t <= 4; $t++): ?>
                <option value="<?= $t ?>" <?= ($t == $triwulan) ? 'selected' : '' ?>>Triwulan <?= $t ?></option>
            <?php endfor; ?>
        </select>

        <select name="semester" class="form-control mr-2" id="filterSemester" style="display: none;">
            <option value="1" <?= ($semester == 1) ? 'selected' : '' ?>>Semester 1</option>
            <option value="2" <?= ($semester == 2) ? 'selected' : '' ?>>Semester 2</option>
        </select>

        <input type="date" name="start_date" class="form-control mr-2" id="filterStart" style="display: none;" value="<?= esc($start_date ?? '') ?>">
        <input type="date" name="end_date" class="form-control mr-2" id="filterEnd" style="display: none;" value="<?= esc($end_date ?? '') ?>">

        <input type="number" name="tahun" class="form-control mr-2" value="<?= $tahun ?>">
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    <script>
        function toggleFilterOptions() {
            const filter = document.getElementById('filterSelect').value;
            document.getElementById('filterBulan').style.display = (filter === 'bulan') ? 'inline-block' : 'none';
            document.getElementById('filterTriwulan').style.display = (filter === 'triwulan') ? 'inline-block' : 'none';
            document.getElementById('filterSemester').style.display = (filter === 'semester') ? 'inline-block' : 'none';
            document.getElementById('filterStart').style.display = (filter === 'rentang') ? 'inline-block' : 'none';
            document.getElementById('filterEnd').style.display = (filter === 'rentang') ? 'inline-block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', toggleFilterOptions);
    </script>


    <div class="row">
        <!-- Aset -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-dark text-white font-weight-bold">
                    Aset
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <?php $totalAset = 0; ?>
                        <?php if (empty($aset)): ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted">Tidak ada data aset</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($aset as $a): ?>
                            <tr>
                                <td><?= esc($a['keterangan']) ?></td>
                                <td class="text-right text-success">
                                    Rp <?= number_format($a['jumlah'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php $totalAset += $a['jumlah']; ?>
                        <?php endforeach; ?>
                        <tr class="font-weight-bold bg-light">
                            <td>Total Aset</td>
                            <td class="text-right">Rp <?= number_format($totalAset, 0, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kewajiban & Ekuitas -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-dark text-white font-weight-bold">
                    Kewajiban & Ekuitas
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <?php
                        $totalKewajiban = 0;
                        $totalEkuitas = 0;
                        ?>
                        <!-- Kewajiban -->
                        <?php foreach ($kewajiban as $k): ?>
                            <tr>
                                <td><?= esc($k['keterangan']) ?></td>
                                <td class="text-right text-danger">
                                    Rp <?= number_format($k['jumlah'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php $totalKewajiban += $k['jumlah']; ?>
                        <?php endforeach; ?>

                        <!-- Ekuitas -->
                        <?php foreach ($ekuitas as $e): ?>
                            <tr>
                                <td><?= esc($e['keterangan']) ?></td>
                                <td class="text-right text-primary">
                                    Rp <?= number_format($e['jumlah'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php $totalEkuitas += $e['jumlah']; ?>
                        <?php endforeach; ?>

                        <tr class="font-weight-bold bg-light">
                            <td>Total Kewajiban & Ekuitas</td>
                            <td class="text-right">Rp <?= number_format($totalKewajiban + $totalEkuitas, 0, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Export PDF -->
    <a href="<?= base_url('keuangan/exportNeracaPDF') ?>?<?= http_build_query([
                                                                'filter'     => $filter,
                                                                'bulan'      => $bulan,
                                                                'tahun'      => $tahun,
                                                                'triwulan'   => $triwulan,
                                                                'semester'   => $semester,
                                                                'tanggal_awal' => $start_date,
                                                                'tanggal_akhir' => $end_date
                                                            ]) ?>" class="btn btn-danger">Export PDF</a>

</div>

<?= $this->endSection(); ?>