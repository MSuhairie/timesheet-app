<?php
require_once __DIR__ . '/../includes/auth.php';

$db     = getDB();
$userId = CURRENT_USER_ID;

// ---------- Ambil filter dari query string ----------
$fMonth  = $_GET['month']  ?? date('Y-m');   // format YYYY-MM
$fPlace  = $_GET['place']  ?? '';
$fStatus = $_GET['status'] ?? '';
$fSearch = trim($_GET['q'] ?? '');

$where  = ['user_id = ?'];
$params = [$userId];

if ($fMonth) {
    $where[] = "DATE_FORMAT(activity_date, '%Y-%m') = ?";
    $params[] = $fMonth;
}
if ($fPlace && in_array($fPlace, ['WFO', 'WFH'], true)) {
    $where[] = 'work_place = ?';
    $params[] = $fPlace;
}
if ($fStatus) {
    $where[] = 'status = ?';
    $params[] = $fStatus;
}
if ($fSearch !== '') {
    $where[] = '(task LIKE ? OR notes LIKE ?)';
    $params[] = "%$fSearch%";
    $params[] = "%$fSearch%";
}

$sql = 'SELECT * FROM activities WHERE ' . implode(' AND ', $where) . ' ORDER BY activity_date DESC, id DESC';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$validStatus = ['Planned', 'In Progress', 'Completed', 'Pending', 'Cancelled'];

$pageTitle = 'Riwayat Aktivitas';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-content">
  <h4 class="fw-bold mb-3">Riwayat Aktivitas</h4>

  <div class="section-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-6 col-md-2">
        <label class="form-label small">Bulan</label>
        <input type="month" name="month" class="form-control form-control-sm" value="<?= e($fMonth) ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small">Work Place</label>
        <select name="place" class="form-select form-select-sm">
          <option value="">Semua</option>
          <option value="WFO" <?= $fPlace === 'WFO' ? 'selected' : '' ?>>WFO</option>
          <option value="WFH" <?= $fPlace === 'WFH' ? 'selected' : '' ?>>WFH</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">Semua</option>
          <?php foreach ($validStatus as $s): ?>
            <option value="<?= e($s) ?>" <?= $fStatus === $s ? 'selected' : '' ?>><?= e($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-4">
        <label class="form-label small">Cari Task / Notes</label>
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search...." value="<?= e($fSearch) ?>">
      </div>
      <div class="col-12 col-md-2 d-flex gap-2">
        <button class="btn btn-primary btn-sm w-100" type="submit"><i class="bi bi-search"></i> Filter</button>
        <a href="history.php" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
        <!-- <a href="report.php?month=<?= e($fMonth) ?>" class="btn btn-outline-danger btn-sm w-100 text-nowrap">
          <i class="bi bi-printer"></i> Cetak
        </a> -->
      </div>
    </form>
  </div>

  <div class="section-card">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="text-muted small"><?= count($rows) ?> aktivitas ditemukan</span>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr class="text-muted small text-uppercase">
            <th>Tanggal</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Jam Kerja</th>
            <th>Tempat</th>
            <th>Task</th>
            <th>Status</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data untuk filter ini.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $r):
            $durasi = '-';
            if ($r['check_in'] && $r['check_out']) {
                $mins = (strtotime($r['check_out']) - strtotime($r['check_in'])) / 60;
                $durasi = floor($mins / 60) . 'j ' . ($mins % 60) . 'm';
            }
        ?>
          <tr>
            <td class="small fw-semibold"><?= date('d/m/Y', strtotime($r['activity_date'])) ?></td>
            <td class="small"><?= $r['check_in'] ? e(substr($r['check_in'], 0, 5)) : '-' ?></td>
            <td class="small"><?= $r['check_out'] ? e(substr($r['check_out'], 0, 5)) : '-' ?></td>
            <td class="small"><?= e($durasi) ?></td>
            <td><span class="badge badge-wp-<?= e($r['work_place']) ?>"><?= e($r['work_place']) ?></span></td>
            <td class="small" style="max-width:300px;">
              <div class="text-truncate" title="<?= e($r['task']) ?>"><?= e($r['task']) ?></div>
            </td>
            <td><span class="badge badge-status-<?= e(str_replace(' ', '', $r['status'])) ?>"><?= e($r['status']) ?></span></td>
            <td class="text-end">
              <button type="button" class="btn btn-sm btn-outline-secondary btn-copy-task" data-task="<?= e($r['task']) ?>" title="Salin Task">
                <i class="bi bi-clipboard"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
