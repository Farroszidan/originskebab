<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ==== AUTH ====
$routes->get('/', 'Home::index');
$routes->get('login', 'Auth::login', ['as' => 'login']);
$routes->post('login', 'Auth::attemptLogin');
$routes->get('register', 'Auth::registerForm', ['as' => 'register']);
$routes->post('register', 'Auth::register');

// ======= DASHBOARD ======= //
$routes->group('', ['filter' => 'role:admin,penjualan,produksi,keuangan'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
});


// ==== ADMIN ====
// $routes->group('admin', ['filter' => 'role:admin'], function ($routes) {
//     // Shift dan BTKL
//     $routes->get('input-shift', 'Admin::inputShift');
//     $routes->post('simpan-shift', 'Admin::simpanShift');
//     $routes->get('data-shift', 'Admin::dataShift');
//     $routes->get('delete-shift/(:num)', 'Admin::deleteShift/$1');
//     $routes->get('btkl', 'Admin::btkl');
//     $routes->post('btkl/hitung', 'Admin::hitungBtkl');

//     // Manajemen pengguna berdasarkan outlet
//     $routes->get('users-by-outlet/(:num)', 'Admin::getUsersByOutlet/$1');

//     // Cetak
//     $routes->get('cetak/(:num)', 'Admin::cetak/$1');
//     $routes->get('cetakSemua', 'Admin::cetakSemua');
// });

// ==== PENJUALAN DAN ADMIN ====
$routes->group('manajemen-penjualan', ['filter' => 'role:admin,penjualan'], function ($routes) {
    // Transaksi
    $routes->get('inputtransaksi', 'ManajemenPenjualan::input_transaksi');
    $routes->post('simpanTransaksi', 'ManajemenPenjualan::simpanTransaksi');
    $routes->get('generate-nomor-faktur', 'ManajemenPenjualan::generateNomorFaktur');
    $routes->get('daftar-transaksi', 'ManajemenPenjualan::daftarTransaksi');
    $routes->get('detail/(:num)', 'ManajemenPenjualan::detail/$1');
    $routes->get('hapus/(:num)', 'ManajemenPenjualan::hapus/$1');

    // Cetak Transaksi Penjualan
    $routes->get('cetak/(:num)', 'ManajemenPenjualan::cetak/$1');
    $routes->get('cetakSemua', 'ManajemenPenjualan::cetakSemua');
    $routes->post('cetak_terpilih', 'ManajemenPenjualan::cetakTerpilih');
    // $routes->get('cetak_laporan_penjualan', 'ManajemenPenjualan::cetakLaporanPenjualan');

    // Autocomplete
    $routes->get('searchKodeMenuAutocomplete', 'ManajemenPenjualan::searchKodeMenuAutocomplete');

    // Pembelian Operasional
    $routes->get('pembelian-operasional', 'ManajemenPenjualan::pembelian_operasional');
    $routes->get('pembelian-operasional/tambah', 'ManajemenPenjualan::tambah_pembelian_operasional');
    $routes->post('pembelian-operasional/simpan', 'ManajemenPenjualan::simpan_pembelian_operasional');
    $routes->get('pembelian-operasional/detail/(:num)', 'ManajemenPenjualan::detail_pembelian_operasional/$1');
    $routes->get('pembelian-operasional/delete/(:num)', 'ManajemenPenjualan::delete_pembelian_operasional/$1');

    // Persediaan outlet
    $routes->get('persediaanOutlet', 'ManajemenPenjualan::persediaanOutlet');
    $routes->post('tambahPersediaanOutlet', 'ManajemenPenjualan::tambahPersediaanOutlet');

    // Daftar permintaan
    $routes->get('permintaan', 'ManajemenPenjualan::permintaan');
    $routes->get('formPermintaan', 'ManajemenPenjualan::formPermintaan');
    $routes->post('storePermintaan', 'ManajemenPenjualan::storePermintaan');
    $routes->get('permintaan/detail/(:num)', 'ManajemenPenjualan::detailPermintaan/$1');
    $routes->post('hapus/(:num)', 'ManajemenPenjualan::hapusPermintaan/$1');

    // Manajemen Laporan Penjualan
    $routes->get('laporanPenjualan', 'ManajemenPenjualan::laporanPenjualan');
    $routes->post('simpanLaporanPenjualan', 'ManajemenPenjualan::simpanLaporanPenjualan');
    $routes->get('cetakLaporanPenjualan', 'ManajemenPenjualan::cetakLaporanPenjualan');

    // Laporan shift
    $routes->get('input-shift', 'ManajemenPenjualan::inputShift');
    $routes->get('data-shift', 'ManajemenPenjualan::dataShift'); // aslinya hanya admin
    $routes->post('simpan-shift', 'ManajemenPenjualan::simpanShift');

    // HPP Penjualan
    $routes->get('hppPenjualan', 'ManajemenPenjualan::hppPenjualan');
    $routes->post('simpanHppPenjualan', 'ManajemenPenjualan::simpanHppPenjualan');

    // Pegawai by outlet
    $routes->get('get-users/(:num)', 'ManajemenPenjualan::getUsersByOutlet/$1');

    $routes->match(['get', 'post'], 'btkl/form', 'ManajemenPenjualan::btklForm');

    $routes->get('btkl', 'ManajemenPenjualan::btkl');
});


