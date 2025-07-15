<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-4 text-gray-800"><?= esc($tittle); ?></h1>

            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success'); ?></div>
            <?php endif; ?>

            <a href="<?= base_url('manajemen-penjualan/pembelian-operasional/tambah') ?>" class="btn btn-primary mb-3">
                <i class="fas fa-plus"></i> Tambah Pembelian
            </a>
        </div>

        <div class="card-body">

            <?php if (!empty($outlets)): ?>
                <form method="get" class="form-row align-items-end mb-4">
                    <div class="col-md-3">
                        <label for="outlet_id">Outlet</label>
                        <select name="outlet_id" id="outlet_id" class="form-control">
                            <option value="">-- Semua Outlet --</option>
                            <?php foreach ($outlets as $outlet): ?>
                                <option value="<?= $outlet['id'] ?>" <?= ($selectedOutletId == $outlet['id']) ? 'selected' : '' ?>>
                                    <?= esc($outlet['nama_outlet']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="start_date">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                            value="<?= esc($filter['start_date'] ?? '') ?>">
                    </div>

                    <div class="col-md-3">
                        <label for="end_date">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                            value="<?= esc($filter['end_date'] ?? '') ?>">
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-block">Filter</button>
                    </div>
                </form>
            <?php endif; ?>


            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <?php if (!empty($outlets)): ?>
                                <th>Outlet</th>
                            <?php endif; ?>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($pembelian_operasional as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>

                                <?php if (!empty($outlets)): ?>
                                    <td><?= esc($row['nama_outlet']) ?></td>
                                <?php endif; ?>

                                <td>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($row['item'] as $item): ?>
                                            <li><?= esc($item['nama_barang']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>

                                <td>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($row['item'] as $item): ?>
                                            <li><?= number_format($item['jumlah'], 0) ?></li> <!-- satuan dihapus -->
                                        <?php endforeach; ?>
                                    </ul>
                                </td>

                                <td class="text-right">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>

                                <td class="text-center">
                                    <a href="<?= base_url('manajemen-penjualan/pembelian-operasional/detail/' . $row['id']) ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <?php if (in_groups('admin')) : ?>
                                        <a href="<?= base_url('manajemen-penjualan/pembelian-operasional/delete/' . $row['id']) ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus pembelian ini?');">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    <?php endif; ?>

                                </td>

                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pembelian_operasional)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Tidak ada data pembelian.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection(); ?>