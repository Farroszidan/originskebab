<?= $this->extend('templates/index_templates_general') ?>
<?= $this->section('page-content') ?>

<div class="container mt-4">
    <h4>Daftar Akun</h4>
    <a href="<?= base_url('keuangan/create_akun') ?>" class="btn btn-success mb-3">+ Tambah Akun</a>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Kode Akun</th>
                    <th>Nama Akun</th>
                    <th>Jenis Akun</th>
                    <th>Tipe</th>
                    <th class="text-right">Saldo Awal (Rp)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($akun) && is_array($akun)): ?>
                    <?php foreach ($akun as $a): ?>
                        <tr>
                            <td><?= esc($a['kode_akun']) ?></td>
                            <td><?= esc($a['nama_akun']) ?></td>
                            <td><?= esc($a['jenis_akun']) ?></td>
                            <td><?= esc($a['tipe']) ?></td>
                            <td class="text-right"><?= number_format($a['saldo_awal'], 2, ',', '.') ?></td>
                            <td>
                                <a href="<?= base_url('keuangan/edit_akun/' . $a['id']) ?>" class="btn btn-sm btn-primary">Edit</a>
                                <form action="<?= base_url('keuangan/delete_akun/' . $a['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus akun ini?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data akun.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>