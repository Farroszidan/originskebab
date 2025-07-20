<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Pembelian</title>
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
        <h4><u>Laporan Pembelian</u></h4>
        <p>Periode: <?= date('Y-m-d', strtotime($start)) ?> s/d <?= date('Y-m-d', strtotime($end)) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Nota</th>
                <th>Tanggal</th>
                <th>Nama Pemasok</th>
                <th>Jenis Pembayaran</th>
                <th>Total</th>
                <th>Status Barang</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $grandTotal = 0;
            ?>
            <?php if (!empty($pembelian)) :
                foreach ($pembelian as $p) :
                    $grandTotal += $p['total'];
            ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($p['no_nota'] ?? '-') ?></td>
                        <td><?= isset($p['tanggal']) ? date('d-m-Y', strtotime($p['tanggal'])) : '-' ?></td>
                        <td><?= esc($p['nama_pemasok'] ?? '-') ?></td>
                        <td><?= ucfirst($p['jenis_pembayaran'] ?? '-') ?></td>
                        <td>Rp <?= number_format($p['total'] ?? 0, 0, ',', '.') ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $p['status_barang'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach;
            else : ?>
                <tr>
                    <td colspan="7" class="text-center">Data tidak tersedia</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">Total Keseluruhan</th>
                <th colspan="2">Rp <?= number_format($grandTotal, 0, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>
</body>

</html>