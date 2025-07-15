<?= $this->extend('auth/templates/indexs'); ?>

<?= $this->section('content'); ?>
<div class="container">

    <div class="card o-hidden border-0 shadow-lg my-5">
        <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
                <div class="col-lg-5 d-none d-lg-block bg-register-image"></div>
                <div class="col-lg-7">
                    <div class="p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4"><?= lang('Auth.register') ?></h1>
                        </div>

                        <?= view('Myth\Auth\Views\_message_block') ?>

                        <form action="<?= url_to('register') ?>" method="post" class="user">
                            <?= csrf_field() ?>
                            <div class="form-group">
                                <input type="text" class="form-control form-control-user <?php if (session('errors.username')) : ?>is-invalid<?php endif ?>" name="username"
                                    placeholder="<?= lang('Auth.username') ?>" value="<?= old('username') ?>">
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control form-control-user <?php if (session('errors.email')) : ?>is-invalid<?php endif ?>" name="email"
                                    placeholder="<?= lang('Auth.email') ?>" value="<?= old('email') ?>">
                            </div>
                            <div class="form-group">
                                <select name="role" id="role" class="form-control <?php if (session('errors.role')) : ?>is-invalid<?php endif ?>">
                                    <option value="">-- Pilih Role --</option>
                                    <option value="penjualan" <?= old('role') == 'penjualan' ? 'selected' : '' ?>>Penjualan</option>
                                    <option value="produksi" <?= old('role') == 'produksi' ? 'selected' : '' ?>>Produksi</option>
                                    <option value="keuangan" <?= old('role') == 'keuangan' ? 'selected' : '' ?>>Keuangan</option>
                                </select>
                            </div>
                            <div class="form-group" id="outlet-field" style="display: none;">
                                <select name="outlet_id" class="form-control <?php if (session('errors.outlet_id')) : ?>is-invalid<?php endif ?>">
                                    <option value="">-- Pilih Outlet --</option>
                                    <?php foreach ($outlets as $outlet): ?>
                                        <option value="<?= $outlet['id'] ?>" <?= old('outlet_id') == $outlet['id'] ? 'selected' : '' ?>>
                                            <?= esc($outlet['nama_outlet']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input type="password" class="form-control form-control-user <?php if (session('errors.password')) : ?>is-invalid<?php endif ?>"
                                        name="password" placeholder="<?= lang('Auth.password') ?>" autocomplete="off">
                                </div>
                                <div class="col-sm-6">
                                    <input type="password" class="form-control form-control-user <?php if (session('errors.pass_confirm')) : ?>is-invalid<?php endif ?>"
                                        name="pass_confirm" placeholder="<?= lang('Auth.repeatPassword') ?>" autocomplete="off">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                <?= lang('Auth.register') ?>
                            </button>
                        </form>
                        <hr>
                        <div class="text-center">
                            <p><?= lang('Auth.alreadyRegistered') ?><a class="small" href="<?= url_to('login') ?>"><?= lang('Auth.signIn') ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ========== SKRIPT MEMUNCULKAN FIELD OUTLET JIKA ROLE PENJUALAN =========== -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.querySelector('select[name="role"]');
        const outletField = document.getElementById('outlet-field');

        function toggleOutletField() {
            outletField.style.display = roleSelect.value === 'penjualan' ? 'block' : 'none';
        }

        roleSelect.addEventListener('change', toggleOutletField);
        toggleOutletField();
    });
</script>
<?= $this->endSection(); ?>