<?php
require_once __DIR__ . '/../includes/auth.php';

$db     = getDB();
$userId = CURRENT_USER_ID;
$today  = date('Y-m-d');

// ---------- Bulan yang ditampilkan (filter, default bulan berjalan) ----------
$month = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    $month = date('Y-m');
}
$monthStart = $month . '-01';
$monthEnd   = date('Y-m-t', strtotime($monthStart));
$isCurrentMonth = $month === date('Y-m');

// ---------- Statistik bulan terpilih ----------
$stmt = $db->prepare("
    SELECT
        COUNT(DISTINCT CASE WHEN check_in IS NOT NULL THEN activity_date END) AS total_hari,
        COALESCE(SUM(CASE WHEN check_in IS NOT NULL AND check_out IS NOT NULL
                     THEN TIMESTAMPDIFF(MINUTE, check_in, check_out) END), 0) AS total_menit,
        COUNT(CASE WHEN work_place = 'WFO' AND check_in IS NOT NULL THEN 1 END) AS hari_wfo,
        COUNT(CASE WHEN work_place = 'WFH' AND check_in IS NOT NULL THEN 1 END) AS hari_wfh,
        COUNT(CASE WHEN status = 'Completed' THEN 1 END) AS task_selesai,
        COUNT(CASE WHEN status = 'Pending' THEN 1 END) AS task_pending,
        COUNT(*) AS task_bulan_ini
    FROM activities
    WHERE user_id = ? AND statusenabled = 't' AND activity_date BETWEEN ? AND ?
");
$stmt->execute([$userId, $monthStart, $monthEnd]);
$stat = $stmt->fetch();

$totalJam = floor($stat['total_menit'] / 60);
$totalSisaMenit = $stat['total_menit'] % 60;

// ---------- Aktivitas hari ini (untuk box check-in/out) ----------
$stmt = $db->prepare('SELECT * FROM activities WHERE user_id = ? AND activity_date = ?');
$stmt->execute([$userId, $today]);
$todayActivity = $stmt->fetch();

// ---------- Jam kerja 7 hari terakhir (untuk grafik sederhana) ----------
$stmt = $db->prepare("
    SELECT activity_date,
           CASE WHEN check_in IS NOT NULL AND check_out IS NOT NULL
                THEN TIMESTAMPDIFF(MINUTE, check_in, check_out) ELSE 0 END AS menit
    FROM activities
    WHERE user_id = ? AND statusenabled = 't' AND activity_date BETWEEN DATE_SUB(?, INTERVAL 6 DAY) AND ?
    ORDER BY activity_date ASC
");
$stmt->execute([$userId, $today, $today]);
$weekRows = $stmt->fetchAll();

// Susun 7 hari penuh (isi 0 kalau tidak ada data)
$weekData = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $weekData[$d] = 0;
}
foreach ($weekRows as $row) {
    $weekData[$row['activity_date']] = round($row['menit'] / 60, 1);
}
$maxJam = max(array_merge($weekData, [1]));

// ---------- Kalender aktivitas bulan terpilih (hijau = sudah entry, merah = belum entry) ----------
$stmt = $db->prepare("SELECT DISTINCT activity_date FROM activities WHERE user_id = ? AND statusenabled = 't' AND activity_date BETWEEN ? AND ?");
$stmt->execute([$userId, $monthStart, $monthEnd]);
$entryDates = array_flip(array_column($stmt->fetchAll(), 'activity_date'));

$daysInMonth   = (int)date('t', strtotime($monthStart));
$firstWeekday  = (int)date('N', strtotime($monthStart)); // 1=Senin ... 7=Minggu
$calendarCells = array_fill(0, $firstWeekday - 1, null); // kosongkan sebelum tanggal 1

for ($d = 1; $d <= $daysInMonth; $d++) {
    $dateStr   = sprintf('%s-%02d', $month, $d);
    $dow       = (int)date('N', strtotime($dateStr)); // 1-7
    $isWeekday = $dow >= 1 && $dow <= 5;
    $hasEntry  = isset($entryDates[$dateStr]);

    if ($hasEntry) {
        $status = 'entry';       // hijau
    } elseif ($isWeekday && $dateStr < $today) {
        $status = 'missing';     // merah - hari kerja sudah lewat, belum diisi
    } elseif ($isWeekday && $dateStr === $today) {
        $status = 'today';       // biru - hari ini, belum diisi
    } else {
        $status = 'neutral';     // abu-abu - weekend / belum waktunya
    }

    $calendarCells[] = ['day' => $d, 'date' => $dateStr, 'status' => $status];
}
// Genapkan ke kelipatan 7 supaya grid rapi
while (count($calendarCells) % 7 !== 0) {
    $calendarCells[] = null;
}

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-content">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h4 class="fw-bold mb-0">Dashboard</h4>
      <small class="text-muted"><?= e(date('F Y', strtotime($monthStart))) ?><?= $isCurrentMonth ? '' : ' (bukan bulan berjalan)' ?></small>
    </div>
    <form method="GET" class="d-flex align-items-end gap-2">
      <div>
        <label class="form-label small mb-1">Bulan</label>
        <input type="month" name="month" class="form-control form-control-sm" value="<?= e($month) ?>" onchange="this.form.submit()">
      </div>
      <?php if (!$isCurrentMonth): ?>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Bulan Ini</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Stat cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <div class="stat-card c1">
        <div class="label">Total Hari Kerja</div>
        <h3><?= (int)$stat['total_hari'] ?> <small style="font-size:.9rem;">Hari</small></h3>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card c2">
        <div class="label">Total Jam Kerja</div>
        <h3><?= $totalJam ?>j <?= $totalSisaMenit ?>m</h3>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card c3">
        <div class="label">Hari WFO / WFH</div>
        <h3><?= (int)$stat['hari_wfo'] ?> / <?= (int)$stat['hari_wfh'] ?></h3>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card c4">
        <div class="label">Task Bulan Ini</div>
        <h3><?= (int)$stat['task_bulan_ini'] ?> <small style="font-size:.9rem;">Task</small></h3>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <!-- Check In/Out box -->
    <div class="col-lg-4">
      <div class="section-card h-100">
        <h6 class="fw-bold mb-3"><i class="bi bi-fingerprint me-1"></i>Check In / Check Out</h6>
        <div class="clock-box">
          <div class="time" id="liveClock"></div>
          <div class="text-muted small mb-3"><?= date('d F Y') ?></div>

          <?php if (!$todayActivity || !$todayActivity['check_in']): ?>
            <!-- <div class="mb-3">
              <select id="workPlaceSelect" class="form-select form-select-sm mx-auto" style="max-width:150px;">
                <option value="WFO">WFO</option>
                <option value="WFH">WFH</option>
              </select>
            </div>
            <button id="btnCheckIn" class="btn btn-success btn-checkin">
              <i class="bi bi-box-arrow-in-right me-1"></i> Check In
            </button> -->
          <?php elseif (!$todayActivity['check_out']): ?>
            <div class="alert alert-success py-2 small">
              Check In: <strong><?= e(substr($todayActivity['check_in'], 0, 5)) ?></strong>
              <span class="badge badge-wp-<?= e($todayActivity['work_place']) ?> ms-1"><?= e($todayActivity['work_place']) ?></span>
            </div>
            <button id="btnCheckOut" class="btn btn-danger btn-checkin">
              <i class="bi bi-box-arrow-right me-1"></i> Check Out
            </button>
          <?php else:
            $mins = (strtotime($todayActivity['check_out']) - strtotime($todayActivity['check_in'])) / 60;
            $h = floor($mins / 60); $m = $mins % 60;
          ?>
            <div class="alert alert-secondary py-2 small mb-2">
              Check In: <strong><?= e(substr($todayActivity['check_in'], 0, 5)) ?></strong> &middot;
              Check Out: <strong><?= e(substr($todayActivity['check_out'], 0, 5)) ?></strong>
            </div>
            <div class="fw-bold text-primary">Working Hours: <?= $h ?> Jam <?= $m ?> Menit</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Grafik aktivitas 7 hari -->
    <div class="col-lg-8">
      <div class="section-card h-100">
        <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart-line me-1"></i>Jam Kerja 7 Hari Terakhir</h6>
        <div class="d-flex align-items-end justify-content-between" style="height:180px;">
          <?php foreach ($weekData as $date => $jam): ?>
            <div class="text-center flex-fill mx-1">
              <div class="mx-auto" style="width:28px; height:<?= max(6, ($jam / $maxJam) * 140) ?>px; background:linear-gradient(180deg,#6366f1,#4338ca); border-radius:6px 6px 0 0;" title="<?= $jam ?> jam"></div>
              <div class="small mt-2 fw-semibold"><?= $jam ?>j</div>
              <div class="small text-muted"><?= date('d/m', strtotime($date)) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Task selesai/pending -->
  <div class="row g-3 mt-1">
    <div class="col-md-6">
      <div class="section-card d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Task Selesai (bulan ini)</div>
          <h4 class="fw-bold text-success mb-0"><?= (int)$stat['task_selesai'] ?></h4>
        </div>
        <i class="bi bi-check-circle text-success" style="font-size:2rem;"></i>
      </div>
    </div>
    <div class="col-md-6">
      <div class="section-card d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Task Pending (bulan ini)</div>
          <h4 class="fw-bold text-danger mb-0"><?= (int)$stat['task_pending'] ?></h4>
        </div>
        <i class="bi bi-hourglass-split text-danger" style="font-size:2rem;"></i>
      </div>
    </div>
  </div>

  <!-- Kalender aktivitas -->
  <div class="row g-3 mt-1">
    <div class="col-12">
      <div class="section-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <h6 class="fw-bold mb-0"><i class="bi bi-calendar3 me-1"></i>Kalender Aktivitas &mdash; <?= e(date('F Y', strtotime($monthStart))) ?></h6>
          <div class="d-flex gap-3 small">
            <span><span class="cal-dot cal-dot-entry"></span> Sudah diisi</span>
            <span><span class="cal-dot cal-dot-missing"></span> Belum diisi</span>
            <span><span class="cal-dot cal-dot-today"></span> Hari ini</span>
          </div>
        </div>
        <div class="calendar-grid">
          <?php foreach (['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $h): ?>
            <div class="calendar-head"><?= $h ?></div>
          <?php endforeach; ?>
          <?php foreach ($calendarCells as $cell): ?>
            <?php if ($cell === null): ?>
              <div class="calendar-cell calendar-empty"></div>
            <?php else: ?>
              <div class="calendar-cell calendar-<?= e($cell['status']) ?>" title="<?= e(date('d/m/Y', strtotime($cell['date']))) ?>">
                <?= $cell['day'] ?>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<script>window.APP_BASE = '<?= $base ?>';</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>