// Grup hanya untuk admin
$routes->group('manajemen-penjualan', ['filter' => 'role:admin'], function ($routes) {
    // Master menu & varian
    $routes->get('master', 'ManajemenPenjualan::master');
    $routes->get('varianmenu', 'ManajemenPenjualan::varianmenu');
    $routes->get('varian_menu', 'ManajemenPenjualan::varianmenu');
    $routes->get('getVarianByKode/(:any)', 'ManajemenPenjualan::getVarianByKode/$1');

    $routes->post('tambahvarian', 'ManajemenPenjualan::tambahvarian');
    $routes->post('simpanVarianMenu', 'ManajemenPenjualan::simpanVarianMenu');
    $routes->post('updateVarianMenu/(:num)', 'ManajemenPenjualan::updateVarianMenu/$1');
    $routes->get('hapusVarianMenu/(:num)', 'ManajemenPenjualan::hapusVarianMenu/$1');

    // Menu penjualan
    $routes->post('simpanMenuPenjualan', 'ManajemenPenjualan::simpanMenuPenjualan');
    $routes->post('editMenuPenjualan/(:num)', 'ManajemenPenjualan::editMenuPenjualan/$1');
    $routes->match(['get', 'post'], 'hapusMenuPenjualan/(:num)', 'ManajemenPenjualan::hapusMenuPenjualan/$1');

    // BTKL dan delete shift (khusus admin)
    $routes->get('delete-shift/(:num)', 'ManajemenPenjualan::deleteShift/$1');

    // Outlet
    $routes->get('inputOutlet', 'ManajemenPenjualan::inputOutlet');
    $routes->post('simpanOutlet', 'ManajemenPenjualan::simpanOutlet');
    $routes->get('hapusOutlet/(:num)', 'ManajemenPenjualan::hapusOutlet/$1');
    // Jam Shift
    $routes->get('inputJamShift', 'ManajemenPenjualan::inputJamShift');
    $routes->post('simpanJamShift', 'ManajemenPenjualan::simpanJamShift');
    $routes->get('hapusJamShift/(:num)', 'ManajemenPenjualan::hapusJamShift/$1');

    // Hapus Laporan Penjualan
    $routes->post('hapus-laporan', 'ManajemenPenjualan::hapusLaporan');
});

// Grup untuk admin dan keuangan
$routes->group('manajemen-penjualan', ['filter' => 'role:admin,keuangan'], function ($routes) {
    $routes->get('input-shift', 'ManajemenPenjualan::inputShift');
    $routes->get('data-shift', 'ManajemenPenjualan::dataShift');
    $routes->post('simpan-shift', 'ManajemenPenjualan::simpanShift');
    $routes->get('btkl', 'ManajemenPenjualan::btkl');


    $routes->get('hppPenjualan', 'ManajemenPenjualan::hppPenjualan');

    $routes->get('admin/dashboard', 'Admin::dashboard');
});
// ==== LAPORAN PERSEDIAAN - MULTI ROLE ====
$routes->group('admin/persediaan', ['filter' => 'role:admin,penjualan,keuangan'], function ($routes) {
    $routes->match(['get', 'post'], 'rekapStokHarian', 'Persediaan::rekapStokHarian');
    $routes->get('laporanHarian', 'Persediaan::laporanHarian');
    $routes->get('cetakLaporan', 'Persediaan::cetakLaporan');
    $routes->post('hapusLaporanHarian', 'Persediaan::hapusLaporanHarian');
});

