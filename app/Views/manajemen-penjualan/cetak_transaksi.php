<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Transaksi - <?= esc($transaksi['no_faktur']) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 30px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .header,
        .footer {
            text-align: center;
            margin-bottom: 20px;
        }

        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .footer {
            border-top: 1px solid #ccc;
            padding-top: 10px;
            font-style: italic;
            font-size: 0.9em;
            color: #555;
        }

        .info {
            margin-bottom: 20px;
        }

        .info td {
            padding: 4px 8px;
        }

        table.detail {
            width: 100%;
            border-collapse: collapse;
        }

        table.detail th,
        table.detail td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        table.detail th {
            background-color: #f0f0f0;
        }

        .total {
            margin-top: 20px;
            font-size: 18px;
            text-align: right;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <h2>Nota Transaksi</h2>

    <div class="header">
        <strong>No Faktur:</strong> <?= esc($transaksi['no_faktur']) ?><br>
        <strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($transaksi['tgl_jual'])) ?>
    </div>

    <table class="info">
        <tr>
            <td><strong>Kasir</strong></td>
            <td>: <?= esc($transaksi['nama_kasir'] ?? '-') ?></td>
        </tr>
        <tr>
            <td><strong>Outlet</strong></td>
            <td>: <?= esc($transaksi['nama_outlet'] ?? '-') ?></td>
        </tr>
    </table>

    <table class="detail">
        <thead>
            <tr>
                <th>No</th>
                <th>Menu</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($detail)) : $no = 1; ?>
                <?php foreach ($detail as $item): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($item['nama_menu']) ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td>Rp<?= number_format($item['harga'], 0, ',', '.') ?></td>
                        <td>Rp<?= number_format($item['total_harga'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">Tidak ada item.</td>
                </tr>
            <?php endif ?>
        </tbody>
    </table>

    <div class="total">
        <strong>Total: Rp<?= number_format($transaksi['grand_total'], 0, ',', '.') ?></strong>
    </div>

    <div class="footer">
        <p>Terima kasih telah berbelanja</p>
    </div>

</body>

</html>