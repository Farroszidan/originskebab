<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Perubahan Ekuitas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h2,
        h3 {
            text-align: center;
            margin: 0;
        }

        h3 {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
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

        .font-bold {
            font-weight: bold;
        }

        .text-danger {
            color: red;
        }

        .text-success {
            color: green;
        }
    </style>
</head>

<body>
    <h2>Sistem Informasi Laporan Keuangan</h2>
    <h2>Origins Kebab</h2>
    <h3>Laporan Perubahan Ekuitas</h3>
    <p style="text-align: center;"><?= esc($judulPeriode ?? '') ?></p>

    <table>
        <thead>
            <tr>
                <th class="text-left">Keterangan</th>
                <th>Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-left">Modal Awal</td>
                <td><?= number_format($modalAwal ?? 0, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-left">Tambahan Modal</td>
                <td><?= number_format($tambahanModal ?? 0, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="text-left">Laba Bersih</td>
                <td class="<?= ($labaBersih ?? 0) < 0 ? 'text-danger' : 'text-success' ?>">
                    <?= number_format($labaBersih ?? 0, 0, ',', '.') ?>
                </td>
            </tr>
            <tr>
                <td class="text-left">Prive</td>
                <td class="<?= ($prive ?? 0) < 0 ? 'text-danger' : 'text-success' ?>">
                    <?= number_format($prive ?? 0, 0, ',', '.') ?>
                </td>
            </tr>
            <tr class="font-bold">
                <td class="text-left">Modal Akhir</td>
                <td><?= number_format($modalAkhir ?? 0, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top: 25px;">Dicetak pada: <?= esc($timestamp ?? date('d-m-Y H:i:s')) ?> WIB</p>
</body>

</html>