// ==== TRANSAKSI DAN FORM MULTI-FORM ====
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('manajemen-transaksi', 'Transaksi::index');
    $routes->post('transaksi/store', 'Transaksi::store');
    $routes->get('transaksi/detail/(:segment)/(:num)', 'Transaksi::detail/$1/$2');
    $routes->get('transaksi/usersByOutlet/(:num)', 'Transaksi::usersByOutlet/$1');
});

// ==== NOTIFIKASI ====
$routes->get('notifikasi/pesan_masuk', 'Notifikasi::pesan_masuk');
$routes->get('notifikasi/baca/(:num)', 'Notifikasi::baca/$1');
$routes->get('notifikasi/ajax', 'Notifikasi::ajax');
$routes->get('notifikasi/tandai_semua', 'Notifikasi::tandai_semua');
$routes->get('notifikasi/detail/(:num)', 'Notifikasi::detail/$1');

// KEUANGAN
$routes->get('dashboard/index', 'Dashboard::index');
$routes->get('keuangan/isi-kas', 'Keuangan::isiKas', ['filter' => 'role:keuangan']);
$routes->post('keuangan/isi-kas', 'Keuangan::simpanIsiKas', ['filter' => 'role:keuangan']);


$routes->add('keuangan/index', 'Keuangan::index_jurnal', ['filter' => 'role:keuangan']); // menampilkan daftar jurnal
$routes->get('/keuangan/create_jurnal', 'Keuangan::create_jurnal', ['filter' => 'role:keuangan']); //menampilkan menambahkan jurnal
$routes->post('keuangan/simpan_jurnal', 'Keuangan::simpan_jurnal', ['filter' => 'role:keuangan']); //menyimpan jurnal
$routes->get('/keuangan/create_akun', 'Keuangan::create_akun', ['filter' => 'role:keuangan']); //menambahkan akun
$routes->post('keuangan/save_akun', 'Keuangan::save_akun');
$routes->get('keuangan/akun', 'Keuangan::daftar_akun');
$routes->get('keuangan/edit_akun/(:num)', 'Keuangan::edit_akun/$1');
$routes->post('keuangan/update_akun/(:num)', 'Keuangan::update_akun/$1');
$routes->post('keuangan/delete_akun/(:num)', 'Keuangan::delete_akun/$1');
$routes->post('/jurnal/store', 'Jurnal::store');
$routes->add('/keuangan/neraca_saldo', 'Keuangan::neraca_saldo', ['filter' => 'role:keuangan']); // menampilkan neraca saldo
$routes->get('keuangan/laporan_utang', 'Keuangan::laporanUtang');
$routes->get('keuangan/laporan_piutang', 'Keuangan::laporanPiutang');
$routes->get('keuangan/export_pdf_piutang', 'Keuangan::export_pdf_piutang');
$routes->get('keuangan/export_pdf_utang', 'Keuangan::export_pdf_utang');
$routes->get('keuangan/form_pelunasan_utang/(:segment)', 'Keuangan::formPelunasanUtang/$1');
$routes->post('keuangan/simpan_pelunasan_utang', 'Keuangan::simpanPelunasanUtang');
$routes->get('keuangan/form_pelunasan_piutang/(:segment)', 'Keuangan::formPelunasanPiutang/$1');
$routes->post('keuangan/simpan_pelunasan_piutang', 'Keuangan::simpanPelunasanPiutang');
$routes->add('/keuangan/laba_rugi', 'Keuangan::laba_rugi', ['filter' => 'role:keuangan']); // menampilkan laporan laba rugi
$routes->get('keuangan/laba_rugi/pdf', 'Keuangan::exportLabaRugiPDF'); //ekspor PDF laba rugi
$routes->get('keuangan/laporan_perubahan_ekuitas', 'Keuangan::laporanPerubahanEkuitas');
$routes->get('keuangan/perubahan_ekuitas_pdf', 'Keuangan::exportPerubahanEkuitasPDF');
$routes->get('keuangan/laporan_neraca', 'Keuangan::laporanNeraca');
$routes->get('keuangan/exportNeracaPDF', 'Keuangan::exportNeracaPDF');
$routes->get('keuangan/arus_kas', 'Keuangan::laporanArusKas', ['filter' => 'role:keuangan']);
$routes->get('keuangan/arus_kas_pdf', 'Keuangan::arusKasPdf', ['filter' => 'role:keuangan']); // jika export PDF ingin digunakan
$routes->get('keuangan/neraca_saldo_pdf', 'Keuangan::exportNeracaSaldoPDF', ['filter' => 'role:keuangan']);

