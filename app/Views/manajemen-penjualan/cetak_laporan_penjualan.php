<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - Origins Kebab</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            color: #212529;
            background-color: #fff;
        }

        h1,
        h4,
        h5 {
            text-align: center;
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        h1 {
            font-size: 1.8rem;
        }

        h4 {
            font-size: 1.3rem;
            margin-top: 5px;
        }

        .date-range {
            text-align: center;
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 1rem;
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

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .table th,
        .table td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            font-size: 12.5px;
            vertical-align: middle;
        }

        .table thead th {
            background-color: #e9ecef;
            font-weight: 600;
            text-align: center;
        }

        .border-top-line {
            border-top: 2px solid #333;
            margin: 20px 0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .text-muted {
            color: #888 !important;
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

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="container mt-4">
        <h1>Origins Kebab</h1>
        <h4>Laporan Penjualan</h4>
        <div class="date-range">
            <?= date('d M Y', strtotime($tanggalAwal)) ?> - <?= date('d M Y', strtotime($tanggalAkhir)) ?>
        </div>

        <table class="summary-table">
            <tr>
                <th>Total Penjualan</th>
                <td>Rp <?= number_format($grandTotalPenjualan, 0, ',', '.') ?></td>
                <th>Total Pengeluaran</th>
                <td>Rp <?= number_format($grandTotalPengeluaran, 0, ',', '.') ?></td>
                <th>Laba Bersih</th>
                <td class="text-success">Rp <?= number_format($grandTotalLaba, 0, ',', '.') ?></td>
            </tr>
        </table>

        <div class="border-top-line"></div>

        <h5 class="text-left">📌 Rincian Penjualan</h5>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Outlet</th>
                    <th>Kasir</th>
                    <th>Kode Menu</th>
                    <th>Nama Menu</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Add Ons</th>
                    <th>Extra</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($detailPenjualan as $row): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_jual'])) ?></td>
                        <td class="text-center"><?= esc($row['jam_jual']) ?></td>
                        <td class="text-left"><?= esc($row['nama_outlet']) ?></td>
                        <td class="text-left"><?= esc($row['nama_kasir']) ?></td>
                        <td class="text-left"><?= esc($row['kode_menu']) ?></td>
                        <td class="text-left"><?= esc($row['nama_menu']) ?></td>
                        <td class="text-right">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td class="text-center"><?= $row['qty'] ?></td>
                        <td class="text-right">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                        <td class="text-left"><?= esc($row['add_ons'] ?? '-') ?></td>
                        <td class="text-left"><?= esc($row['extra'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (count($detailPenjualan) === 0): ?>
                    <tr>
                        <td colspan="12" class="text-center text-muted">Tidak ada data penjualan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>

</html>