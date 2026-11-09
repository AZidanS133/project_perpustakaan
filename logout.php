<?php
require 'config/koneksi.php';

// 1. Kosongkan semua data session
$_SESSION = [];

// 2. Hapus cookie session di browser
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// 3. Hancurkan session di server
session_destroy();

// 4. Hapus cookie "Remember Me"
setcookie('remember_username', '', time() - 3600, '/');

// 5. Mulai session baru hanya untuk menampilkan pesan logout
session_start();
set_flash('success', 'Anda berhasil logout.');

header('Location: index.php');
exit;