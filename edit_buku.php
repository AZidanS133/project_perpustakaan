<?php
require 'config/koneksi.php';
wajib_login();

// Ambil id dari URL, lalu cari datanya
$id = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, 'SELECT * FROM buku WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$b = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Kalau data tidak ada, kembali ke dashboard
if (!$b) {
    set_flash('danger', 'Data buku tidak ditemukan.');
    header('Location: dashboard.php');
    exit;
}

$flash = get_flash();
$status_awal = tentukan_status($b['stok']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Buku | Mini DigitalLibrary</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="css/style.css?v=2" rel="stylesheet">
</head>
<body class="app-page">

<div id="pageLoader"><div class="spinner-border text-light" style="width:3rem;height:3rem"></div></div>

<nav class="navbar navbar-dark navbar-app shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard</a>
    <button id="themeToggle" class="btn btn-sm btn-light" type="button" data-bs-toggle="tooltip" title="Ganti tema"></button>
  </div>
</nav>

<div class="container py-4">
  <div class="card card-form fade-up">
    <div class="card-body p-4">
      <h5 class="fw-bold mb-4"><i class="bi bi-pencil-square me-2"></i>Edit Buku</h5>

      <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible alert-auto" role="alert">
          <?= e($flash['msg']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <form action="proses_edit.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">

        <div class="row g-4">

          <!-- Kolom kiri: sampul -->
          <div class="col-md-4 text-center">
            <img id="preview" src="uploads/<?= e($b['sampul']) ?>" alt="Sampul <?= e($b['judul']) ?>">

            <label for="sampul" class="dropzone">
              <i class="bi bi-cloud-arrow-up fs-3 d-block"></i>
              <span id="namaFile">Seret &amp; lepas gambar baru, atau klik</span>
            </label>

            <div class="mt-3 text-start">
              <label class="form-label" for="sampul">Ganti Sampul <small class="text-body-secondary">(opsional, .jpg / .png, maks 2 MB)</small></label>
              <input type="file" name="sampul" id="sampul" class="form-control" accept=".jpg,.jpeg,.png">
              <div class="form-text">Kosongkan jika tidak ingin mengganti sampul.</div>
            </div>
          </div>

          <!-- Kolom kanan: data buku -->
          <div class="col-md-8">
            <div class="mb-3">
              <label class="form-label" for="judul">Judul Buku</label>
              <input type="text" name="judul" id="judul" class="form-control" maxlength="150"
                     value="<?= e($b['judul']) ?>" required>
              <div class="invalid-feedback">Judul wajib diisi.</div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label" for="kategori">Kategori</label>
                <select name="kategori" id="kategori" class="form-select" required>
                  <?php foreach ($daftar_kategori as $k): ?>
                    <option value="<?= e($k) ?>" <?= $k === $b['kategori'] ? 'selected' : '' ?>><?= e($k) ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Pilih salah satu kategori.</div>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label" for="tahun_terbit">Tahun Terbit</label>
                <input type="number" name="tahun_terbit" id="tahun_terbit" class="form-control"
                       min="1901" max="<?= date('Y') ?>" value="<?= e($b['tahun_terbit']) ?>" required>
                <div class="invalid-feedback">Isi tahun antara 1901 sampai <?= date('Y') ?>.</div>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="penulis">Penulis</label>
              <input type="text" name="penulis" id="penulis" class="form-control" maxlength="100"
                     value="<?= e($b['penulis']) ?>" required>
              <div class="invalid-feedback">Penulis wajib diisi.</div>
            </div>

            <div class="row mb-4">
              <div class="col-md-6">
                <label class="form-label" for="stok">Stok</label>
                <input type="number" name="stok" id="stok" class="form-control" min="0"
                       value="<?= (int)$b['stok'] ?>" required>
                <div class="invalid-feedback">Stok harus berupa angka 0 atau lebih.</div>
              </div>
              <div class="col-md-6 mt-3 mt-md-0">
                <label class="form-label d-block">Status Ketersediaan</label>
                <span id="infoStatus" class="badge fs-6 text-bg-<?= warna_status($status_awal) ?>"><?= e($status_awal) ?></span>
                <div class="form-text">Otomatis: stok &gt; 0 = Tersedia, stok 0 = Dipinjam.</div>
              </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Perbarui</button>
            <a href="dashboard.php" class="btn btn-outline-secondary">Batal</a>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js?v=2"></script>
</body>
</html>