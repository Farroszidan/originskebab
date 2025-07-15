<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 30px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
        }

        th {
            background-color: #f0f0f0;
        }

        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 13px;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <h2>Laporan Transaksi</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Faktur</th>
                <th>Tanggal</th>
                <th>Outlet</th>
                <th>Kasir</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $grandTotal = 0;
            foreach ($transaksi as $row):
                $grandTotal += $row['grand_total'];
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($row['no_faktur']) ?></td>
                    <td><?= esc($row['tgl_jual']) ?></td>
                    <td><?= esc($row['nama_outlet'] ?? '-') ?></td>
                    <td><?= esc($row['nama_kasir'] ?? '-') ?></td>
                    <td>Rp<?= number_format($row['grand_total'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align:right"><strong>Total Keseluruhan</strong></td>
                <td><strong>Rp<?= number_format($grandTotal, 0, ',', '.') ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dicetak pada <?= date('d-m-Y H:i:s') ?>
    </div>

</body>

</html>