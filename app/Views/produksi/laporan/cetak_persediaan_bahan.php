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

    <div class="header" style="display: flex; align-items: center; justify-content: center; gap: 16px; margin-bottom: 1rem; position: relative; padding: 1.5rem 0;">
        <div class="header-logo" style="position: absolute; left: 5rem;">
            <img src="<?= base_url('img/img-login.png') ?>" alt="Origins Kebab Logo" style="height: 120px; width: auto;">
        </div>
        <div class="header-text" style="text-align: center;">
            <h1 style="margin:0;font-weight:600;font-size:1.8rem;">Origins Kebab</h1>
            <h4 style="margin:0;font-weight:600;font-size:1.2rem;margin-top:0.2rem;">Laporan Persediaan Bahan</h4>
            <div class="date-range" style="font-size:0.95rem;color:#555;margin-top:0.2rem;">
                Periode: <?= date('Y-m-d', strtotime($start)) ?> s/d <?= date('Y-m-d', strtotime($end)) ?>
            </div>
        </div>
    </div>

    <table class="table table-bordered table-striped" style="width:100%; margin-top:20px; font-size:13px;">
        <thead class="thead-light">
            <tr class="align-middle">
                <th class="text-left" style="width:40px;">No</th>
                <th class="text-left" style="width:80px;">Kode</th>
                <th class="text-left" style="width:160px;">Nama</th>
                <th class="text-left" style="width:80px;">Satuan</th>
                <th class="text-right" style="width:100px;">Jumlah Masuk</th>
                <th class="text-right" style="width:100px;">Jumlah Keluar</th>
                <th class="text-right" style="width:100px;">Sisa Akhir</th>
                <th class="text-right" style="width:120px;">Harga Satuan Rata-rata</th>
                <th class="text-right" style="width:120px;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $totalKeseluruhan = 0;
            foreach ($bahan as $b) {
                $masuk = 0;
                $keluar = 0;
                $totalHargaMasuk = 0;
                $totalQtyMasuk = 0;
                foreach ($kartu as $k) {
                    if ($k['bahan_id'] == $b['id']) {
                        if ($k['jenis'] == 'masuk') {
                            $masuk += $k['jumlah'];
                            $totalHargaMasuk += $k['jumlah'] * $k['harga_satuan'];
                            $totalQtyMasuk += $k['jumlah'];
                        } elseif ($k['jenis'] == 'keluar') {
                            $keluar += $k['jumlah'];
                        }
                    }
                }
                $sisa = $masuk - $keluar;
                // Hitung harga satuan rata-rata dari semua transaksi masuk
                $harga_avg_bahan = $totalQtyMasuk > 0 ? ($totalHargaMasuk / $totalQtyMasuk) : 0;
                if (strtolower($b['satuan']) == 'kg' || strtolower($b['satuan']) == 'liter') {
                    $total_bahan = ($sisa / 1000) * $harga_avg_bahan;
                } else {
                    $total_bahan = $sisa * $harga_avg_bahan;
                }
                $totalKeseluruhan += $total_bahan;
            ?>
                <tr class="text-center align-middle">
                    <td><?= $no++; ?></td>
                    <td><?= esc($b['kode']); ?></td>
                    <td><?= esc($b['nama']); ?></td>
                    <td><?= esc($b['satuan']); ?></td>
                    <td class="text-right">
                        <?php
                        if (strtolower($b['satuan']) == 'kg' || strtolower($b['satuan']) == 'liter') {
                            echo number_format($masuk / 1000, 2);
                        } else {
                            echo number_format($masuk, 2);
                        }
                        ?>
                    </td>
                    <td class="text-right">
                        <?php
                        if (strtolower($b['satuan']) == 'kg' || strtolower($b['satuan']) == 'liter') {
                            echo number_format($keluar / 1000, 2);
                        } else {
                            echo number_format($keluar, 2);
                        }
                        ?>
                    </td>
                    <td class="text-right">
                        <?php
                        if (strtolower($b['satuan']) == 'kg' || strtolower($b['satuan']) == 'liter') {
                            echo number_format($sisa / 1000, 2);
                        } else {
                            echo number_format($sisa, 2);
                        }
                        ?>
                    </td>
                    <td class="text-right">
                        <?= number_format($harga_avg_bahan, 2) ?>
                    </td>
                    <td class="text-right">
                        <?= number_format($total_bahan, 2) ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" class="text-right">Total Keseluruhan</th>
                <th class="text-right"><?= number_format($totalKeseluruhan, 2) ?></th>
            </tr>
        </tfoot>
    </table>

</body>

</html>