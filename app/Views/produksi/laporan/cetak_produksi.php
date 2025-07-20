<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Produksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2,
        .header p {
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        .print-button {
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header">
        <h2><b>ORIGINS KEBAB</b></h2>
        <p>Gedawang Permai I No.1e, Gedawang, Kec. Banyumanik, Kota Semarang, Jawa Tengah 50266</p>
        <h4><u>Laporan Produksi</u></h4>
        <p>Periode: <?= date('Y-m-d', strtotime($start)) ?> s/d <?= date('Y-m-d', strtotime($end)) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama BSJ</th>
                <th>Jumlah Produksi</th>
                <th>Status Produksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php if (!empty($produksi)) :
                foreach ($produksi as $row) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                        <td><?= esc($row['nama_bsj'] ?? '-') ?></td>
                        <td><?= esc($row['jumlah']) ?> <?= esc($row['satuan'] ?? '') ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                    </tr>
                <?php endforeach;
            else : ?>
                <tr>
                    <td colspan="5" class="text-center">Data tidak tersedia</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>