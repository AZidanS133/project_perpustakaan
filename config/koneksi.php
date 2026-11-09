<?php
// Mulai session (dipakai untuk login)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== Pengaturan database =====
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_perpustakaan';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

// ===== Array =====
// Daftar kategori (dipakai di form tambah & edit)
$daftar_kategori = ['Fiksi', 'Non-Fiksi', 'Sains', 'Teknologi', 'Sejarah', 'Pendidikan', 'Agama', 'Lainnya'];

// Aturan upload sampul
$aturan_upload = [
    'ekstensi'  => ['jpg', 'jpeg', 'png'],
    'mime'      => ['image/jpeg', 'image/png'],
    'maks_byte' => 2 * 1024 * 1024, // 2 MB
];

// ===== Fungsi bantu =====

// Escape output agar aman dari XSS
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Hanya user yang sudah login boleh lewat
function wajib_login() {
    if (empty($_SESSION['login'])) {
        header('Location: index.php');
        exit;
    }
}

// Pesan sekali tampil (flash message)
function set_flash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// Menentukan label ketersediaan dengan if-else
function tentukan_status($stok) {
    $stok = (int)$stok;
    if ($stok > 0) {
        return 'Tersedia';
    } else {
        return 'Dipinjam';
    }
}

// Menentukan warna badge dengan switch
function warna_status($status) {
    switch ($status) {
        case 'Tersedia':
            return 'success';
        case 'Dipinjam':
            return 'warning';
        default:
            return 'secondary';
    }
}

// Upload sampul: mengembalikan [nama_file, error]
function upload_sampul($file) {
    global $aturan_upload;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload gagal (kode ' . $file['error'] . ').'];
    }
    if ($file['size'] > $aturan_upload['maks_byte']) {
        return [null, 'Ukuran sampul maksimal 2 MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $aturan_upload['ekstensi'])) {
        return [null, 'Sampul harus berformat .jpg atau .png.'];
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, $aturan_upload['mime'])) {
        return [null, 'Isi file bukan gambar JPG/PNG yang valid.'];
    }

    $nama = 'cover_' . uniqid() . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../uploads/' . $nama)) {
        return [null, 'Gagal menyimpan file ke folder uploads.'];
    }
    return [$nama, null];
}