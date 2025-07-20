<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            font-size: 14px;
        }

        h2,
        h4 {
            text-align: center;
            margin: 0;
        }

        h4 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #eee;
        }

        .right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            background-color: #e0f7da;
        }

        .summary {
            margin-top: 30px;
            width: 50%;
            float: right;
        }

        .summary td {
            border: 1px solid #444;
            padding: 6px 10px;
        }

        .footer {
            clear: both;
            margin-top: 60px;
            text-align: right;
            font-size: 12px;
        }

        @media print {
            .footer {
                position: fixed;
                bottom: 10px;
                right: 40px;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <h2>Laporan Penjualan</h2>
    <h4>
        Tanggal:
        <?= ($tanggal_awal === $tanggal_akhir)
            ? esc(date('d-m-Y', strtotime($tanggal_awal)))
            : esc(date('d-m-Y', strtotime($tanggal_awal))) . ' s/d ' . esc(date('d-m-Y', strtotime($tanggal_akhir))) ?>
        <?= isset($nama_outlet) && $nama_outlet ? " | Outlet: " . esc($nama_outlet) : '' ?>
    </h4>

    <table>
        <thead>
            <tr>
                <th>Outlet</th>
                <th>Shift</th>
                <th>Jam</th>
                <th>Total Penjualan</th>
                <th>Total Pengeluaran</th>
                <th>Rincian Pengeluaran</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalPenjualanAll = 0;
            $totalPengeluaranAll = 0;
            foreach ($laporan as $row):
                $totalPenjualanAll += $row['total_penjualan'];
                $totalPengeluaranAll += $row['total_pengeluaran'];
            ?>
                <tr>
                    <td><?= esc($row['nama_outlet']) ?></td>
                    <td><?= esc($row['nama_shift']) ?></td>
                    <td><?= esc($row['jam_mulai']) ?> - <?= esc($row['jam_selesai']) ?></td>
                    <td class="right">Rp<?= number_format($row['total_penjualan'], 0, ',', '.') ?></td>
                    <td class="right">Rp<?= number_format($row['total_pengeluaran'], 0, ',', '.') ?></td>
                    <td><?= esc($row['keterangan_pengeluaran']) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="3">Total Keseluruhan</td>
                <td class="right">Rp<?= number_format($totalPenjualanAll, 0, ',', '.') ?></td>
                <td class="right">Rp<?= number_format($totalPengeluaranAll, 0, ',', '.') ?></td>
                <td>-</td>
            </tr>
        </tbody>
    </table>

    <!-- Ringkasan Total -->
    <table class="summary">
        <tr>
            <td><strong>Total Penjualan</strong></td>
            <td class="right">Rp<?= number_format($totalPenjualanAll, 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td><strong>Total Pengeluaran</strong></td>
            <td class="right">Rp<?= number_format($totalPengeluaranAll, 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td><strong>Keuntungan Kotor</strong></td>
            <td class="right">
                <span style="color: <?= ($totalPenjualanAll - $totalPengeluaranAll) >= 0 ? 'green' : 'red' ?>;">
                    Rp<?= number_format($totalPenjualanAll - $totalPengeluaranAll, 0, ',', '.') ?>
                </span>
            </td>
        </tr>
    </table>
    <div class="footer">
        Dicetak pada: <?= date('d-m-Y H:i') ?>
    </div>

</body>

</html>