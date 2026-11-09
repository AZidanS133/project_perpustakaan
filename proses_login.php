<?php
require 'config/koneksi.php';

// Hanya menerima request POST dari form login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi input kosong
if ($username === '' || $password === '') {
    set_flash('danger', 'Username dan password wajib diisi.');
    header('Location: index.php');
    exit;
}

// Cari user di database (prepared statement)
$stmt = mysqli_prepare($conn, 'SELECT id, username, password, nama_lengkap FROM users WHERE username = ?');
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Cek password dengan password_verify
if ($user && password_verify($password, $user['password'])) {

    session_regenerate_id(true); // cegah session fixation

    // Simpan data login ke SESSION
    $_SESSION['login']    = true;
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nama']     = $user['nama_lengkap'];

    // Cookie "Remember Me": simpan username selama 7 hari
    if (!empty($_POST['remember'])) {
        setcookie('remember_username', $user['username'], time() + (7 * 24 * 3600), '/', '', false, true);
    } else {
        setcookie('remember_username', '', time() - 3600, '/'); // hapus cookie
    }

    header('Location: dashboard.php');
    exit;
}

// Login gagal
set_flash('danger', 'Username atau password salah.');
header('Location: index.php');
exit;