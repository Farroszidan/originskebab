<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Persediaan Bahan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
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
    </style>
</head>

<body onload="window.print()">

    <div class="header">
        <h2><b>ORIGINS KEBAB</b></h2>
        <p>Gedawang Permai I No.1e, Gedawang, Kec. Banyumanik, Kota Semarang</p>
        <h4><u>Laporan Persediaan Bahan</u></h4>
        <p>Periode: <?= date('d-m-Y', strtotime($start)) ?> s/d <?= date('d-m-Y', strtotime($end)) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Satuan</th>
                <th>Jumlah Masuk</th>
                <th>Jumlah Keluar</th>
                <th>Sisa Akhir</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($bahan as $b) {
                $masuk = 0;
                $keluar = 0;
                foreach ($kartu as $k) {
                    if ($k['bahan_id'] == $b['id']) {
                        if ($k['jenis'] == 'masuk') {
                            $masuk += $k['jumlah'];
                        } elseif ($k['jenis'] == 'keluar') {
                            $keluar += $k['jumlah'];
                        }
                    }
                }
                $sisa = $masuk - $keluar;
            ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= esc($b['kode']); ?></td>
                    <td><?= esc($b['nama']); ?></td>
                    <td><?= esc($b['satuan']); ?></td>
                    <td>
                        <?php
                        if (strtolower($b['satuan']) == 'kg' || strtolower($b['satuan']) == 'liter') {
                            echo number_format($masuk / 1000);
                        } else {
                            echo number_format($masuk);
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if (strtolower($b['satuan']) == 'kg' || strtolower($b['satuan']) == 'liter') {
                            echo number_format($keluar / 1000);
                        } else {
                            echo number_format($keluar);
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if (strtolower($b['satuan']) == 'kg' || strtolower($b['satuan']) == 'liter') {
                            echo number_format($sisa / 1000);
                        } else {
                            echo number_format($sisa);
                        }
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</body>

</html>