<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Tanggal:</strong> <?= date('d-m-Y', strtotime($perintah['tanggal'] ?? '')) ?></p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header font-weight-bold">Daftar Produksi (BSJ/Bahan Baku)</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Ambil semua produksi yang punya admin_id yang sama
                    $produksiList = array_filter($perintah_list ?? [], function ($item) use ($perintah) {
                        return isset($item['admin_id']) && isset($perintah['admin_id']) && $item['admin_id'] == $perintah['admin_id'];
                    });
                    ?>
                    <?php if (!empty($produksiList)) : ?>
                        <?php foreach ($produksiList as $p) : ?>
                            <tr>
                                <td><?= esc($p['nama']) ?></td>
                                <td><?= strtoupper(esc($p['tipe'])) ?></td>
                                <td><?= esc($p['jumlah']) ?></td>
                                <td><?= esc($p['satuan']) ?></td>
                            </tr>
                        <?php endforeach ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data produksi.</td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header font-weight-bold">Kebutuhan Bahan yang Perlu Dibeli</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Jumlah Dibutuhkan</th>
                        <th>Satuan</th>
                        <th>Stok Tersedia</th>
                        <th>Kekurangan</th>
                        <th>Pembulatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $bahanModel = new \App\Models\BahanModel();
                    $adaKekurangan = false;
                    ?>
                    <?php foreach ($detail as $b) :
                        $bahan = $bahanModel->where('nama', $b['nama'])->where('satuan', $b['satuan'])->first();
                        $stok = $bahan['stok'] ?? 0;
                        // Jika satuan kg atau liter, tampilkan stok dalam satuan kg/liter (bukan gram/ml)
                        $stok_tampil = $stok;
                        if (isset($b['satuan'])) {
                            $satuan_lc = strtolower($b['satuan']);
                            if ($satuan_lc === 'kg' || $satuan_lc === 'liter' || $satuan_lc === 'ltr') {
                                $stok_tampil = $stok / 1000;
                            }
                        }
                        $kurang = $b['jumlah'] - $stok_tampil;
                        $pembulatan = ceil($kurang);
                    ?>
                        <?php if ($kurang > 0) :
                            $adaKekurangan = true;
                        ?>
                            <tr>
                                <td><?= esc($b['nama']) ?></td>
                                <td><?= esc($b['kategori']) ?></td>
                                <td><?= esc(number_format($b['jumlah'], 2)) ?></td>
                                <td><?= esc($b['satuan']) ?></td>
                                <td><?= esc(number_format($stok_tampil, 2)) ?></td>
                                <td><strong><?= esc(number_format($kurang, 2)) ?></strong></td>
                                <td><strong><?= esc(number_format($pembulatan, 0)) ?></strong></td>
                            </tr>
                        <?php endif ?>
                    <?php endforeach; ?>
                    <?php if (!$adaKekurangan): ?>
                        <tr>
                            <td colspan="7" class="text-center">Semua kebutuhan bahan tersedia, tidak ada kekurangan.</td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="<?= base_url('admin/perintah-kerja'); ?>" class="btn btn-secondary">Kembali</a>
</div>

<?= $this->endSection(); ?>