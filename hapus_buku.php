<?php
require 'config/koneksi.php';
wajib_login();

// Hanya menerima request POST (dari modal konfirmasi di dashboard)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

// 1. Ambil nama file sampul sebelum datanya dihapus
$stmt = mysqli_prepare($conn, 'SELECT judul, sampul FROM buku WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$b = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$b) {
    set_flash('danger', 'Data buku tidak ditemukan.');
    header('Location: dashboard.php');
    exit;
}

// 2. Hapus data dari database
$stmt = mysqli_prepare($conn, 'DELETE FROM buku WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    // 3. Hapus file sampul dari folder uploads (unlink)
    $path = __DIR__ . '/uploads/' . basename($b['sampul']);
    if (is_file($path)) {
        unlink($path);
    }
    set_flash('success', 'Buku "' . $b['judul'] . '" berhasil dihapus.');
} else {
    set_flash('danger', 'Gagal menghapus data buku.');
}

header('Location: dashboard.php');
exit;