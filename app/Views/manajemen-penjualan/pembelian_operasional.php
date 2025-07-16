<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <div class="card border border-dark shadow mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold"><?= esc($tittle); ?></h5>
            <a href="<?= base_url('manajemen-penjualan/pembelian-operasional/tambah') ?>" class="btn btn-sm btn-warning text-dark font-weight-bold">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>

        <div class="card-body">
            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert alert-success alert-dismissible fade show border-left border-success pl-3" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= session()->getFlashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (!empty($outlets)): ?>
                <form method="get" class="form-row align-items-end mb-4">
                    <div class="form-group col-md-3">
                        <label for="outlet_id" class="font-weight-bold text-dark">Outlet</label>
                        <select name="outlet_id" id="outlet_id" class="form-control border border-dark">
                            <option value="">-- Semua Outlet --</option>
                            <?php foreach ($outlets as $outlet): ?>
                                <option value="<?= $outlet['id'] ?>" <?= ($selectedOutletId == $outlet['id']) ? 'selected' : '' ?>>
                                    <?= esc($outlet['nama_outlet']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="start_date" class="font-weight-bold text-dark">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control border border-dark"
                            value="<?= esc($filter['start_date'] ?? '') ?>">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="end_date" class="font-weight-bold text-dark">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" class="form-control border border-dark"
                            value="<?= esc($filter['end_date'] ?? '') ?>">
                    </div>

                    <div class="form-group col-md-3">
                        <button type="submit" class="btn btn-danger btn-block font-weight-bold">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped text-sm">
                    <thead class="thead-dark text-center">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <?php if (!empty($outlets)): ?>
                                <th>Outlet</th>
                            <?php endif; ?>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($pembelian_operasional as $row): ?>
                            <tr>
                                <td class="text-center font-weight-bold"><?= $no++ ?></td>
                                <td class="text-dark"><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>

                                <?php if (!empty($outlets)): ?>
                                    <td class="text-dark"><?= esc($row['nama_outlet']) ?></td>
                                <?php endif; ?>

                                <td>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($row['item'] as $item): ?>
                                            <li><span class="text-dark"><?= esc($item['nama_barang']) ?></span></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>

                                <td class="text-center">
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($row['item'] as $item): ?>
                                            <li><span class="badge badge-danger"><?= number_format($item['jumlah']) ?></span></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>

                                <td class="text-right font-weight-bold text-primary">
                                    Rp <?= number_format($row['total'], 0, ',', '.') ?>
                                </td>

                                <td class="text-center">
                                    <a href="<?= base_url('manajemen-penjualan/pembelian-operasional/detail/' . $row['id']) ?>"
                                        class="btn btn-sm btn-outline-primary mb-1 font-weight-bold" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (in_groups('admin')) : ?>
                                        <a href="<?= base_url('manajemen-penjualan/pembelian-operasional/delete/' . $row['id']) ?>"
                                            class="btn btn-sm btn-outline-danger mb-1 font-weight-bold"
                                            onclick="return confirm('Yakin ingin menghapus pembelian ini?');"
                                            title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pembelian_operasional)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted font-italic">Tidak ada data pembelian.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>