// ADMIN PEMASOK
$routes->get('admin/pemasok', 'Admin::pemasok', ['filter' => 'role:admin']);
$routes->get('admin/pemasok/create', 'Admin::createPemasok', ['filter' => 'role:admin']);
$routes->post('admin/pemasok/tambah', 'Admin::tambahPemasok', ['filter' => 'role:admin']);
$routes->get('admin/pemasok/edit/(:num)', 'Admin::editPemasok/$1', ['filter' => 'role:admin']);
$routes->post('admin/pemasok/update/(:num)', 'Admin::updatePemasok/$1', ['filter' => 'role:admin']);
$routes->get('admin/pemasok/delete/(:num)', 'Admin::hapusPemasok/$1', ['filter' => 'role:admin']);

//ADMIN BIAYA
$routes->get('admin/biaya/view_tenaker', 'Admin::biayaTNK', ['filter' => 'role:admin']);
$routes->get('admin/biaya/view_bop', 'Admin::biayaBOP', ['filter' => 'role:admin']);
$routes->post('admin/biaya/simpan', 'Admin::simpanTNK', ['filter' => 'role:admin']);
$routes->post('admin/biaya/simpanBOP', 'Admin::simpanBOP', ['filter' => 'role:admin']);
$routes->get('admin/biaya/delete/(:num)', 'Admin::hapusTNK/$1', ['filter' => 'role:admin']);
$routes->get('admin/biaya/deleteBOP/(:num)', 'Admin::hapusBOP/$1', ['filter' => 'role:admin']);

//ADMIN KOMPOSISI
$routes->get('admin/komposisi', 'Admin::komposisiIndex', ['filter' => 'role:admin']);
$routes->get('admin/komposisi/tambah', 'Admin::komposisiTambah', ['filter' => 'role:admin']);
$routes->post('admin/komposisi/simpan', 'Admin::komposisiSimpan', ['filter' => 'role:admin']);
$routes->get('admin/komposisi/edit/(:num)', 'Admin::editKomposisi/$1', ['filter' => 'role:admin']);
$routes->post('admin/komposisi/update', 'Admin::updateKomposisi', ['filter' => 'role:admin']);
$routes->get('admin/komposisi/hapus/(:num)', 'Admin::hapusKomposisi/$1', ['filter' => 'role:admin']);

// ADMIN PERINTAH KERJA PRODUKSI BSJ
$routes->get('admin/perintah-kerja', 'Admin::perintahKerjaIndex', ['filter' => 'role:admin']);
$routes->get('admin/perintah-kerja/input', 'Admin::perintahKerjaInput', ['filter' => 'role:admin']);
$routes->post('admin/perintah-kerja/simpan', 'Admin::perintahKerjaSimpan', ['filter' => 'role:admin']);
$routes->get('admin/perintah-kerja/detail/(:num)', 'Admin::perintahKerjaDetail/$1', ['filter' => 'role:admin']);
$routes->get('admin/perintah-kerja/hapus/(:num)', 'Admin::perintahKerjaHapus/$1', ['filter' => 'role:admin']);

// PRODUKSI PEMBELIAN
$routes->group('produksi', ['filter' => 'role:produksi,admin'], function ($routes) {
    $routes->add('/', 'Produksi::index');
    $routes->get('pembelian', 'Produksi::pembelian');
    $routes->get('pembelian/create', 'Produksi::createPembelian');
    $routes->post('pembelian/simpan-pembelian', 'Produksi::simpanPembelian');
    $routes->get('pembelian/delete/(:num)', 'Produksi::hapusPembelian/$1');
    $routes->get('pembelian/detail/(:num)', 'Produksi::detailPembelian/$1');
    $routes->get('pembelian/edit/(:num)', 'Produksi::editPembelian/$1');
    $routes->post('pembelian/update/(:num)', 'Produksi::updatePembelian/$1');
    $routes->get('pembelian/update-status/(:num)/(:any)', 'Produksi::updateStatusPembelian/$1/$2');
});

