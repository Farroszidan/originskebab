<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <h2>Laporan Laba Rugi<br>
        ORIGINS KEBAB<br>
        <small><?= esc($judulPeriode) ?></small>
    </h2>

    <h4>Pendapatan</h4>
    <table>
        <tr>
            <th>Pendapatan</th>
            <th>Jumlah (Rp)</th>
        </tr>
        <?php if (!empty($pendapatan)): ?>
            <?php foreach ($pendapatan as $p): ?>
                <tr>
                    <td><?= esc($p['nama_akun']) ?></td>
                    <td><?= number_format($p['jumlah'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="2">Tidak ada data pendapatan.</td>
            </tr>
        <?php endif; ?>
        <tr>
            <th>Total Pendapatan</th>
            <th><?= number_format($totalPendapatan, 2, ',', '.') ?></th>
        </tr>
    </table>

    <h4>Beban</h4>
    <table>
        <tr>
            <th>Beban</th>
            <th>Jumlah (Rp)</th>
        </tr>
        <?php if (!empty($beban)): ?>
            <?php foreach ($beban as $b): ?>
                <tr>
                    <td><?= esc($b['nama_akun']) ?></td>
                    <td><?= number_format($b['jumlah'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="2">Tidak ada data beban.</td>
            </tr>
        <?php endif; ?>
        <tr>
            <th>Total Beban</th>
            <th><?= number_format($totalBeban, 2, ',', '.') ?></th>
        </tr>
    </table>

    <h4>Laba Bersih (<?= esc($judulPeriode) ?>): Rp <?= number_format($labaBersih, 2, ',', '.') ?></h4>
    <p>Dicetak pada: <?= date('d-m-Y H:i:s') ?></p>
</body>

</html>