<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Utang Origins Kebab</title>
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

        .header {
            text-align: center;
        }

        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }

        .total-keterangan {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="header">
        <!-- Gambar dibaca langsung dari file sistem -->
        <img src="file://<?= FCPATH . 'img/logo_icon.png' ?>" class="logo" alt="Logo">
        <h3 style="margin: 0;">Sistem Informasi Laporan Keuangan</h3>
        <h3 style="margin: 0;">Origins Kebab</h3>
        <h3 style="margin-bottom: 5px;">Laporan Utang</h3>
        <small>Periode: <?= date('d-m-Y', strtotime($start)) ?> s.d <?= date('d-m-Y', strtotime($end)) ?></small>
    </div>

    <table>
        <thead>
            <tr>
                <th>Supplier</th>
                <th>Jumlah Utang (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utang as $row): ?>
                <tr>
                    <td><?= esc($row['nama_supplier']) ?></td>
                    <td><?= number_format($row['jumlah'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th><?= number_format($total_utang, 2, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="total-keterangan">
        Total utang periode ini: Rp <?= number_format($total_utang, 2, ',', '.') ?>
    </div>

</body>

</html>