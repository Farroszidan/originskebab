<?php $this->extend('templates/index_templates_general'); ?>
<?php $this->section('page-content'); ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Perhitungan HPP per BSJ</h1>
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success"> <?= session()->getFlashdata('success'); ?> </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger"> <?= session()->getFlashdata('error'); ?> </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar HPP Produksi BSJ</h6>
            <a href="<?= base_url('produksi/hpp/form'); ?>" class="btn btn-success btn-sm float-right">Hitung HPP</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Produksi</th>
                            <th>Tanggal</th>
                            <th>Nama BSJ</th>
                            <th>Jumlah Produksi</th>
                            <th>Total Biaya</th>
                            <th>HPP per BSJ</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($hpp_list)) : ?>
                            <?php $no = 1;
                            foreach ($hpp_list as $hpp) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= esc($hpp['kode_produksi']); ?></td>
                                    <td><?= date('d-m-Y', strtotime($hpp['tanggal'])); ?></td>
                                    <td><?= esc($hpp['nama_bsj']); ?></td>
                                    <td><?= esc($hpp['jumlah_produksi']); ?></td>
                                    <td>Rp <?= number_format($hpp['total_biaya'], 0, ',', '.'); ?></td>
                                    <td>Rp <?= number_format($hpp['hpp_per_unit'], 0, ',', '.'); ?></td>
                                    <td><?= esc($hpp['keterangan']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data HPP.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>