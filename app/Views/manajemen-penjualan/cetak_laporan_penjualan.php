<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            font-size: 14px;
            color: #333;
        }

        h2,
        h4 {
            text-align: center;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        table,
        th,
        td {
            border: 1px solid #999;
        }

        th {
            background-color: #f2f2f2;
            padding: 8px;
        }

        td {
            padding: 8px;
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background-color: #e8f5e9;
        }

        .summary {
            margin-top: 40px;
        }

        .summary td {
            text-align: right;
            padding: 5px 10px;
        }
    </style>
</head>

<body>

    <h2>LAPORAN PENJUALAN</h2>
    <h4>Periode: <?= date('d M Y', strtotime($tanggalAwal)) ?> - <?= date('d M Y', strtotime($tanggalAkhir)) ?></h4>
    <?php if ($outletId): ?>
        <h4>Outlet ID: <?= $outletId ?></h4>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Outlet</th>
                <th>Total Penjualan</th>
                <th>Total Pengeluaran</th>
                <th>Laba Bruto</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($laporan as $item):
                $laba = $item['total_penjualan'] - $item['total_pengeluaran'];
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= date('d/m/Y', strtotime($item['tanggal'])) ?></td>
                    <td><?= esc($item['nama_outlet'] ?? 'Outlet ' . $item['outlet_id']) ?></td>
                    <td><?= number_format($item['total_penjualan'], 0, ',', '.') ?></td>
                    <td><?= number_format($item['total_pengeluaran'], 0, ',', '.') ?></td>
                    <td><?= number_format($laba, 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="3">TOTAL</td>
                <td><?= number_format($grandTotalPenjualan, 0, ',', '.') ?></td>
                <td><?= number_format($grandTotalPengeluaran, 0, ',', '.') ?></td>
                <td><?= number_format($grandTotalLaba, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

</body>

</html>