<?= $this->extend('templates/index_templates_general'); ?>
<?= $this->section('page-content'); ?>
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>

    <?php
    // Ambil role dan outlet_id
    $user = user(); // Dari Myth:Auth
    $groupModel = new \Myth\Auth\Authorization\GroupModel();
    $roles = $groupModel->getGroupsForUser($user->id);
    $role = $roles[0] ?? 'unknown';
    $outlet_id = $user->outlet_id ?? null;
    ?>

    <?php if ($role == 'admin'): ?>
        <p>Selamat datang, Admin. Ini adalah dashboard untuk admin.</p>
        <!-- Tambahkan konten dashboard khusus admin -->

    <?php elseif ($role == 'penjualan'): ?>
        <p>Selamat datang, Penjualan (Outlet ID: <?= $outlet_id ?>)</p>
        <?php if ($outlet_id == 1): ?>
            <p>Ini dashboard untuk outlet 1.</p>
            <!-- Konten untuk outlet 1 -->
        <?php elseif ($outlet_id == 2): ?>
            <p>Ini dashboard untuk outlet 2.</p>
            <!-- Konten untuk outlet 2 -->
        <?php else: ?>
            <p>Dashboard penjualan umum.</p>
            <!-- Konten default jika outlet tidak dikenali -->
        <?php endif; ?>

    <?php elseif ($role == 'produksi'): ?>
        <p>Selamat datang, Tim Produksi.</p>
        <!-- Konten dashboard produksi -->

    <?php elseif ($role == 'keuangan'): ?>
        <p>Selamat datang, Tim Keuangan.</p>
        <!-- Konten dashboard keuangan -->

    <?php else: ?>
        <p>Role tidak dikenali.</p>
    <?php endif; ?>

</div>
<?= $this->endSection(); ?>