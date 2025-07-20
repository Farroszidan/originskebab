<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Pembelian</h6>
        </div>
        <div class="card-body">
            <form action="<?= base_url('produksi/pembelian/update/' . $pembelian['id']) ?>" method="post">
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="no_nota" class="col-form-label">No. Nota</label>
                        <input type="text" name="no_nota" class="form-control" value="<?= esc($pembelian['no_nota']) ?>" required>
                    </div>
                </div>
                <hr>
                <h5 class="mb-3">Edit Jumlah Bahan</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nama Bahan</th>
                                <th>Satuan</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detail as $item): ?>
                                <tr>
                                    <td><?= esc($item['nama']) ?></td>
                                    <td><?= esc($item['satuan']) ?></td>
                                    <td>
                                        <?php
                                        $satuan = strtolower($item['satuan']);
                                        $jumlah = $item['jumlah'];
                                        if ($satuan === 'kg') {
                                            $jumlah = $jumlah / 1000;
                                        } elseif ($satuan === 'liter') {
                                            $jumlah = $jumlah / 1000;
                                        }
                                        ?>
                                        <input type="number" step="any" name="jumlah[<?= $item['id'] ?>]" class="form-control" value="<?= $jumlah ?>" min="0" required>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
                <div class="form-group row">
                    <div class="col-sm-10 offset-sm-2">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="<?= base_url('produksi/pembelian') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>