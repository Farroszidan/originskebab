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
    <div class="header" style="display: flex; align-items: center; justify-content: center; gap: 16px; margin-bottom: 1rem; position: relative; padding: 1.5rem 0;">
        <div class="header-logo" style="position: absolute; left: 5rem;">
            <img src="<?= base_url('img/img-login.png') ?>" alt="Origins Kebab Logo" style="height: 120px; width: auto;">
        </div>
        <div class="header-text" style="text-align: center;">
            <h1 style="margin:0;font-weight:600;font-size:1.8rem;">Origins Kebab</h1>
            <h4 style="margin:0;font-weight:600;font-size:1.2rem;margin-top:0.2rem;">Laporan Produksi</h4>
            <div class="date-range" style="font-size:0.95rem;color:#555;margin-top:0.2rem;">
                Periode: <?= date('Y-m-d', strtotime($start)) ?> s/d <?= date('Y-m-d', strtotime($end)) ?>
            </div>
        </div>
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