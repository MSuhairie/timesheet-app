<?php
require_once __DIR__ . '/../includes/auth.php';

$db     = getDB();
$userId = CURRENT_USER_ID;
$errors = [];
$flash  = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$validJenis = ['libur' => 'Tanggal Merah / Libur', 'cuti_pengganti' => 'Cuti Pengganti Lembur'];

// ---------- Handle Simpan (Tambah / Edit) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id         = (int)($_POST['id'] ?? 0);
    $tanggal    = $_POST['tanggal'] ?? '';
    $jenis      = array_key_exists($_POST['jenis'] ?? '', $validJenis) ? $_POST['jenis'] : 'libur';
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($tanggal === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $errors[] = 'Tanggal wajib diisi.';
    }
    if ($keterangan === '') {
        $errors[] = 'Keterangan wajib diisi.';
    }

    if (!$errors) {
        try {
            if ($id > 0) {
                $stmt = $db->prepare('UPDATE special_dates SET tanggal=?, jenis=?, keterangan=? WHERE id=? AND user_id=?');
                $stmt->execute([$tanggal, $jenis, $keterangan, $id, $userId]);
                $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Tanggal berhasil diperbarui.'];
            } else {
                $stmt = $db->prepare('INSERT INTO special_dates (user_id, tanggal, jenis, keterangan) VALUES (?,?,?,?)');
                $stmt->execute([$userId, $tanggal, $jenis, $keterangan]);
                $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Tanggal berhasil ditambahkan.'];
            }
            header('Location: special_dates.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'Tanggal ini sudah terdaftar. Edit yang sudah ada, atau hapus dulu.';
            } else {
                $errors[] = 'Gagal menyimpan: ' . $e->getMessage();
            }
        }
    }
}

// ---------- Handle Hapus ----------
if (isset($_GET['delete'])) {
    $stmt = $db->prepare('DELETE FROM special_dates WHERE id = ? AND user_id = ?');
    $stmt->execute([(int)$_GET['delete'], $userId]);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Tanggal berhasil dihapus.'];
    header('Location: special_dates.php');
    exit;
}

// ---------- Data untuk form edit ----------
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM special_dates WHERE id = ? AND user_id = ?');
    $stmt->execute([(int)$_GET['edit'], $userId]);
    $editData = $stmt->fetch();
}

// ---------- Tanggal prefill (klik dari kalender Dashboard) ----------
$prefillTanggal = null;
if (!$editData && isset($_GET['tanggal']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tanggal'])) {
    $prefillTanggal = $_GET['tanggal'];
}

// ---------- List ----------
$stmt = $db->prepare('SELECT * FROM special_dates WHERE user_id = ? ORDER BY tanggal DESC');
$stmt->execute([$userId]);
$dates = $stmt->fetchAll();

$pageTitle = 'Tanggal Merah & Cuti';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="fw-bold mb-0">Tanggal Merah &amp; Cuti Pengganti</h4>
      <small class="text-muted">Tanggal di sini otomatis dikecualikan dari "Belum Diisi" di kalender Dashboard</small>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#sdModal" onclick="resetForm()">
      <i class="bi bi-plus-lg me-1"></i>Tambah Tanggal
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
            <th>Jenis</th>
            <th>Keterangan</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$dates): ?>
          <tr><td colspan="4" class="text-center text-muted py-4">Belum ada tanggal khusus. Klik "Tambah Tanggal" untuk mulai.</td></tr>
        <?php endif; ?>
        <?php foreach ($dates as $d):
            $hariNama = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'][date('N', strtotime($d['tanggal'])) - 1];
        ?>
          <tr>
            <td class="small fw-semibold">
              <?= date('d/m/Y', strtotime($d['tanggal'])) ?>
              <div class="text-muted" style="font-size:.72rem;"><?= e($hariNama) ?></div>
            </td>
            <td>
              <?php if ($d['jenis'] === 'libur'): ?>
                <span class="badge" style="background:#fee2e2; color:#991b1b;">Tanggal Merah / Libur</span>
              <?php else: ?>
                <span class="badge" style="background:#cffafe; color:#155e75;">Cuti Pengganti Lembur</span>
              <?php endif; ?>
            </td>
            <td class="small"><?= e($d['keterangan']) ?></td>
            <td class="text-end">
              <a href="special_dates.php?edit=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sdModal" onclick="fillForm(<?= htmlspecialchars(json_encode($d), ENT_QUOTES) ?>)">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="special_dates.php?delete=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete-activity">
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
<div class="modal fade" id="sdModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="special_dates.php">
      <div class="modal-header">
        <h5 class="modal-title" id="sdModalTitle">Tambah Tanggal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" id="sd_id" value="">

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label small">Tanggal</label>
            <input type="date" name="tanggal" id="sd_tanggal" class="form-control" required>
          </div>
          <div class="col-12">
            <label class="form-label small">Jenis</label>
            <select name="jenis" id="sd_jenis" class="form-select">
              <?php foreach ($validJenis as $val => $label): ?>
                <option value="<?= e($val) ?>"><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label small">Keterangan</label>
            <input type="text" name="keterangan" id="sd_keterangan" class="form-control"
                   placeholder="mis. Hari Raya Idulfitri, atau Cuti pengganti lembur 16 Agustus" required>
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
  document.getElementById('sdModalTitle').textContent = 'Tambah Tanggal';
  document.getElementById('sd_id').value = '';
  document.getElementById('sd_tanggal').value = '';
  document.getElementById('sd_jenis').value = 'libur';
  document.getElementById('sd_keterangan').value = '';
}

function fillForm(data) {
  document.getElementById('sdModalTitle').textContent = 'Edit Tanggal';
  document.getElementById('sd_id').value = data.id;
  document.getElementById('sd_tanggal').value = data.tanggal;
  document.getElementById('sd_jenis').value = data.jenis;
  document.getElementById('sd_keterangan').value = data.keterangan;
}

<?php if ($editData): ?>
document.addEventListener('DOMContentLoaded', function () {
  fillForm(<?= json_encode($editData) ?>);
  new bootstrap.Modal(document.getElementById('sdModal')).show();
});
<?php elseif ($prefillTanggal): ?>
document.addEventListener('DOMContentLoaded', function () {
  resetForm();
  document.getElementById('sd_tanggal').value = <?= json_encode($prefillTanggal) ?>;
  new bootstrap.Modal(document.getElementById('sdModal')).show();
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>