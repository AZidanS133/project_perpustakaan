<?php
require 'config/koneksi.php';

// Proses login
if (!empty($_SESSION['login'])) {
    header('Location: dashboard.php');
    exit;
}

$flash = get_flash();
// Username dari cookie "Remember Me"
$username_cookie = $_COOKIE['remember_username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Mini DigitalLibrary</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="login-page">

<!-- Loader halaman -->
<div id="pageLoader"><div class="spinner-border text-light" style="width:3rem;height:3rem"></div></div>

<!-- Tombol mode gelap / terang -->
<button id="themeToggle" class="btn btn-light position-fixed top-0 end-0 m-3 rounded-circle" type="button"
        data-bs-toggle="tooltip" title="Ganti tema"></button>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-11 col-sm-8 col-md-6 col-lg-4">
      <div class="card login-card mt-5 fade-up">
        <div class="card-body p-4 pt-0">

          <div class="brand-icon"><i class="bi bi-book-half"></i></div>
          <h4 class="text-center fw-bold mb-0">Project DigitalLibrary</h4>
          <p class="text-center text-body-secondary small mb-4">Silahkan Masuk Untuk Melanjutkan</p>

          <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible alert-auto py-2 small" role="alert">
              <?= e($flash['msg']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <form action="proses_login.php" method="POST" class="needs-validation" novalidate>

            <div class="mb-3">
              <label class="form-label" for="username">Username</label>
              <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" name="username" id="username" class="form-control"
                       value="<?= e($username_cookie) ?>" autocomplete="username" required autofocus>
                <div class="invalid-feedback">Username wajib diisi.</div>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="password">Password</label>
              <div class="input-group has-validation">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="password" class="form-control"
                       minlength="6" autocomplete="current-password" required>
                <button class="btn btn-outline-secondary" type="button" id="togglePw"><i class="bi bi-eye"></i></button>
                <div class="invalid-feedback">Password wajib diisi (minimal 6 karakter).</div>
              </div>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="remember" id="remember"
                     <?= $username_cookie ? 'checked' : '' ?>>
              <label class="form-check-label small" for="remember">Remember Me</label>
            </div>

            <button class="btn btn-primary w-100 py-2" type="submit">
              <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>