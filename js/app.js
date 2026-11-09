// Terapkan tema sedini mungkin agar tidak berkedip
(function () {
  const t = localStorage.getItem('tema') || 'light';
  document.documentElement.setAttribute('data-bs-theme', t);
})();

document.addEventListener('DOMContentLoaded', function () {

  // 1. Sembunyikan loader halaman
  const loader = document.getElementById('pageLoader');
  if (loader) setTimeout(() => loader.classList.add('hide'), 300);

  // 2. Tombol mode gelap / terang
  const btnTema = document.getElementById('themeToggle');
  function ikonTema() {
    const gelap = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    if (btnTema) btnTema.innerHTML = '<i class="bi bi-' + (gelap ? 'sun-fill' : 'moon-stars-fill') + '"></i>';
  }
  ikonTema();
  if (btnTema) {
    btnTema.addEventListener('click', function () {
      const baru = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-bs-theme', baru);
      localStorage.setItem('tema', baru);
      ikonTema();
    });
  }

  // 3. Tampilkan / sembunyikan password
  const togglePw = document.getElementById('togglePw');
  if (togglePw) {
    togglePw.addEventListener('click', function () {
      const pw = document.getElementById('password');
      const show = pw.type === 'password';
      pw.type = show ? 'text' : 'password';
      this.innerHTML = '<i class="bi bi-' + (show ? 'eye-slash' : 'eye') + '"></i>';
    });
  }

  // 4. Validasi form + tombol loading saat submit
  document.querySelectorAll('.needs-validation').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      } else {
        const btn = form.querySelector('button[type="submit"], button:not([type])');
        if (btn) {
          setTimeout(function () {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
          }, 0);
        }
      }
      form.classList.add('was-validated');
    });
  });

  // 5. Preview sampul + drag & drop
  const inputSampul = document.getElementById('sampul');
  const preview = document.getElementById('preview');
  function tampilkanFile(f) {
    if (!f) return;
    if (!['image/jpeg', 'image/png'].includes(f.type)) {
      alert('Sampul harus berformat JPG atau PNG.');
      inputSampul.value = '';
      return;
    }
    if (f.size > 2 * 1024 * 1024) {
      alert('Ukuran file maksimal 2 MB.');
      inputSampul.value = '';
      return;
    }
    preview.src = URL.createObjectURL(f);
    const nama = document.getElementById('namaFile');
    if (nama) nama.textContent = f.name;
  }
  if (inputSampul && preview) {
    inputSampul.addEventListener('change', () => tampilkanFile(inputSampul.files[0]));
    const dz = document.querySelector('.dropzone');
    if (dz) {
      ['dragenter', 'dragover'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('dragover'); }));
      ['dragleave', 'drop'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('dragover'); }));
      dz.addEventListener('drop', function (e) {
        if (e.dataTransfer.files.length) {
          inputSampul.files = e.dataTransfer.files;
          tampilkanFile(inputSampul.files[0]);
        }
      });
    }
  }

  // 6. Animasi angka statistik
  document.querySelectorAll('.counter').forEach(function (el) {
    const target = +el.dataset.target;
    let n = 0;
    if (target === 0) { el.textContent = 0; return; }
    const timer = setInterval(function () {
      n += Math.max(1, Math.ceil(target / 25));
      if (n >= target) { n = target; clearInterval(timer); }
      el.textContent = n;
    }, 30);
  });

  // 7. Animasi baris tabel muncul bertahap
  document.querySelectorAll('#tabelBuku tbody tr').forEach(function (tr, i) {
    tr.classList.add('fade-up');
    tr.style.animationDelay = (i * 60) + 'ms';
  });

  // 8. Pencarian + filter status
  const cari = document.getElementById('cari');
  const filterStatus = document.getElementById('filterStatus');
  function terapkanFilter() {
    const q = cari ? cari.value.toLowerCase() : '';
    const st = filterStatus ? filterStatus.value : '';
    let tampil = 0;
    document.querySelectorAll('#tabelBuku tbody tr[data-status]').forEach(function (tr) {
      const cocok = tr.textContent.toLowerCase().includes(q) && (st === '' || tr.dataset.status === st);
      tr.style.display = cocok ? '' : 'none';
      if (cocok) tampil++;
    });
    const kosong = document.getElementById('barisKosong');
    if (kosong) kosong.classList.toggle('d-none', tampil > 0);
  }
  if (cari) cari.addEventListener('input', terapkanFilter);
  if (filterStatus) filterStatus.addEventListener('change', terapkanFilter);

  // 9. Modal konfirmasi hapus
  const modalEl = document.getElementById('modalHapus');
  if (modalEl) {
    const modal = new bootstrap.Modal(modalEl);
    document.querySelectorAll('.btn-hapus').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('idHapus').value = btn.dataset.id;
        document.getElementById('judulHapus').textContent = btn.dataset.judul;
        modal.show();
      });
    });
  }

  // 10. Toast notifikasi
  const toastEl = document.getElementById('toastInfo');
  if (toastEl) new bootstrap.Toast(toastEl).show();

  // 11. Tooltip Bootstrap
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

  // 12. Alert hilang otomatis setelah 4 detik
  document.querySelectorAll('.alert-auto').forEach(function (a) {
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(a).close(), 4000);
  });
});