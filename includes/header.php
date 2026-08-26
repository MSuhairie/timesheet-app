<?php
/**
 * Header layout. Variabel opsional yang bisa di-set sebelum include:
 * $pageTitle = 'Dashboard';
 */
$user = currentUser();
$pageTitle = $pageTitle ?? 'Timesheet App';
$base = basePath();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<link href="../assets/img/icon.png" rel="icon">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> - <?= e(APP_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= $base ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg app-navbar">
  <div class="container-fluid">
    <button class="btn btn-sm btn-light d-lg-none me-2" type="button" id="sidebarToggle">
      <i class="bi bi-list"></i>
    </button>
    <a class="navbar-brand fw-semibold" href="<?= $base ?>/pages/dashboard.php">
      <i class="bi bi-clock-history me-1"></i> <?= e(APP_NAME) ?>
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <span class="text-white-50 small d-none d-md-inline"><?= date('l, d F Y') ?></span>
      <a href="<?= $base ?>/pages/profile.php" class="btn btn-sm btn-outline-light">
        <i class="bi bi-person-circle me-1"></i><?= e($user['nama'] ?? 'User') ?>
      </a>
    </div>
  </div>
</nav>

<div class="app-wrapper">
