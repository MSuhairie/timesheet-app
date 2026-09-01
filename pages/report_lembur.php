<?php
require_once __DIR__ . '/../includes/auth.php';

$db     = getDB();
$userId = CURRENT_USER_ID;

$month = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    $month = date('Y-m');
}
$monthStart = $month . '-01';
$monthEnd   = date('Y-m-t', strtotime($monthStart));

$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

// ---------- Tanggal khusus (Tanggal Merah/Libur & Cuti Pengganti) bulan terpilih ----------
$stmt = $db->prepare("SELECT tanggal, jenis FROM special_dates WHERE user_id = ? AND tanggal BETWEEN ? AND ?");
$stmt->execute([$userId, $monthStart, $monthEnd]);
$specialDates = array_column($stmt->fetchAll(), 'jenis', 'tanggal');

// ---------- Ambil semua entry bulan ini yang check_in & check_out terisi ----------
$stmt = $db->prepare("SELECT * FROM activities
                       WHERE user_id = ? AND statusenabled = 't' AND activity_date BETWEEN ? AND ?
                         AND check_in IS NOT NULL AND check_out IS NOT NULL
                       ORDER BY activity_date ASC, id ASC");
$stmt->execute([$userId, $monthStart, $monthEnd]);
$allRows = $stmt->fetchAll();

// ---------- Filter: hanya yang lembur (weekend ATAU tanggal khusus) ----------
$lemburRows = [];
foreach ($allRows as $r) {
    $dow       = (int)date('N', strtotime($r['activity_date'])); // 1=Senin ... 7=Minggu
    $isWeekend = $dow >= 6;
    $isSpecial = isset($specialDates[$r['activity_date']]);
    if ($isWeekend || $isSpecial) {
        $lemburRows[] = $r;
    }
}

// Nama bulan dalam Bahasa Indonesia
$namaBulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];
[$y, $m] = explode('-', $month);
$periode = $namaBulan[(int)$m] . ' ' . $y;

// ---------- Judul dokumen untuk nama file pas "Save as PDF" (format: Surat Perintah Lembur - Aug 2026 - Nama) ----------
$bulanSingkatEn = date('M', strtotime($month . '-01')); // Jan, Feb, Mar, ... Aug, ...
$printFileTitle = 'Surat Perintah Lembur - ' . $bulanSingkatEn . ' ' . $y . ' - ' . ($user['nama'] ?? '');

// ---------- Logo kop surat ----------
$logoKiri  = findLogoFile('Logo1');
$logoKanan = findLogoFile('Logo2');

