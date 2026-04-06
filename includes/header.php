<?php require_once __DIR__ . '/session.php'; ?>
<div class="topbar">
  <div class="topbar-left">
    <h1>Dashboard</h1>
    <p>Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>.</p>
  </div>

  <div class="topbar-right">
    <div class="date-badge">
      <i class="fa-regular fa-calendar" style="font-size:12px;"></i>
      <span id="todayDate"></span>
    </div>

    <div class="topbar-user">
      <div class="topbar-avatar">
        <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
      </div>
      <span class="topbar-username">
        <?= htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
      </span>
    </div>
  </div>
</div>