<?php
require_once __DIR__ . '/../includes/auth.php';

$db     = getDB();
$userId = CURRENT_USER_ID;
$errors = [];
$flash  = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$validStatus = ['Planned', 'In Progress', 'Completed', 'Pending', 'Cancelled'];
$validPlace  = ['WFO', 'WFH'];

// ---------- Handle Simpan (Tambah / Edit) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id           = (int)($_POST['id'] ?? 0);
    $activityDate = $_POST['activity_date'] ?? date('Y-m-d');
    $checkIn      = $_POST['check_in'] ?: null;
    $checkOut     = $_POST['check_out'] ?: null;
    $workPlace    = in_array($_POST['work_place'] ?? '', $validPlace, true) ? $_POST['work_place'] : 'WFO';
    $task         = trim($_POST['task'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');
    $status       = in_array($_POST['status'] ?? '', $validStatus, true) ? $_POST['status'] : 'Completed';

    if ($task === '') {
        $errors[] = 'Task wajib diisi.';
    }
    if ($checkIn && $checkOut && $checkOut < $checkIn) {
        $errors[] = 'Check Out tidak boleh lebih awal dari Check In.';
    }

    if (!$errors) {
        if ($id > 0) {
            $stmt = $db->prepare('UPDATE activities SET activity_date=?, check_in=?, check_out=?, work_place=?, task=?, notes=?, status=?
                                   WHERE id=? AND user_id=?');
            $stmt->execute([$activityDate, $checkIn, $checkOut, $workPlace, $task, $notes, $status, $id, $userId]);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Aktivitas berhasil diperbarui.'];
        } else {
            $stmt = $db->prepare('INSERT INTO activities (user_id, activity_date, check_in, check_out, work_place, task, notes, status, statusenabled)
                                   VALUES (?,?,?,?,?,?,?,?,"t")');
            $stmt->execute([$userId, $activityDate, $checkIn, $checkOut, $workPlace, $task, $notes, $status]);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Aktivitas berhasil ditambahkan.'];
        }
        header('Location: history.php');
        exit;
    }
}

// ---------- Handle Hapus ----------
if (isset($_GET['delete'])) {
    $stmt = $db->prepare('UPDATE activities SET statusenabled = "f" WHERE id = ? AND user_id = ?');
    $stmt->execute([(int)$_GET['delete'], $userId]);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Aktivitas berhasil dihapus.'];
    header('Location: history.php');
    exit;
}

// ---------- Data untuk form edit ----------
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM activities WHERE id = ? AND user_id = ?');
    $stmt->execute([(int)$_GET['edit'], $userId]);
    $editData = $stmt->fetch();
}

// ---------- Ambil filter dari query string ----------
$fMonth  = $_GET['month']  ?? date('Y-m');   // format YYYY-MM
$fPlace  = $_GET['place']  ?? '';
$fStatus = $_GET['status'] ?? '';
$fSearch = trim($_GET['q'] ?? '');

$where  = ['user_id = ?'];
$params = [$userId];
$statusEnabled = 't';
$where  = ['statusenabled = ?'];
$params = [$statusEnabled];

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

// ---------- Pagination ----------
$countStmt = $db->prepare('SELECT COUNT(*) AS total FROM activities WHERE ' . implode(' AND ', $where));
$countStmt->execute($params);
$totalRows  = (int)$countStmt->fetch()['total'];

$perPage    = 10;
$page       = max(1, (int)($_GET['page'] ?? 1));
$totalPages = max(1, (int)ceil($totalRows / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$stmt = $db->prepare($sql . " LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Query string filter (tanpa "page") supaya bisa dipakai ulang di link pagination
$filterQuery = http_build_query(array_filter([
    'month'  => $fMonth,
    'place'  => $fPlace,
    'status' => $fStatus,
    'q'      => $fSearch,
]));

$validStatus = ['Planned', 'In Progress', 'Completed', 'Pending', 'Cancelled'];

$pageTitle = 'Riwayat Aktivitas';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-content">
  <!-- <h4 class="fw-bold mb-3">Riwayat Aktivitas</h4> -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Daily Activity</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#historyModal" onclick="resetForm()">
      <i class="bi bi-plus-lg me-1"></i>Tambah Aktivitas
    </button>
  </div>

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
      <!-- <div class="col-6 col-md-2">
        <label class="form-label small">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">Semua</option>
          <?php foreach ($validStatus as $s): ?>
            <option value="<?= e($s) ?>" <?= $fStatus === $s ? 'selected' : '' ?>><?= e($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div> -->
      <div class="col-6 col-md-6">
        <label class="form-label small">Cari Task / Notes</label>
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari....." value="<?= e($fSearch) ?>">
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
      <span class="text-muted small"><?= $totalRows ?> aktivitas ditemukan</span>
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
            <td class="text-end">
              <button type="button" class="btn btn-sm btn-outline-secondary btn-copy-task" data-task="<?= e($r['task']) ?>" title="Salin Task">
                <i class="bi bi-clipboard"></i>
              </button>
              <a href="history.php?edit=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="history.php?delete=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete-activity">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <span class="small text-muted">Halaman <?= $page ?> dari <?= $totalPages ?></span>
        <nav>
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="?<?= $filterQuery ?>&page=<?= $page - 1 ?>">&laquo; Sebelumnya</a>
            </li>
            <?php
            $start = max(1, $page - 2);
            $end   = min($totalPages, $page + 2);
            if ($start > 1) echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
            for ($p = $start; $p <= $end; $p++): ?>
              <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="?<?= $filterQuery ?>&page=<?= $p ?>"><?= $p ?></a>
              </li>
            <?php endfor;
            if ($end < $totalPages) echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
            ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
              <a class="page-link" href="?<?= $filterQuery ?>&page=<?= $page + 1 ?>">Berikutnya &raquo;</a>
            </li>
          </ul>
        </nav>
      </div>
    <?php endif; ?>
  </div>
</main>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="historyModal" tabindex="-1" <?= $editData ? 'data-autoshow="1"' : '' ?>>
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="history.php">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"><?= $editData ? 'Edit Aktivitas' : 'Tambah Aktivitas' ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" id="f_id" value="<?= $editData['id'] ?? '' ?>">

        <div class="row g-3">
          <div class="col-6">
            <label class="form-label small">Tanggal</label>
            <input type="date" name="activity_date" id="f_date" class="form-control"
                   value="<?= e($editData['activity_date'] ?? date('Y-m-d')) ?>" required>
          </div>
          <div class="col-6">
            <label class="form-label small">Work Place</label>
            <select name="work_place" id="f_place" class="form-select">
              <option value="WFO" <?= (($editData['work_place'] ?? '') === 'WFO') ? 'selected' : '' ?>>WFO</option>
              <option value="WFH" <?= (($editData['work_place'] ?? '') === 'WFH') ? 'selected' : '' ?>>WFH</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label small">Check In</label>
            <input type="time" name="check_in" id="f_checkin" class="form-control"
                   value="<?= $editData['check_in'] ? substr($editData['check_in'], 0, 5) : '' ?>">
          </div>
          <div class="col-6">
            <label class="form-label small">Check Out</label>
            <input type="time" name="check_out" id="f_checkout" class="form-control"
                   value="<?= $editData['check_out'] ? substr($editData['check_out'], 0, 5) : '' ?>">
          </div>
          <div class="col-12">
            <label class="form-label small">Task</label>
            <textarea name="task" id="f_task" class="form-control" rows="4" required><?= e($editData['task'] ?? '') ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label small">Notes</label>
            <textarea name="notes" id="f_notes" class="form-control" rows="2"><?= e($editData['notes'] ?? '') ?></textarea>
          </div>
          <!-- <div class="col-12">
            <label class="form-label small">Status</label>
            <select name="status" id="f_status" class="form-select">
              <?php foreach ($validStatus as $s): ?>
                <option value="<?= e($s) ?>" <?= (($editData['status'] ?? 'Planned') === $s) ? 'selected' : '' ?>><?= e($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div> -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>window.APP_BASE = '<?= $base ?>';</script>
<script>
function resetForm() {
  document.getElementById('modalTitle').textContent = 'Tambah Aktivitas';
  document.getElementById('f_id').value = '';
  document.getElementById('f_date').value = new Date().toISOString().slice(0,10);
  document.getElementById('f_checkin').value = '';
  document.getElementById('f_checkout').value = '';
  document.getElementById('f_task').value = '';
  document.getElementById('f_notes').value = '';
  document.getElementById('f_status').value = 'Planned';
  document.getElementById('f_place').value = 'WFO';
}

function updateStatus(select) {
  postJSON('update_status.php', { id: select.dataset.id, status: select.value }, function (res) {
    if (!res.success) alert('Gagal update status');
  });
}

// Auto-buka modal kalau lagi mode edit
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.getElementById('historyModal');
  if (modalEl.dataset.autoshow) {
    new bootstrap.Modal(modalEl).show();
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>