$pageTitle = 'Surat Perintah Lembur';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-content">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 no-print">
    <h4 class="fw-bold mb-0">Surat Perintah Lembur</h4>
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
      <button type="button" class="btn btn-danger btn-sm" onclick="printReport()">
        <i class="bi bi-file-earmark-pdf me-1"></i>Cetak / Simpan PDF
      </button>
      <!-- <a href="report_lembur_word.php?month=<?= e($month) ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-file-earmark-word me-1"></i>Export Word
      </a> -->
    </div>
  </div>

  <?php if (!$lemburRows): ?>
    <div class="alert alert-info no-print">Tidak ada data lembur (weekend / tanggal merah yang dikerjakan) pada bulan <?= e($periode) ?>.</div>
  <?php endif; ?>

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
    <hr style="border:none; border-top:3px solid #000; margin-bottom:1.2rem;">

    <table class="mb-3 small">
      <tr><td style="width:140px;">Nama</td><td>: <?= e($user['nama'] ?? '-') ?></td></tr>
      <tr><td>Departemen</td><td>: <?= e($user['departemen'] ?? '-') ?></td></tr>
      <tr><td>Nama Project</td><td>: <?= e($user['project'] ?? '-') ?></td></tr>
      <tr><td>Periode</td><td>: <?= e($periode) ?></td></tr>
    </table>

    <div class="spl-banner">SURAT PERINTAH LEMBUR</div>

    <table class="table table-bordered table-sm report-table spl-table">
      <thead>
        <tr class="text-center">
          <th style="width:36px;">No</th>
          <th style="width:100px;">Tanggal SPL</th>
          <th colspan="3">Jam Lembur</th>
          <th>Uraian Tugas Lembur</th>
        </tr>
        <tr class="text-center">
          <th></th>
          <th></th>
          <th style="width:70px;">Start</th>
          <th style="width:70px;">End</th>
          <th style="width:70px;">Total</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$lemburRows): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data lembur pada periode ini.</td></tr>
        <?php endif; ?>
        <?php foreach ($lemburRows as $i => $r):
            $mins  = (strtotime($r['check_out']) - strtotime($r['check_in'])) / 60;
            $total = floor($mins / 60) . 'j ' . ($mins % 60) . 'm';
        ?>
          <tr>
            <td class="text-center"><?= $i + 1 ?></td>
            <td class="text-center"><?= date('d/m/Y', strtotime($r['activity_date'])) ?></td>
            <td class="text-center"><?= e(substr($r['check_in'], 0, 5)) ?></td>
            <td class="text-center"><?= e(substr($r['check_out'], 0, 5)) ?></td>
            <td class="text-center"><?= $total ?></td>
            <td><?= nl2br(e($r['notes'] ?? '')) ?>, <?= nl2br(e($r['task'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <table class="w-100 spl-signature" style="margin-top:20px;">
      <tr>
        <td style="width:33.33%; text-align:center; font-weight:600; padding-bottom:6px;">Pemberi Tugas,</td>
        <td style="width:33.34%; text-align:center; font-weight:600; padding-bottom:6px;">Penerima Tugas,</td>
      </tr>
      <tr>
        <td style="height:90px; border:1px solid #000; vertical-align:middle; text-align:center;">
            <div style="height:70px;">
                <img src="../assets/img/ttd2.png" alt="">
            </div>
        </td>
        <td style="height:90px; border:1px solid #000; vertical-align:middle; text-align:center;">
            <div style="height:70px;">
                <img src="../assets/img/ttd1.png" alt="">
            </div>
        </td>
      </tr>
      <tr>
        <td class="small" style="padding-top:6px;">Nama : Andrie Anmaris</td>
        <td class="small" style="padding-top:6px;">Nama : <?= e($user['nama'] ?: '') ?></td>
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
.spl-banner {
  background: #000;
  color: #fff;
  text-align: center;
  font-weight: 700;
  padding: 8px;
  margin-bottom: 0;
  letter-spacing: .5px;
}
.report-table th { background: #f1f2f4; font-size: .8rem; }
.report-table td { font-size: .82rem; vertical-align: top; }
.report-table,
.report-table th,
.report-table td { border-color: #000 !important; }
.spl-table { margin-top: 0; }
.report-table thead { display: table-header-group; }
.report-table tr { page-break-inside: avoid; break-inside: avoid; }
 
.ttd-dummy {
  font-family: 'Brush Script MT', 'Segoe Script', 'Dancing Script', cursive;
  font-size: 2rem;
  color: #1e3a8a;
  display: inline-block;
  transform: rotate(-3deg);
}
 
@media print {
  @page { margin: 8mm; }
  .app-navbar, .app-sidebar, .no-print { display: none !important; }
  .app-content { padding: 0 !important; }
  .app-wrapper { display: block !important; }
  body { background: #fff !important; }
  .report-sheet { box-shadow: none !important; border: none !important; padding: 0 !important; }
  .spl-signature { page-break-inside: avoid; }
}
</style>
 
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
function printReport() {
  const originalTitle = document.title;
  document.title = <?= json_encode($printFileTitle) ?>;

  const restoreTitle = function () { document.title = originalTitle; };
  window.addEventListener('afterprint', restoreTitle, { once: true });
  setTimeout(restoreTitle, 2000); // fallback untuk browser yang tidak trigger afterprint

  window.print();
}
</script>