<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Sidebar Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <!-- <link rel="stylesheet" href="style.css"/> -->
</head>

<body>

  <!-- ====== SIDEBAR ====== -->
  <aside class="sidebar" id="sidebar">

    <!-- Header -->
    <div class="sidebar-header">
      <div class="logo-area">
        <div class="logo-circle">
          <img src="../assets/logoyh1.jpeg" alt="Logo" style="width: 100%; height:100%; object-fit: cover;">
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

      <a class="nav-item" href="../dashboard.php" data-tooltip="Dashboard">
        <span class="nav-icon"><i class="fa-solid fa-house"></i></span>
        <span class="nav-text">Dashboard</span>
      </a>

      <div class="nav-item dropdown" data-tooltip="Data Barang">
        <span class="nav-icon"><i class="fa-solid fa-box"></i></span>
        <span class="nav-text">Data Barang</span>
        <span class="dropdown-icon"><i class="fa-solid fa-chevron-down"></i></span>
      </div>

      <div class="dropdown-menu">
        <a href="#" class="dropdown-item">Tambah Barang</a>
        <a href="../pages/data_barang.php" class="dropdown-item">List Barang</a>
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
        <div class="user-avatar">A</div>
        <div class="user-info">
          <div class="user-name">Aridika</div>
          <div class="user-role">Super Admin</div>
        </div>
      </a>

      <a class="nav-item" href="#" data-tooltip="Logout" style="color: #f87171;">
        <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
        <span class="nav-text" style="color: #f87171;">Logout</span>
      </a>

    </div>
  </aside>


</body>

</html>