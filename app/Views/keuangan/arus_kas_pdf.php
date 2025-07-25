<!DOCTYPE html>
<html>

<head>
    <title><?= $tittle ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h2,
        h3 {
            text-align: center;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #000;
            text-align: left;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h2><?= $tittle ?></h2>
    <h3><?= $periodeText ?></h3>

    <!-- Operasi -->
    <h4>Arus Kas dari Aktivitas Operasi</h4>
    <?php if (!empty($arusKas['operasi'])): ?>
        <table>
            <?php foreach ($arusKas['operasi'] as $item): ?>
                <tr>
                    <td><?= esc($item['akun']) ?></td>
                    <td class="right"><?= number_format($item['jumlah'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- Investasi -->
    <h4>Arus Kas dari Aktivitas Investasi</h4>
    <?php if (!empty($arusKas['investasi'])): ?>
        <table>
            <?php foreach ($arusKas['investasi'] as $item): ?>
                <tr>
                    <td><?= esc($item['akun']) ?></td>
                    <td class="right"><?= number_format($item['jumlah'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- Pendanaan -->
    <h4>Arus Kas dari Aktivitas Pendanaan</h4>
    <?php if (!empty($arusKas['pendanaan'])): ?>
        <table>
            <?php foreach ($arusKas['pendanaan'] as $item): ?>
                <tr>
                    <td><?= esc($item['akun']) ?></td>
                    <td class="right"><?= number_format($item['jumlah'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- Total Kas -->
    <h4>Total Kenaikan/Penurunan Kas: Rp <?= number_format($arusKas['total'], 2, ',', '.') ?></h4>

    <p style="font-size: 11px; margin-top: 30px;">
        Dicetak pada: <?= $timestamp ?>
    </p>

</body>

</html>