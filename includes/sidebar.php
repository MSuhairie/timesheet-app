<?php
$current = basename($_SERVER['SCRIPT_NAME']);
function navActive(string $file, string $current): string
{
    return $file === $current ? 'active' : '';
}
?>
<aside class="app-sidebar" id="appSidebar">
  <nav class="nav flex-column p-2">
    <a class="nav-link <?= navActive('dashboard.php', $current) ?>" href="<?= $base ?>/pages/dashboard.php">
      <i class="bi bi-speedometer2 me-2"></i>Dashboard
    </a>
    <!-- <a class="nav-link <?= navActive('activity.php', $current) ?>" href="<?= $base ?>/pages/activity.php">
      <i class="bi bi-list-task me-2"></i>Daily Activity
    </a> -->
    <a class="nav-link <?= navActive('history.php', $current) ?>" href="<?= $base ?>/pages/history.php">
      <!-- <i class="bi bi-clock-history me-2"></i>Riwayat Aktivitas -->
      <i class="bi bi-list-task me-2"></i>Daily Activity
    </a>
    <a class="nav-link <?= navActive('report.php', $current) ?>" href="<?= $base ?>/pages/report.php">
      <i class="bi bi-printer me-2"></i>Cetak Laporan
    </a>
    <a class="nav-link <?= navActive('profile.php', $current) ?>" href="<?= $base ?>/pages/profile.php">
      <i class="bi bi-person-badge me-2"></i>Profil Karyawan
    </a>
  </nav>
</aside>
