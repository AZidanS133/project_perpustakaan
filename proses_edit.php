<?php
require 'config/koneksi.php';
wajib_login();

// Hanya menerima request POST dari form edit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// Ambil dan bersihkan input
$id       = (int)($_POST['id'] ?? 0);
$judul    = trim($_POST['judul'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$penulis  = trim($_POST['penulis'] ?? '');
$tahun    = filter_var($_POST['tahun_terbit'] ?? '', FILTER_VALIDATE_INT);
$stok     = filter_var($_POST['stok'] ?? '', FILTER_VALIDATE_INT);

$kembali = 'edit_buku.php?id=' . $id; // tujuan jika validasi gagal

// Validasi tipe data & isi
if ($judul === '' || $penulis === '') {
    set_flash('danger', 'Judul dan penulis wajib diisi.');
    header('Location: ' . $kembali);
    exit;
}
if (!in_array($kategori, $daftar_kategori)) {
    set_flash('danger', 'Kategori tidak valid.');
    header('Location: ' . $kembali);
    exit;
}
if ($tahun === false || $tahun < 1901 || $tahun > (int)date('Y')) {
    set_flash('danger', 'Tahun terbit harus berupa angka antara 1901 sampai ' . date('Y') . '.');
    header('Location: ' . $kembali);
    exit;
}
if ($stok === false || $stok < 0) {
    set_flash('danger', 'Stok harus berupa angka bulat 0 atau lebih.');
    header('Location: ' . $kembali);
    exit;
}

// Ambil nama sampul lama dari database
$stmt = mysqli_prepare($conn, 'SELECT sampul FROM buku WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$lama = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$lama) {
    set_flash('danger', 'Data buku tidak ditemukan.');
    header('Location: dashboard.php');
    exit;
}

// Sampul: pakai yang lama, kecuali ada file baru yang diunggah
$sampul = $lama['sampul'];
$ganti_sampul = !empty($_FILES['sampul']) && $_FILES['sampul']['error'] !== UPLOAD_ERR_NO_FILE;

if ($ganti_sampul) {
    [$nama_file, $error_upload] = upload_sampul($_FILES['sampul']); // validasi tipe & ukuran maks 2 MB
    if ($error_upload) {
        set_flash('danger', $error_upload);
        header('Location: ' . $kembali);
        exit;
    }
    $sampul = $nama_file;
}

// Status ditentukan otomatis dari stok (if-else)
$status = tentukan_status($stok);

// Update database (prepared statement)
$stmt = mysqli_prepare($conn,
    'UPDATE buku SET judul = ?, kategori = ?, penulis = ?, tahun_terbit = ?, stok = ?, status = ?, sampul = ? WHERE id = ?'
);
mysqli_stmt_bind_param($stmt, 'sssiissi', $judul, $kategori, $penulis, $tahun, $stok, $status, $sampul, $id);

if (mysqli_stmt_execute($stmt)) {
    // Update berhasil: hapus file sampul lama (hanya jika sampul diganti)
    if ($ganti_sampul) {
        $path_lama = __DIR__ . '/uploads/' . basename($lama['sampul']);
        if (is_file($path_lama)) {
            unlink($path_lama);
        }
    }
    set_flash('success', 'Data buku "' . $judul . '" berhasil diperbarui.');
    header('Location: dashboard.php');
} else {
    // Update gagal: hapus file baru yang sudah terlanjur diunggah
    if ($ganti_sampul) {
        $path_baru = __DIR__ . '/uploads/' . $sampul;
        if (is_file($path_baru)) {
            unlink($path_baru);
        }
    }
    set_flash('danger', 'Gagal memperbarui data buku.');
    header('Location: ' . $kembali);
}
exit;