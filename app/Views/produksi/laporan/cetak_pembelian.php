<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian - Origins Kebab</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="icon" href="<?= base_url('img/logo_icon.png') ?>" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            color: #212529;
            background-color: #fff;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 1rem;
            position: relative;
            padding: 1.5rem 0;
        }

        .header-logo {
            position: absolute;
            left: 5rem;
        }

        .header-logo img {
            height: 120px;
            width: auto;
        }

        .header-text {
            text-align: center;
        }

        h1,
        h4 {
            margin: 0;
            font-weight: 600;
        }

        h1 {
            font-size: 1.8rem;
        }

        h4 {
            font-size: 1.2rem;
            margin-top: 0.2rem;
        }

        .date-range {
            font-size: 0.95rem;
            color: #555;
            margin-top: 0.2rem;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .summary-table th,
        .summary-table td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            font-size: 13px;
        }

        .summary-table th {
            background-color: #f1f1f1;
            text-align: left;
            font-weight: 600;
        }

        .summary-table td {
            text-align: right;
            font-weight: 500;
        }

        .summary-table .text-success {
            color: #28a745;
            font-weight: bold;
        }

        .border-top-line {
            border-top: 2px solid #333;
            margin: 20px 0;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }

            .container {
                padding: 0 10px;
            }

            .header-logo img {
                height: 70px;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header">
        <div class="header-logo">
            <img src="<?= base_url('img/img-login.png') ?>" alt="Origins Kebab Logo">
        </div>
        <div class="header-text">
            <h1>Origins Kebab</h1>
            <h4>Laporan Pembelian</h4>
            <div class="date-range">
                Periode: <?= date('Y-m-d', strtotime($start)) ?> s/d <?= date('Y-m-d', strtotime($end)) ?>
            </div>
        </div>
    </div>
    <table class="table table-bordered table-striped" style="width:100%; margin-top:20px; font-size:13px;">
        <thead class="thead-light">
            <tr class="align-middle">
                <th class="text-left" style="width:40px;">No</th>
                <th class="text-left" style="width:100px;">Tanggal</th>
                <th class="text-left" style="width:140px;">Nama Pemasok</th>
                <th class="text-left" style="width:120px;">Jenis Pembayaran</th>
                <th class="text-left" style="width:160px;">Nama Bahan</th>
                <th class="text-left" style="width:80px;">Jumlah</th>
                <th class="text-left" style="width:80px;">Satuan</th>
                <th class="text-right" style="width:80px;">Harga Satuan</th>
                <th class="text-right" style="width:80px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $grandTotal = 0;
            if (!empty($pembelian)) :
                foreach ($pembelian as $p) :
                    if (!empty($p['detail']) && is_array($p['detail'])) :
                        foreach ($p['detail'] as $d) :
                            $subtotal = isset($d['subtotal']) ? $d['subtotal'] : 0;
                            $grandTotal += $subtotal;
            ?>
                            <tr class="text-center align-middle">
                                <td><?= $no++ ?></td>
                                <td><?= isset($p['tanggal']) ? date('d-m-Y', strtotime($p['tanggal'])) : '-' ?></td>
                                <td><?= esc($d['nama_pemasok'] ?? '-') ?></td>
                                <td><?= ucfirst($d['tipe_pembayaran'] ?? $p['jenis_pembayaran'] ?? '-') ?></td>
                                <td><?= esc($d['nama_bahan'] ?? '-') ?></td>
                                <td><?= $d['jumlah'] ?? '-' ?></td>
                                <td><?= $d['satuan'] ?? '-' ?></td>
                                <td class="text-right">Rp <?= isset($d['harga_satuan']) ? number_format($d['harga_satuan'], 0, ',', '.') : '-' ?></td>
                                <td class="text-right">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                            </tr>
                <?php
                        endforeach;
                    endif;
                endforeach;
            else :
                ?>
                <tr>
                    <td colspan="9" class="text-center">Data tidak tersedia</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" class="text-right">Total Keseluruhan</th>
                <th class="text-right">Rp <?= number_format($grandTotal, 0, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>
</body>

</html>