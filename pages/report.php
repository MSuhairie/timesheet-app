<?php
require_once __DIR__ . '/../includes/auth.php';

$db     = getDB();
$userId = CURRENT_USER_ID;

$month = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    $month = date('Y-m');
}

$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM activities WHERE user_id = ? AND DATE_FORMAT(activity_date, '%Y-%m') = ?
                       ORDER BY activity_date ASC, id ASC");
$stmt->execute([$userId, $month]);
$rows = $stmt->fetchAll();

// Nama bulan dalam Bahasa Indonesia
$namaBulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];
[$y, $m] = explode('-', $month);
$periode = $namaBulan[(int)$m] . ' ' . $y;

// ---------- Logo kop surat (opsional). Taruh file di assets/img/ dengan nama
// Logo1.png (logo kiri) dan/atau Logo2.png (elemen kanan), ekstensi png/jpg/webp/svg semua didukung.
$logoKiri  = findLogoFile('Logo1');
$logoKanan = findLogoFile('Logo2');

$pageTitle = 'Cetak Laporan';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-content">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 no-print">
    <h4 class="fw-bold mb-0">Cetak Laporan</h4>
    <div class="d-flex align-items-end gap-2">
      <form method="GET" class="d-flex align-items-end gap-2">
        <div>
          <label class="form-label small mb-1">Bulan</label>
          <input type="month" name="month" class="form-control form-control-sm" value="<?= e($month) ?>">
        </div>
        <button type="submit" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-repeat me-1"></i>Tampilkan
        </button>
      </form>
      <button type="button" class="btn btn-danger btn-sm" onclick="window.print()">
        <i class="bi bi-file-earmark-pdf me-1"></i>Cetak / Simpan PDF
      </button>
      <!-- <a href="report_word.php?month=<?= e($month) ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-file-earmark-word me-1"></i>Export Word
      </a> -->
    </div>
  </div>

  <div class="section-card report-sheet">
    <table class="w-100 mb-4" style="border-collapse:collapse;">
      <tr>
        <td style="width:90px; vertical-align:top;">
          <?php if ($logoKiri): ?>
            <img src="<?= $base ?>/assets/img/<?= e(basename($logoKiri)) ?>" alt="Logo" style="max-width:120px; max-height:120px;">
          <?php else: ?>
            <div class="report-logo">iglo</div>
          <?php endif; ?>
        </td>
        <td style="text-align:center;">
          <div class="fw-bold" style="font-size:1.3rem; color:#c0392b;"><?= e($user['perusahaan'] ?: 'PT. Perusahaan') ?></div>
          <?php if (!empty($user['perusahaan_alamat'])): ?>
            <div class="small">Komplek Pertokoan Aldiron Hero Blok C No 10<br>Jl. Daan Mogot Kav. 119, Jakarta Barat 11460</div>
          <?php endif; ?>
          <?php if (!empty($user['perusahaan_telp'])): ?>
            <div class="small"><?= e($user['perusahaan_telp']) ?></div>
          <?php endif; ?>
        </td>
        <td style="width:90px; text-align:right; vertical-align:top;">
          <?php if ($logoKanan): ?>
            <img src="<?= $base ?>/assets/img/<?= e(basename($logoKanan)) ?>" alt="" style="max-width:90px; max-height:90px;">
          <?php endif; ?>
        </td>
      </tr>
    </table>
    <hr style="border:none; border-top:3px solid #000; margin-top:-10px;">

    <table class="mb-4 small">
      <tr><td style="width:140px;">Nama</td><td>: <?= e($user['nama'] ?? '-') ?></td></tr>
      <tr><td>Departemen</td><td>: <?= e($user['departemen'] ?? '-') ?> - <?= e($user['jabatan'] ?? '-') ?></td></tr>
      <tr><td>Nama Project</td><td>: <?= e($user['project'] ?? '-') ?></td></tr>
      <tr><td>Periode</td><td>: <?= e($periode) ?></td></tr>
    </table>

    <table class="table table-bordered table-sm report-table">
      <thead>
        <tr class="text-center">
          <th style="width:36px;">No</th>
          <th style="width:90px;">Date</th>
          <th style="width:70px;">Check In</th>
          <th style="width:75px;">Check Out</th>
          <th style="width:80px;">Work Place</th>
          <th>Task List</th>
          <th style="width:100px;">Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada aktivitas pada periode ini.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $i => $r): ?>
          <tr>
            <td class="text-center"><?= $i + 1 ?></td>
            <td class="text-center"><?= date('d/m/Y', strtotime($r['activity_date'])) ?></td>
            <td class="text-center"><?= $r['check_in'] ? e(substr($r['check_in'], 0, 5)) : '-' ?></td>
            <td class="text-center"><?= $r['check_out'] ? e(substr($r['check_out'], 0, 5)) : '-' ?></td>
            <td class="text-center"><?= e($r['work_place']) ?></td>
            <td><?= nl2br(e($r['task'])) ?></td>
            <td><?= nl2br(e($r['notes'] ?? '')) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <table class="w-100 signature-block" style="margin-top:50px;">
      <tr>
        <td style="width:50%; text-align:center; vertical-align:top;">
          <div>Pemohon,</div>
          <div style="height:70px;">
            <img src="../assets/img/ttd1.png" alt="">
          </div>
          <div class="fw-bold" style="text-decoration:underline;"><?= e($user['nama'] ?: '.....................') ?></div>
          <div class="small">Karyawan</div>
        </td>
        <td style="width:50%; text-align:center; vertical-align:top;">
          <div>Mengetahui,</div>
          <div style="height:70px;">
            <img src="../assets/img/ttd2.png" alt="">
          </div>
          <div class="fw-bold" style="text-decoration:underline;">Andrie Anmaris</div>
          <div class="small">PIC</div>
        </td>
      </tr>
    </table>
  </div>
</main>

<style>
.report-logo {
  background: #c0392b;
  color: #fff;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-style: italic;
}
.report-table th { background: #f1f2f4; font-size: .8rem; }
.report-table td { font-size: .82rem; vertical-align: top; }
.report-table,
.report-table th,
.report-table td { border-color: #000 !important; }
.report-table thead { display: table-header-group; }
.report-table tr { page-break-inside: avoid; break-inside: avoid; }

@media print {
  @page { margin: 8mm; }
  .app-navbar, .app-sidebar, .no-print { display: none !important; }
  .app-content { padding: 0 !important; }
  .app-wrapper { display: block !important; }
  body { background: #fff !important; }
  .report-sheet { box-shadow: none !important; border: none !important; padding: 0 !important; }
  .signature-block { page-break-inside: avoid; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