// PRODUKSI PERSEDIAAN
//BAHAN MENTAH
// PRODUKSI PERSEDIAAN
$routes->group('produksi/persediaan', ['filter' => 'role:produksi,admin'], function ($routes) {
    $routes->get('/', 'Produksi::bahan');
    $routes->get('create', 'Produksi::create');
    $routes->post('simpan', 'Produksi::simpanBahan');
    $routes->get('edit/(:num)', 'Produksi::editBahan/$1');
    $routes->post('update/(:num)', 'Produksi::updateBahan/$1');
    $routes->get('delete/(:num)', 'Produksi::hapusBahan/$1');

    // Kartu Persediaan Bahan
    $routes->get('kartu/bahan', 'Produksi::kartuPersediaanBahan');
    // Kartu Persediaan BSJ
    $routes->get('kartu/bsj', 'Produksi::kartuPersediaanBSJ');
});
//BSJ
$routes->group('produksi/persediaan/bsj', ['filter' => 'role:produksi,admin'], function ($routes) {
    $routes->get('/', 'Produksi::bsj');
    $routes->get('tambah', 'Produksi::tambahBSJ');
    $routes->post('simpan', 'Produksi::simpanBSJ');
    $routes->get('edit/(:num)', 'Produksi::editBSJ/$1');
    $routes->post('update/(:num)', 'Produksi::updateBSJ/$1');
    $routes->get('delete/(:num)', 'Produksi::hapusBSJ/$1');
});


// PRODUKSI PRODUKSI
$routes->group('produksi/produksi', ['filter' => 'role:produksi,admin'], function ($routes) {
    $routes->get('input', 'Produksi::inputProduksi');
    $routes->post('simpan', 'Produksi::simpanProduksi');
    $routes->get('daftar', 'Produksi::daftarProduksi');
    $routes->get('detail/(:num)', 'Produksi::detailProduksi/$1');
    $routes->get('hapus/(:num)', 'Produksi::hapusProduksi/$1');
    $routes->get('updateStatus/(:num)/(:any)', 'Produksi::updateStatusProduksi/$1/$2');
});

// PRODUKSI PENGIRIMAN
$routes->group('produksi/pengiriman', ['filter' => 'role:produksi,admin'], function ($routes) {
    $routes->get('/', 'Produksi::pengirimanIndex');
    $routes->get('form-pengiriman', 'Produksi::pengirimanInput');
    $routes->post('form-pengiriman/store', 'Produksi::pengirimanSimpan');
    $routes->get('detail/(:num)', 'Produksi::pengirimanDetail/$1');
    $routes->match(['get', 'post'], 'hapus/(:num)', 'Produksi::hapusPengiriman/$1');
});

$routes->group('produksi/laporan', ['filter' => 'role:produksi,admin'], function ($routes) {
    // 🔹 Laporan Pembelian
    $routes->get('form_cetak_pembelian', 'Produksi::formCetakPembelian');
    $routes->get('cetak_pembelian', 'Produksi::cetakPembelian');

    // 🔹 Laporan Persediaan Bahan
    $routes->get('form_cetak_persediaan_bahan', 'Produksi::formCetakPersediaanBahan');
    $routes->get('cetak_persediaan_bahan', 'Produksi::cetakPersediaanBahan');

    // 🔹 Laporan Persediaan BSJ
    $routes->get('form_cetak_persediaan_bsj', 'Produksi::formCetakPersediaanBSJ');
    $routes->get('cetak_persediaan_bsj', 'Produksi::cetakPersediaanBSJ');

    // 🔹 Laporan Produksi
    $routes->get('form_cetak_produksi', 'Produksi::formCetakProduksi');
    $routes->get('cetak_produksi', 'Produksi::cetakProduksi');

    // 🔹 Kartu Persediaan Bahan (bukan cetak tapi laporan)
    $routes->get('kartu_persediaan_bahan', 'Produksi::kartuPersediaanBahan');
});


$routes->group('produksi/hpp', ['filter' => 'role:produksi,admin'], function ($routes) {
    $routes->get('form', 'Produksi::formHPP');
    $routes->post('simpan', 'Produksi::simpanHPP');
    $routes->get('/', 'Produksi::indexHPP');
});

$routes->add('penjualan', 'Penjualan::index', ['filter' => 'role:penjualan']);
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'role:admin,penjualan,keuangan,produksi']);
