<?php
require 'config/koneksi.php';
wajib_login(); // hanya user yang sudah login

$flash = get_flash();

// Ambil semua data buku
$hasil = mysqli_query($conn, 'SELECT * FROM buku ORDER BY id DESC');
$buku  = mysqli_fetch_all($hasil, MYSQLI_ASSOC);

// Hitung statistik (status ditentukan dari stok)
$total = count($buku);
$tersedia = 0;
foreach ($buku as $b) {
    if (tentukan_status($b['stok']) === 'Tersedia') {
        $tersedia++;
    }
}
$dipinjam = $total - $tersedia;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard | Mini DigitalLibrary</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="app-page">

<div id="pageLoader"><div class="spinner-border text-light" style="width:3rem;height:3rem"></div></div>

<nav class="navbar navbar-dark navbar-app shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-book-half me-2"></i>Mini DigitalLibrary</a>
    <div class="d-flex align-items-center text-white gap-2">
      <span class="small d-none d-sm-inline"><i class="bi bi-person-circle me-1"></i><?= e($_SESSION['nama']) ?></span>
      <button id="themeToggle" class="btn btn-sm btn-light" type="button" data-bs-toggle="tooltip" title="Ganti tema"></button>
      <a href="logout.php" class="btn btn-sm btn-light"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">

  <!-- Kartu statistik -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-md-4 fade-up">
      <div class="card stat stat-1"><div class="card-body d-flex justify-content-between align-items-center">
        <div><div class="small">Total Buku</div><h2 class="mb-0 counter" data-target="<?= $total ?>">0</h2></div>
        <i class="bi bi-collection fs-1 opacity-50"></i>
      </div></div>
    </div>
    <div class="col-6 col-md-4 fade-up" style="animation-delay:.1s">
      <div class="card stat stat-2"><div class="card-body d-flex justify-content-between align-items-center">
        <div><div class="small">Tersedia</div><h2 class="mb-0 counter" data-target="<?= $tersedia ?>">0</h2></div>
        <i class="bi bi-check-circle fs-1 opacity-50"></i>
      </div></div>
    </div>
    <div class="col-6 col-md-4 fade-up" style="animation-delay:.2s">
      <div class="card stat stat-3"><div class="card-body d-flex justify-content-between align-items-center">
        <div><div class="small">Dipinjam</div><h2 class="mb-0 counter" data-target="<?= $dipinjam ?>">0</h2></div>
        <i class="bi bi-bookmark-check fs-1 opacity-50"></i>
      </div></div>
    </div>
  </div>

  <!-- Tabel buku -->
  <div class="card card-table fade-up" style="animation-delay:.3s">
    <div class="card-body">

      <div class="row g-2 mb-3 align-items-center">
        <div class="col-12 col-lg"><h5 class="mb-0 fw-bold"><i class="bi bi-journal-bookmark me-2"></i>Daftar Buku</h5></div>
        <div class="col-12 col-sm-6 col-lg-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="cari" class="form-control" placeholder="Cari judul / penulis / kategori...">
          </div>
        </div>
        <div class="col-6 col-sm-3 col-lg-2">
          <select id="filterStatus" class="form-select form-select-sm">
            <option value="">Semua Status</option>
            <option value="Tersedia">Tersedia</option>
            <option value="Dipinjam">Dipinjam</option>
          </select>
        </div>
        <div class="col-6 col-sm-3 col-lg-auto">
          <a href="tambah_buku.php" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i> Tambah Buku</a>
        </div>
      </div>

      <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible alert-auto py-2" role="alert">
          <?= e($flash['msg']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <div class="table-responsive">
        <table class="table table-hover align-middle" id="tabelBuku">
          <thead class="table-light">
            <tr>
              <th>#</th><th>Sampul</th><th>Judul</th><th>Kategori</th><th>Penulis</th>
              <th>Tahun</th><th>Stok</th><th>Status</th><th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($buku)): ?>
            <tr><td colspan="9" class="text-center text-body-secondary py-4">Belum ada data buku. Klik "Tambah Buku" untuk memulai.</td></tr>
          <?php endif; ?>

          <?php foreach ($buku as $i => $b):
              $status = tentukan_status($b['stok']);
          ?>
            <tr data-status="<?= e($status) ?>">
              <td><?= $i + 1 ?></td>
              <td><img src="uploads/<?= e($b['sampul']) ?>" class="cover" alt="Sampul <?= e($b['judul']) ?>"></td>
              <td class="fw-semibold"><?= e($b['judul']) ?></td>
              <td><span class="badge text-bg-info"><?= e($b['kategori']) ?></span></td>
              <td><?= e($b['penulis']) ?></td>
              <td><?= e($b['tahun_terbit']) ?></td>
              <td><?= (int)$b['stok'] ?></td>
              <td><span class="badge text-bg-<?= warna_status($status) ?>"><?= e($status) ?></span></td>
              <td class="text-center text-nowrap">
                <a href="edit_buku.php?id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-outline-primary"
                   data-bs-toggle="tooltip" title="Edit"><i class="bi bi-pencil-square"></i></a>
                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus"
                        data-id="<?= (int)$b['id'] ?>" data-judul="<?= e($b['judul']) ?>"
                        data-bs-toggle="tooltip" title="Hapus"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          <?php endforeach; ?>

            <!-- Baris ini muncul saat hasil pencarian/filter kosong -->
            <tr id="barisKosong" class="d-none">
              <td colspan="9" class="text-center text-body-secondary py-4">
                <i class="bi bi-search fs-3 d-block mb-1"></i>Buku tidak ditemukan.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal konfirmasi hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" action="hapus_buku.php" method="POST">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Hapus Buku</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Yakin ingin menghapus <strong id="judulHapus"></strong>? Gambar sampulnya juga akan dihapus dari server.
        <input type="hidden" name="id" id="idHapus">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>