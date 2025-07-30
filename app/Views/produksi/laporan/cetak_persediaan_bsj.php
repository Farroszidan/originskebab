<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Persediaan BSJ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header" style="display: flex; align-items: center; justify-content: center; gap: 16px; margin-bottom: 1rem; position: relative; padding: 1.5rem 0;">
        <div class="header-logo" style="position: absolute; left: 5rem;">
            <img src="<?= base_url('img/img-login.png') ?>" alt="Origins Kebab Logo" style="height: 120px; width: auto;">
        </div>
        <div class="header-text" style="text-align: center;">
            <h1 style="margin:0;font-weight:600;font-size:1.8rem;">Origins Kebab</h1>
            <h4 style="margin:0;font-weight:600;font-size:1.2rem;margin-top:0.2rem;">Laporan Persediaan BSJ</h4>
            <div class="date-range" style="font-size:0.95rem;color:#555;margin-top:0.2rem;">
                Periode: <?= isset($start) ? date('Y-m-d', strtotime($start)) : '-' ?> s/d <?= isset($end) ? date('Y-m-d', strtotime($end)) : '-' ?>
            </div>
        </div>
    </div>

    <table class="table table-bordered table-striped" style="width:100%; margin-top:20px; font-size:13px;">
        <thead class="thead-light">
            <tr class="align-middle">
                <th class="text-left" style="width:40px;">No</th>
                <th class="text-left" style="width:160px;">Nama BSJ</th>
                <th class="text-left" style="width:80px;">Satuan</th>
                <th class="text-right" style="width:100px;">Stok Awal</th>
                <th class="text-right" style="width:100px;">Barang Masuk</th>
                <th class="text-right" style="width:100px;">Barang Keluar</th>
                <th class="text-right" style="width:100px;">Stok Akhir</th>
                <th class="text-right" style="width:120px;">Harga Satuan Rata-rata</th>
                <th class="text-right" style="width:120px;">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalStokAkhir = 0;
            $totalSaldoAkhir = 0;
            if (isset($bsjData) && is_array($bsjData) && count($bsjData) > 0) {
                $no = 1;
                foreach ($bsjData as $row) {
                    $totalStokAkhir += $row['stok_akhir'];
                    $totalSaldoAkhir += $row['saldo_akhir'];
            ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($row['nama']) ?></td>
                        <td><?= esc($row['satuan']) ?></td>
                        <td class="text-right"><?= number_format($row['stok_awal'], 2) ?></td>
                        <td class="text-right"><?= number_format($row['masuk'], 2) ?></td>
                        <td class="text-right"><?= number_format($row['keluar'], 2) ?></td>
                        <td class="text-right"><?= number_format($row['stok_akhir'], 2) ?></td>
                        <td class="text-right">Rp <?= number_format($row['harga_avg'], 2, ',', '.') ?></td>
                        <td class="text-right">Rp <?= number_format($row['saldo_akhir'], 2, ',', '.') ?></td>
                    </tr>
                <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="9" class="text-center">Data tidak tersedia</td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">Total Stok Akhir</th>
                <th class="text-right"><?= number_format($totalStokAkhir, 2) ?></th>
                <th class="text-right" colspan="2">Total Saldo Akhir: Rp <?= number_format($totalSaldoAkhir, 2, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>
</body>

</html>