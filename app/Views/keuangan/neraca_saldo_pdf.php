<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h3 { text-align: center; margin-bottom: 5px; }
        .meta { text-align: center; margin-bottom: 15px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        .text-right { text-align: right; }
        .thead-dark th { background: #222; color: #fff; }
        tfoot th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h3><?= $tittle ?><br>Bulan <?= date('F', mktime(0,0,0,$bulan,1)) ?> <?= $tahun ?></h3>
    <table>
        <thead class="thead-dark">
            <tr>
                <th class="text-center">Kode Akun</th>
                <th class="text-center">Nama Akun</th>
                <th class="text-center">Debit (Rp)</th>
                <th class="text-center">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($akun as $a): ?>
                <tr>
                    <td><?= esc($a['kode_akun']) ?></td>
                    <td><?= esc($a['nama_akun']) ?></td>
                    <td class="text-right">
                        <?= $a['tipe'] === 'debit' ? number_format($a['saldo'], 2, ',', '.') : '0,00' ?>
                    </td>
                    <td class="text-right">
                        <?= $a['tipe'] === 'kredit' ? number_format($a['saldo'], 2, ',', '.') : '0,00' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">Total </th>
                <th class="text-right text-success"><?= number_format($total_debet, 2, ',', '.') ?></th>
                <th class="text-right text-danger"><?= number_format($total_kredit, 2, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>
    <small class="text-muted">Dicetak pada: <?= date('d-m-Y H:i') ?></small>
</body>
</html>
