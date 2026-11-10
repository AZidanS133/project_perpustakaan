<?php
require 'config/koneksi.php';
wajib_login();

// Hanya menerima request POST dari form tambah
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// Ambil dan bersihkan input
$judul    = trim($_POST['judul'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$penulis  = trim($_POST['penulis'] ?? '');
$tahun    = filter_var($_POST['tahun_terbit'] ?? '', FILTER_VALIDATE_INT);
$stok     = filter_var($_POST['stok'] ?? '', FILTER_VALIDATE_INT);

// Validasi tipe data & isi
if ($judul === '' || $penulis === '') {
    set_flash('danger', 'Judul dan penulis wajib diisi.');
    header('Location: tambah_buku.php');
    exit;
}
if (!in_array($kategori, $daftar_kategori)) {
    set_flash('danger', 'Kategori tidak valid.');
    header('Location: tambah_buku.php');
    exit;
}
if ($tahun === false || $tahun < 1901 || $tahun > (int)date('Y')) {
    set_flash('danger', 'Tahun terbit harus berupa angka antara 1901 sampai ' . date('Y') . '.');
    header('Location: tambah_buku.php');
    exit;
}
if ($stok === false || $stok < 0) {
    set_flash('danger', 'Stok harus berupa angka bulat 0 atau lebih.');
    header('Location: tambah_buku.php');
    exit;
}

// Sampul wajib diunggah
if (empty($_FILES['sampul']) || $_FILES['sampul']['error'] === UPLOAD_ERR_NO_FILE) {
    set_flash('danger', 'Sampul buku wajib diunggah.');
    header('Location: tambah_buku.php');
    exit;
}

// Upload file (validasi ekstensi, isi file, dan ukuran maks 2 MB ada di fungsi ini)
[$nama_file, $error_upload] = upload_sampul($_FILES['sampul']);
if ($error_upload) {
    set_flash('danger', $error_upload);
    header('Location: tambah_buku.php');
    exit;
}

// Status ditentukan otomatis dari stok (if-else)
$status = tentukan_status($stok);

// Simpan ke database (prepared statement)
$stmt = mysqli_prepare($conn,
    'INSERT INTO buku (judul, kategori, penulis, tahun_terbit, stok, status, sampul) VALUES (?, ?, ?, ?, ?, ?, ?)'
);
mysqli_stmt_bind_param($stmt, 'sssiiss', $judul, $kategori, $penulis, $tahun, $stok, $status, $nama_file);

if (mysqli_stmt_execute($stmt)) {
    set_flash('success', 'Buku "' . $judul . '" berhasil ditambahkan.');
    header('Location: dashboard.php');
} else {
    // Query gagal: hapus file yang sudah terlanjur diunggah
    $path = __DIR__ . '/uploads/' . $nama_file;
    if (is_file($path)) {
        unlink($path);
    }
    set_flash('danger', 'Gagal menyimpan data buku.');
    header('Location: tambah_buku.php');
}
exit;