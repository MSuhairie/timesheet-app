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

$namaBulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];
[$y, $m] = explode('-', $month);
$periode  = $namaBulan[(int)$m] . ' ' . $y;
$fileName = 'Timesheet - ' . ($user['nama'] ?? 'Laporan') . ' - ' . $periode . '.doc';

// ---------- Logo kop surat: di-embed langsung sebagai base64 data URI.
// Wajib base64 (bukan path relatif biasa) karena file .doc ini didownload dan
// dibuka lepas dari server -- path relatif/URL biasa tidak akan ter-load oleh Word.
$logoKiriPath  = findLogoFile('Logo1');
$logoKananPath = findLogoFile('Logo2');
$logoKiriB64   = $logoKiriPath ? imageToBase64($logoKiriPath) : '';
$logoKananB64  = $logoKananPath ? imageToBase64($logoKananPath) : '';

// ---------- Header khusus supaya file dikenali & didownload sebagai Word ----------
header('Content-Type: application/msword; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<!--[if gte mso 9]>
<xml>
<w:WordDocument>
<w:View>Print</w:View>
<w:Zoom>100</w:Zoom>
<w:DoNotOptimizeForBrowser/>
</w:WordDocument>
</xml>
<![endif]-->
<style>
  /* ---------- Margin Narrow (0.5 inch semua sisi) khusus Word ---------- */
  @page Section1 {
    size: 8.5in 11.0in;
    margin: 0.5in 0.5in 0.5in 0.5in;
    mso-header-margin: 0.5in;
    mso-footer-margin: 0.5in;
    mso-paper-source: 0;
  }
  div.Section1 { page: Section1; }

  /* ---------- Reset paragraph spacing bawaan Word (biar tidak renggang) ---------- */
  p.MsoNormal, li.MsoNormal, div.MsoNormal {
    margin: 0in;
    margin-bottom: .0001pt;
    mso-pagination: widow-orphan;
    font-size: 11pt;
    font-family: Calibri, Arial, sans-serif;
  }
  p, div {
    margin: 0;
    padding: 0;
    mso-margin-top-alt: 0;
    mso-margin-bottom-alt: 0;
    line-height: 1.25;
  }

  body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; }
  table { border-collapse: collapse; width: 100%; }
  .kop-table td { border: none; vertical-align: top; }
  .company-name { color: #c0392b; font-size: 16pt; font-weight: bold; text-align:center; }
  .company-info { text-align:center; font-size: 9.5pt; }
  .divider { border-top: 3px solid #000; margin: 8px 0 16px 0; font-size:1px; line-height:1px; }
  .info-table td { border: none; font-size: 10.5pt; padding: 1px 0; line-height: 1.3; }
  .report-table, .report-table th, .report-table td { border: 1px solid #000; }
  .report-table th { background: #f1f2f4; font-size: 10pt; text-align:center; padding: 5px; }
  .report-table td { font-size: 10pt; padding: 5px; vertical-align: top; }
  .text-center { text-align: center; }
</style>
</head>
<body>
<div class="Section1">

<table class="kop-table">
  <tr>
    <td style="width:80px;">
      <?php if ($logoKiriB64): ?>
        <img src="<?= $logoKiriB64 ?>" alt="" width="70" height="70">
      <?php endif; ?>
    </td>
    <td>
      <div class="company-name"><?= e($user['perusahaan'] ?: 'PT. Perusahaan') ?></div>
      <?php if (!empty($user['perusahaan_alamat'])): ?>
        <div class="company-info"><?= e($user['perusahaan_alamat']) ?></div>
      <?php endif; ?>
      <?php if (!empty($user['perusahaan_telp'])): ?>
        <div class="company-info"><?= e($user['perusahaan_telp']) ?></div>
      <?php endif; ?>
    </td>
    <td style="width:80px; text-align:right;">
      <?php if ($logoKananB64): ?>
        <img src="<?= $logoKananB64 ?>" alt="" width="70" height="70">
      <?php endif; ?>
    </td>
  </tr>
</table>
<div class="divider">&nbsp;</div>

<table class="info-table" style="margin-bottom:16px;">
  <tr><td style="width:140px;">Nama</td><td>: <?= e($user['nama'] ?? '-') ?></td></tr>
  <tr><td>Departemen</td><td>: <?= e($user['departemen'] ?? '-') ?></td></tr>
  <tr><td>Nama Project</td><td>: <?= e($user['project'] ?? '-') ?></td></tr>
  <tr><td>Periode</td><td>: <?= e($periode) ?></td></tr>
</table>

<table class="report-table">
  <thead>
    <tr>
      <th style="width:30px;">No</th>
      <th style="width:80px;">Date</th>
      <th style="width:65px;">Check In</th>
      <th style="width:65px;">Check Out</th>
      <th style="width:65px;">Work Place</th>
      <th>Task List</th>
      <th style="width:120px;">Notes</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="7" class="text-center">Tidak ada aktivitas pada periode ini.</td></tr>
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

<table style="width:100%; margin-top:50px;">
  <tr>
    <td style="width:50%; text-align:center; vertical-align:top;">
      <div>Dibuat oleh,</div>
      <div style="height:55px;">&nbsp;</div>
      <div style="font-weight:bold; text-decoration:underline;"><?= e($user['nama'] ?: '.....................') ?></div>
      <div style="font-size:9.5pt;"><?= e($user['jabatan'] ?: 'Karyawan') ?></div>
    </td>
    <td style="width:50%; text-align:center; vertical-align:top;">
      <div>Mengetahui,</div>
      <div style="height:55px;">&nbsp;</div>
      <div style="font-weight:bold; text-decoration:underline;"><?= e($user['atasan'] ?: '.....................') ?></div>
      <div style="font-size:9.5pt;">Atasan / PIC</div>
    </td>
  </tr>
</table>

</div>
</body>
</html>
