<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Perubahan Ekuitas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: right;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        td.text-left {
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>Laporan Perubahan Ekuitas<br>ORIGINS KEBAB<br><?= esc($judulPeriode) ?></h2>
    <table>
        <thead>
            <tr>
                <th class="text-left">Keterangan</th>
                <th>Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ekuitas as $item): ?>
                <tr>
                    <td class="text-left"><?= esc($item['keterangan']) ?></td>
                    <td><?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p style="margin-top:30px; font-size:12px;">
        Dicetak pada: <?= esc($timestamp) ?>
    </p>
    <p style="font-size:12px;">
</body>

</html>