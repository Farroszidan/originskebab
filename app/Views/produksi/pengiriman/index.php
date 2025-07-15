<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Daftar Pengiriman Barang ke Outlet</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success'); ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error'); ?>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">
            <a href="<?= base_url('produksi/pengiriman/form-pengiriman'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Input Pengiriman
            </a>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Outlet</th>
                        <th>Jumlah Item</th>
                        <th>Daftar Barang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pengiriman)): ?>
                        <?php $no = 1;
                        foreach ($pengiriman as $p): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= date('d-m-Y', strtotime($p['tanggal'])); ?></td>
                                <td><?= esc($p['outlet_nama'] ?? '-'); ?></td>
                                <td><?= isset($p['barang']) ? count($p['barang']) : 0; ?></td>
                                <td>
                                    <?php if (!empty($p['barang'])): ?>
                                        <ul class="mb-0 pl-3">
                                            <?php foreach ($p['barang'] as $b): ?>
                                                <li><?= esc($b['nama']) . ' (' . ($b['jumlah'] + 0) . ' ' . esc($b['satuan']) . ')'; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('produksi/pengiriman/detail/' . $p['id']); ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <form action="<?= base_url('produksi/pengiriman/hapus/' . $p['id']); ?>" method="post" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus data pengiriman ini? Semua detail barang juga akan terhapus!');">
                                        <?= csrf_field(); ?>
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data pengiriman.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>