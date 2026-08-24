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
    $status       = in_array($_POST['status'] ?? '', $validStatus, true) ? $_POST['status'] : 'Planned';

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
            $stmt = $db->prepare('INSERT INTO activities (user_id, activity_date, check_in, check_out, work_place, task, notes, status)
                                   VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$userId, $activityDate, $checkIn, $checkOut, $workPlace, $task, $notes, $status]);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Aktivitas berhasil ditambahkan.'];
        }
        header('Location: activity.php');
        exit;
    }
}

// ---------- Handle Hapus ----------
if (isset($_GET['delete'])) {
    $stmt = $db->prepare('DELETE FROM activities WHERE id = ? AND user_id = ?');
    $stmt->execute([(int)$_GET['delete'], $userId]);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Aktivitas berhasil dihapus.'];
    header('Location: activity.php');
    exit;
}

// ---------- Data untuk form edit ----------
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM activities WHERE id = ? AND user_id = ?');
    $stmt->execute([(int)$_GET['edit'], $userId]);
    $editData = $stmt->fetch();
}

// ---------- List aktivitas terbaru (30 terakhir) ----------
$stmt = $db->prepare('SELECT * FROM activities WHERE user_id = ? ORDER BY activity_date DESC, id DESC LIMIT 30');
$stmt->execute([$userId]);
$activities = $stmt->fetchAll();

$pageTitle = 'Daily Activity';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Daily Activity</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#activityModal" onclick="resetForm()">
      <i class="bi bi-plus-lg me-1"></i>Tambah Aktivitas
    </button>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> py-2"><?= e($flash['msg']) ?></div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert alert-danger py-2">
      <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
    </div>
  <?php endif; ?>

  <div class="section-card">
    <div class="table-responsive">
      <table class="table align-middle table-hover">
        <thead>
          <tr class="text-muted small text-uppercase">
            <th>Tanggal</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Tempat</th>
            <th>Task</th>
            <th>Status</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$activities): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Belum ada aktivitas. Klik "Tambah Aktivitas" untuk mulai.</td></tr>
        <?php endif; ?>
        <?php foreach ($activities as $a): ?>
          <tr>
            <td class="small fw-semibold"><?= date('d/m/Y', strtotime($a['activity_date'])) ?></td>
            <td class="small"><?= $a['check_in'] ? e(substr($a['check_in'], 0, 5)) : '-' ?></td>
            <td class="small"><?= $a['check_out'] ? e(substr($a['check_out'], 0, 5)) : '-' ?></td>
            <td><span class="badge badge-wp-<?= e($a['work_place']) ?>"><?= e($a['work_place']) ?></span></td>
            <td class="small" style="max-width:260px;">
              <div class="text-truncate" title="<?= e($a['task']) ?>"><?= e($a['task']) ?></div>
            </td>
            <td>
              <select class="form-select form-select-sm status-select" data-id="<?= $a['id'] ?>"
                      style="width:130px;" onchange="updateStatus(this)">
                <?php foreach ($validStatus as $s): ?>
                  <option value="<?= e($s) ?>" <?= $s === $a['status'] ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td class="text-end">
              <button type="button" class="btn btn-sm btn-outline-secondary btn-copy-task" data-task="<?= e($a['task']) ?>" title="Salin Task">
                <i class="bi bi-clipboard"></i>
              </button>
              <a href="activity.php?edit=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="activity.php?delete=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete-activity">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="activityModal" tabindex="-1" <?= $editData ? 'data-autoshow="1"' : '' ?>>
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="activity.php">
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
            <textarea name="task" id="f_task" class="form-control" rows="2" required><?= e($editData['task'] ?? '') ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label small">Notes</label>
            <textarea name="notes" id="f_notes" class="form-control" rows="2"><?= e($editData['notes'] ?? '') ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label small">Status</label>
            <select name="status" id="f_status" class="form-select">
              <?php foreach ($validStatus as $s): ?>
                <option value="<?= e($s) ?>" <?= (($editData['status'] ?? 'Planned') === $s) ? 'selected' : '' ?>><?= e($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
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
  const modalEl = document.getElementById('activityModal');
  if (modalEl.dataset.autoshow) {
    new bootstrap.Modal(modalEl).show();
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
