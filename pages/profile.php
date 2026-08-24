<?php
require_once __DIR__ . '/../includes/auth.php';

$db     = getDB();
$userId = CURRENT_USER_ID;
$flash  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama'] ?? '');
    $nik        = trim($_POST['nik'] ?? '');
    $departemen = trim($_POST['departemen'] ?? '');
    $jabatan    = trim($_POST['jabatan'] ?? '');
    $project    = trim($_POST['project'] ?? '');
    $client     = trim($_POST['client'] ?? '');
    $atasan     = trim($_POST['atasan'] ?? '');
    $perusahaan = trim($_POST['perusahaan'] ?? '');
    $alamat     = trim($_POST['perusahaan_alamat'] ?? '');
    $telp       = trim($_POST['perusahaan_telp'] ?? '');

    if ($nama === '') {
        $flash = ['type' => 'danger', 'msg' => 'Nama wajib diisi.'];
    } else {
        $stmt = $db->prepare('UPDATE users SET nama=?, nik=?, departemen=?, jabatan=?, project=?, client=?, atasan=?, perusahaan=?, perusahaan_alamat=?, perusahaan_telp=? WHERE id=?');
        $stmt->execute([$nama, $nik, $departemen, $jabatan, $project, $client, $atasan, $perusahaan, $alamat, $telp, $userId]);
        $flash = ['type' => 'success', 'msg' => 'Profil berhasil diperbarui.'];
    }
}

$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$pageTitle = 'Profil Karyawan';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-content">
  <h4 class="fw-bold mb-3">Profil Karyawan</h4>

  <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> py-2"><?= e($flash['msg']) ?></div>
  <?php endif; ?>

  <div class="section-card" style="max-width:640px;">
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label small">Nama Lengkap</label>
          <input type="text" name="nama" class="form-control" value="<?= e($user['nama']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label small">NIK / ID Karyawan</label>
          <input type="text" name="nik" class="form-control" value="<?= e($user['nik'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small">Departemen</label>
          <input type="text" name="departemen" class="form-control" value="<?= e($user['departemen'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small">Jabatan</label>
          <input type="text" name="jabatan" class="form-control" value="<?= e($user['jabatan'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small">Project</label>
          <input type="text" name="project" class="form-control" value="<?= e($user['project'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small">Client</label>
          <input type="text" name="client" class="form-control" value="<?= e($user['client'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small">Atasan / PIC</label>
          <input type="text" name="atasan" class="form-control" value="<?= e($user['atasan'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small">Perusahaan</label>
          <input type="text" name="perusahaan" class="form-control" value="<?= e($user['perusahaan'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label small">Alamat Perusahaan <span class="text-muted">(untuk kop surat laporan)</span></label>
          <input type="text" name="perusahaan_alamat" class="form-control" value="<?= e($user['perusahaan_alamat'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label small">Telp / Fax Perusahaan <span class="text-muted">(untuk kop surat laporan)</span></label>
          <input type="text" name="perusahaan_telp" class="form-control" value="<?= e($user['perusahaan_telp'] ?? '') ?>">
        </div>
      </div>

      <button type="submit" class="btn btn-primary mt-4">
        <i class="bi bi-save me-1"></i>Simpan Perubahan
      </button>
    </form>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
