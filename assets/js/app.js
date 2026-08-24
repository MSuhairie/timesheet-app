document.addEventListener('DOMContentLoaded', function () {
  // ---------- Toggle sidebar (mobile) ----------
  const toggleBtn = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('appSidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => sidebar.classList.toggle('show'));
  }

  // ---------- Jam realtime ----------
  const clockEl = document.getElementById('liveClock');
  if (clockEl) {
    const updateClock = () => {
      const now = new Date();
      clockEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    };
    updateClock();
    setInterval(updateClock, 1000);
  }

  // ---------- Check In ----------
  const btnCheckIn = document.getElementById('btnCheckIn');
  if (btnCheckIn) {
    btnCheckIn.addEventListener('click', function () {
      const workPlace = document.getElementById('workPlaceSelect')?.value || 'WFO';
      postJSON('checkin.php', { work_place: workPlace }, (res) => {
        if (res.success) {
          window.location.reload();
        } else {
          alert(res.message || 'Gagal check in');
        }
      });
    });
  }

  // ---------- Check Out ----------
  const btnCheckOut = document.getElementById('btnCheckOut');
  if (btnCheckOut) {
    btnCheckOut.addEventListener('click', function () {
      if (!confirm('Yakin ingin check out sekarang?')) return;
      postJSON('checkout.php', {}, (res) => {
        if (res.success) {
          window.location.reload();
        } else {
          alert(res.message || 'Gagal check out');
        }
      });
    });
  }

  // ---------- Delete confirm (dipakai di halaman activity/history) ----------
  document.querySelectorAll('.btn-delete-activity').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      if (!confirm('Hapus aktivitas ini? Tindakan tidak bisa dibatalkan.')) {
        e.preventDefault();
      }
    });
  });

  // ---------- Salin Task ke clipboard ----------
  document.querySelectorAll('.btn-copy-task').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const text = btn.dataset.task || '';
      if (!text.trim()) {
        alert('Task masih kosong, tidak ada yang bisa disalin.');
        return;
      }
      copyText(text).then(function () {
        const icon = btn.querySelector('i');
        const originalClass = icon.className;
        icon.className = 'bi bi-check-lg text-success';
        setTimeout(function () { icon.className = originalClass; }, 1200);
      }).catch(function () {
        alert('Gagal menyalin. Silakan salin manual.');
      });
    });
  });
});

function copyText(text) {
  if (navigator.clipboard && window.isSecureContext) {
    return navigator.clipboard.writeText(text);
  }
  // Fallback untuk browser/lingkungan tanpa Clipboard API (mis. non-HTTPS)
  return new Promise(function (resolve, reject) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try {
      document.execCommand('copy');
      resolve();
    } catch (err) {
      reject(err);
    } finally {
      document.body.removeChild(ta);
    }
  });
}

function postJSON(url, data, callback) {
  const base = window.APP_BASE || '';
  fetch(base + '/api/' + url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
    .then((res) => res.json())
    .then(callback)
    .catch((err) => alert('Terjadi kesalahan: ' + err));
}
