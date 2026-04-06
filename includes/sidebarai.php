<?php require_once __DIR__ . '/session.php'; ?>
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$isDataBarang = in_array($currentPage, [
  'data_barang.php',
  'tambah_varian.php',
  'edit.php',
  'master_data.php',
  'tambah_master.php',
  'edit_master.php'
]);
?>
  <!-- ====== SIDEBAR ====== -->
  <aside class="sidebar" id="sidebar">

    <!-- Header -->
    <div class="sidebar-header">
      <div class="logo-area">
        <div class="logo-circle">
          <img src="/assets/logoyh1.jpeg" alt="Logo" style="width: 100%; height:100%; object-fit: cover;">
        </div>
        <div class="logo-text">
          <span class="logo-name">Yudhistira</span>
          <span class="logo-tagline">Handmade</span>
        </div>
      </div>
      <button class="collapse-btn" id="collapseBtn" title="Collapse sidebar">
        <i class="fa-solid fa-chevron-left"></i>
      </button>
    </div>

    <!-- Main Nav -->
    <nav class="sidebar-nav">

      <span class="nav-label">Main Menu</span>

      <a class="nav-item <?= $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="/dashboard.php" data-tooltip="Dashboard">
        <span class="nav-icon"><i class="fa-solid fa-house"></i></span>
        <span class="nav-text">Dashboard</span>
      </a>

      <div class="nav-item dropdown  <?= $isDataBarang ? 'active open' : ''; ?>" data-tooltip="Data Barang">
        <span class="nav-icon"><i class="fa-solid fa-box"></i></span>
        <span class="nav-text">Data Barang</span>
        <span class="dropdown-icon"><i class="fa-solid fa-chevron-down"></i></span>
      </div>

      <div class="dropdown-menu">
        <a href="/pages/data_barang.php" class="dropdown-item <?= $currentPage === 'data_barang.php' || $currentPage === 'edit.php' ? 'active' : ''; ?>">List Produk</a>
        <a href="/pages/master_data.php" class="dropdown-item <?= $currentPage === 'master_data.php' || $currentPage === 'tambah_master.php' || $currentPage === 'edit_master.php' ? 'active' : ''; ?>">Master Data</a>
        <a href="/pages/tambah_varian.php" class="dropdown-item <?= $currentPage === 'tambah_varian.php' ? 'active' : ''; ?>">Tambah Varian</a>
      </div>

      <!-- <a class="nav-item active" href="#" data-tooltip="Calendar">
        <span class="nav-icon"><i class="fa-solid fa-box"></i></span>
        <span class="nav-text">Data Barang</span>
      </a> -->

      <a class="nav-item" href="#" data-tooltip="Notifications">
        <span class="nav-icon"><i class="fa-solid fa-circle-user"></i></span>
        <span class="nav-text">Data User</span>
        <span class="nav-badge">4</span>
      </a>

      <a class="nav-item" href="#" data-tooltip="Team">
        <span class="nav-icon"><i class="fa-solid fa-cart-arrow-down"></i></span>
        <span class="nav-text">Kasir</span>
      </a>

      <!-- <div class="nav-divider"></div>
      <span class="nav-label">Laporan</span>

      <a class="nav-item" href="#" data-tooltip="Analytics">
        <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span>
        <span class="nav-text">Analytics</span>
      </a>

      <a class="nav-item" href="#" data-tooltip="Bookmarks">
        <span class="nav-icon"><i class="fa-solid fa-bookmark"></i></span>
        <span class="nav-text">Bookmarks</span>
      </a>

      <div class="nav-divider"></div>
      <span class="nav-label">System</span>

      <a class="nav-item" href="#" data-tooltip="Settings">
        <span class="nav-icon"><i class="fa-solid fa-gear"></i></span>
        <span class="nav-text">Settings</span>
      </a> -->

    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">

      <a class="nav-item user-card" href="#" data-tooltip="Profile">
        <div class="user-avatar"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User')[0]; ?></div>
        <div class="user-info">
          <div class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></div>
          <div class="user-role"><?= htmlspecialchars($_SESSION['role'] ?? 'Role'); ?></div>
        </div>
      </a>

      <a class="nav-item" href="../logout.php" data-tooltip="Logout" style="color: #f87171;">
        <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
        <span class="nav-text" style="color: #f87171;">Logout</span>
      </a>

    </div>
  </aside>
