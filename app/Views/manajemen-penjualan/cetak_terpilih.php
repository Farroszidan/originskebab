<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Transaksi Terpilih</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 40px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }

        table.detail {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table.detail th,
        table.detail td {
            border: 1px solid #999;
            padding: 8px 10px;
            font-size: 13px;
        }

        table.detail th {
            background-color: #f0f0f0;
        }

        table.detail tr:nth-child(even) {
            background-color: #fafafa;
        }

        .total-transaksi,
        .total-semua {
            font-weight: bold;
            text-align: right;
            background-color: #f9f9f9;
        }

        .total-semua {
            background-color: #e9e9e9;
        }

        .footer {
            border-top: 1px solid #ccc;
            padding-top: 20px;
            font-style: italic;
            font-size: 0.95em;
            color: #555;
            text-align: center;
            margin-top: 60px;
        }

        @media print {
            body {
                margin: 0;
            }

            .footer {
                position: fixed;
                bottom: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <h2>Daftar Transaksi Terpilih</h2>

    <table class="detail">
        <thead>
            <tr>
                <th>No</th>
                <th>No Faktur</th>
                <th>Tanggal</th>
                <th>Kasir</th>
                <th>Outlet</th>
                <th>Menu</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            $totalSemua = 0; ?>
            <?php foreach ($transaksi_list as $trx): ?>
                <?php foreach ($trx['detail'] as $i => $item): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <?php if ($i == 0): ?>
                            <td><?= esc($trx['no_faktur']) ?></td>
                            <td><?= date('d M Y H:i', strtotime($trx['tgl_jual'])) ?></td>
                            <td><?= esc($trx['nama_kasir'] ?? '-') ?></td>
                            <td><?= esc($trx['nama_outlet'] ?? '-') ?></td>
                        <?php else: ?>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        <?php endif; ?>
                        <td><?= esc($item['nama_menu']) ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td>Rp<?= number_format($item['harga'], 0, ',', '.') ?></td>
                        <td>Rp<?= number_format($item['total_harga'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="8" class="total-transaksi">Total Transaksi (<?= esc($trx['no_faktur']) ?>)</td>
                    <td class="total-transaksi">Rp<?= number_format($trx['grand_total'], 0, ',', '.') ?></td>
                </tr>
                <?php $totalSemua += $trx['grand_total']; ?>
            <?php endforeach; ?>
            <tr>
                <td colspan="8" class="total-semua">Total Seluruh Transaksi</td>
                <td class="total-semua">Rp<?= number_format($totalSemua, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Terima kasih telah berbelanja</p>
    </div>

</body>

</html>