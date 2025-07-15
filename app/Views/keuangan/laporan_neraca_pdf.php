<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        h3 {
            text-align: center;
            margin-bottom: 5px;
        }

        .meta {
            text-align: center;
            margin-bottom: 15px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .subtotal {
            font-weight: bold;
        }

        .total {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .wrapper-table {
            width: 100%;
        }

        .column {
            width: 50%;
            vertical-align: top;
        }
    </style>
</head>

<body>

    <h3><?= $tittle ?><br><?= $judul_periode ?></h3><br>
    <table class="wrapper-table">
        <tr>
            <!-- Kolom ASET -->
            <td class="column">
                <table>
                    <thead>
                        <tr>
                            <th colspan="2">Aset</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aset as $item): ?>
                            <tr>
                                <td><?= $item['keterangan'] ?></td>
                                <td class="text-right">Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total">
                            <td>Total Aset</td>
                            <td class="text-right">Rp <?= number_format($total_aset, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <!-- Kolom KEWAJIBAN + EKUITAS -->
            <td class="column">
                <!-- Kewajiban -->
                <table>
                    <thead>
                        <tr>
                            <th colspan="2">Kewajiban</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kewajiban as $item): ?>
                            <tr>
                                <td><?= $item['keterangan'] ?></td>
                                <td class="text-right">Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="subtotal">
                            <td>Total Kewajiban</td>
                            <td class="text-right">Rp <?= number_format($total_kewajiban, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Ekuitas -->
                <table>
                    <thead>
                        <tr>
                            <th colspan="2">Ekuitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ekuitas as $item): ?>
                            <tr>
                                <td><?= $item['keterangan'] ?></td>
                                <td class="text-right">Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="subtotal">
                            <td>Total Ekuitas</td>
                            <td class="text-right">Rp <?= number_format($total_ekuitas, 0, ',', '.') ?></td>
                        </tr>
                        <tr class="total">
                            <td>Total Kewajiban + Ekuitas</td>
                            <td class="text-right">Rp <?= number_format($total_kewajiban + $total_ekuitas, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
    <small class="text-muted">Dicetak pada: <?= $timestamp ?></small>
</body>

</html>