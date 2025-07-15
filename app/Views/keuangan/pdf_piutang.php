<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Piutang Origins Kebab</title> <!-- ✅ Ini penting untuk judul tab -->
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        h3 {
            margin-bottom: 0;
            text-align: center;
        }

        small {
            display: block;
            text-align: center;
        }
    </style>
</head>

<body>
    <h3>Laporan Piutang Origins Kebab</h3>
    <small>Periode: <?= date('d-m-Y', strtotime($start)) ?> s.d <?= date('d-m-Y', strtotime($end)) ?></small>

    <table>
        <thead>
            <tr>
                <th>Akun</th>
                <th>Jumlah Piutang (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($piutang as $row): ?>
                <tr>
                    <td><?= esc($row['nama_akun']) ?></td>
                    <td><?= number_format($row['jumlah'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th><?= number_format($total_piutang, 2, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>
</body>

</html>