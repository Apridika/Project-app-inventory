<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NexAdmin — Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --sidebar-width: 260px;
      --sidebar-collapsed: 72px;
      --bg-body:    #080808;
      --bg-sidebar: #0a0a0a;
      --bg-card:    #111111;
      --bg-card-2:  #161616;
      --bg-hover:   rgba(255,255,255,0.05);
      --bg-active:  rgba(255,255,255,0.10);
      --border:     rgba(255,255,255,0.07);
      --border-md:  rgba(255,255,255,0.12);
      --text-primary:   #f5f5f5;
      --text-secondary: #888888;
      --text-muted:     #444444;
      --accent-gradient: linear-gradient(135deg, #ffffff, #aaaaaa);
      --green:  #22c55e;
      --yellow: #eab308;
      --red:    #ef4444;
      --shadow-card: 0 2px 20px rgba(0,0,0,0.4);
      --shadow-logo: 0 4px 20px rgba(255,255,255,0.08);
      --transition: 0.3s cubic-bezier(0.4,0,0.2,1);
      --radius-sidebar: 0 22px 22px 0;
      --radius-card: 16px;
      --radius-menu: 12px;
    }

    html, body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-body); color: var(--text-primary); height: 100%; overflow: hidden; }

    .layout { display: flex; height: 100vh; }

    /* ═══ SIDEBAR ═══ */
    .sidebar {
      width: var(--sidebar-width); height: 100vh;
      background: var(--bg-sidebar);
      border-radius: var(--radius-sidebar);
      box-shadow: 4px 0 40px rgba(0,0,0,0.6);
      display: flex; flex-direction: column;
      position: fixed; left: 0; top: 0; z-index: 100;
      transition: width var(--transition);
      overflow: hidden;
      border-right: 1px solid var(--border);
    }
    .sidebar.collapsed { width: var(--sidebar-collapsed); }

    .sidebar-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 24px 18px 20px; flex-shrink: 0;
      border-bottom: 1px solid var(--border); min-height: 80px;
    }
    .logo-area { display: flex; align-items: center; gap: 12px; overflow: hidden; }
    .logo-circle {
      width: 40px; height: 40px; border-radius: 50%;
      background: var(--accent-gradient);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0; box-shadow: var(--shadow-logo); overflow: hidden;
    }
    .logo-circle i { color: #111; font-size: 16px; }
    .logo-text { display: flex; flex-direction: column; overflow: hidden; opacity: 1; transition: opacity var(--transition), width var(--transition); }
    .sidebar.collapsed .logo-text { opacity: 0; width: 0; pointer-events: none; }
    .logo-name { font-size: 14px; font-weight: 800; color: var(--text-primary); white-space: nowrap; }
    .logo-tagline { font-size: 10px; color: var(--text-secondary); white-space: nowrap; letter-spacing: 0.06em; text-transform: uppercase; }

    .collapse-btn {
      width: 30px; height: 30px; border-radius: 8px;
      background: var(--bg-hover); border: 1px solid var(--border);
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; flex-shrink: 0; color: var(--text-secondary);
      transition: background var(--transition), transform var(--transition);
    }
    .collapse-btn:hover { background: var(--bg-active); color: var(--text-primary); }
    .sidebar.collapsed .collapse-btn { transform: rotate(180deg); }

    .sidebar-nav { flex: 1; display: flex; flex-direction: column; padding: 16px 12px; overflow-y: auto; overflow-x: hidden; gap: 2px; scrollbar-width: none; }
    .sidebar-nav::-webkit-scrollbar { display: none; }

    .nav-label { font-size: 9.5px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-muted); padding: 6px 10px 8px; white-space: nowrap; opacity: 1; transition: opacity var(--transition); }
    .sidebar.collapsed .nav-label { opacity: 0; }

    .nav-item {
      display: flex; align-items: center; gap: 12px;
      padding: 11px 12px; border-radius: var(--radius-menu);
      cursor: pointer; transition: background var(--transition), color var(--transition);
      color: var(--text-secondary); position: relative;
      white-space: nowrap; overflow: hidden; user-select: none; text-decoration: none;
    }
    .nav-item:hover { background: var(--bg-hover); color: var(--text-primary); }
    .nav-item.active { background: var(--bg-active); color: var(--text-primary); }
    .nav-item.active .nav-icon { color: #fff; }
    .nav-item.active::before {
      content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%);
      width: 3px; height: 60%; background: var(--accent-gradient); border-radius: 0 4px 4px 0;
    }
    .nav-icon { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 15px; }
    .nav-text { font-size: 13.5px; font-weight: 500; flex: 1; opacity: 1; transition: opacity var(--transition); }
    .sidebar.collapsed .nav-text { opacity: 0; pointer-events: none; }
    .nav-badge { background: var(--text-primary); color: #111; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 20px; flex-shrink: 0; opacity: 1; transition: opacity var(--transition); }
    .sidebar.collapsed .nav-badge { opacity: 0; }
    .sidebar.collapsed .nav-item::after {
      content: attr(data-tooltip); position: absolute; left: calc(100% + 14px); top: 50%; transform: translateY(-50%);
      background: #1a1a1a; color: var(--text-primary); font-size: 12px; font-weight: 500;
      padding: 6px 12px; border-radius: 8px; white-space: nowrap; pointer-events: none;
      opacity: 0; transition: opacity 0.2s; box-shadow: 0 4px 16px rgba(0,0,0,0.5);
      border: 1px solid var(--border); z-index: 200;
    }
    .sidebar.collapsed .nav-item:hover::after { opacity: 1; }
    .nav-divider { height: 1px; background: var(--border); margin: 10px 4px; }

    .sidebar-footer { padding: 12px 12px 20px; border-top: 1px solid var(--border); display: flex; flex-direction: column; gap: 2px; }
    .user-avatar-sm { width: 32px; height: 32px; border-radius: 50%; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 12px; font-weight: 700; color: #111; }
    .user-info { flex: 1; overflow: hidden; opacity: 1; transition: opacity var(--transition); }
    .sidebar.collapsed .user-info { opacity: 0; }
    .user-name-sm { font-size: 12.5px; font-weight: 600; color: var(--text-primary); white-space: nowrap; }
    .user-role-sm { font-size: 10.5px; color: var(--text-secondary); white-space: nowrap; }

    /* ═══ MAIN ═══ */
    .main {
      margin-left: var(--sidebar-width); flex: 1;
      height: 100vh; overflow-y: auto; overflow-x: hidden;
      transition: margin-left var(--transition);
      scrollbar-width: thin; scrollbar-color: #222 transparent;
    }
    .main.shifted { margin-left: var(--sidebar-collapsed); }
    .main::-webkit-scrollbar { width: 5px; }
    .main::-webkit-scrollbar-track { background: transparent; }
    .main::-webkit-scrollbar-thumb { background: #222; border-radius: 99px; }

    .content-wrap { padding: 28px 32px; }

    /* TOP BAR */
    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid var(--border); }
    .topbar-left h1 { font-size: 24px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em; }
    .topbar-left p { font-size: 13px; color: var(--text-secondary); margin-top: 3px; }
    .topbar-right { display: flex; align-items: center; gap: 12px; }

    .date-badge { background: var(--bg-card); border: 1px solid var(--border); padding: 8px 14px; border-radius: 10px; font-size: 12px; color: var(--text-secondary); display: flex; align-items: center; gap: 7px; }

    .notif-btn { width: 38px; height: 38px; border-radius: 10px; background: var(--bg-card); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; cursor: pointer; position: relative; color: var(--text-secondary); transition: background var(--transition), color var(--transition); }
    .notif-btn:hover { background: var(--bg-card-2); color: var(--text-primary); }
    .notif-dot { position: absolute; top: 8px; right: 8px; width: 7px; height: 7px; border-radius: 50%; background: var(--text-primary); border: 2px solid var(--bg-body); }

    .topbar-user { display: flex; align-items: center; gap: 10px; background: var(--bg-card); border: 1px solid var(--border); padding: 6px 14px 6px 8px; border-radius: 99px; cursor: pointer; transition: background var(--transition); }
    .topbar-user:hover { background: var(--bg-card-2); }
    .topbar-avatar { width: 30px; height: 30px; border-radius: 50%; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; color: #111; }
    .topbar-username { font-size: 12.5px; font-weight: 600; color: var(--text-primary); }

    /* STAT CARDS */
    .stats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 24px; }

    .stat-card {
      background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-card);
      padding: 22px 22px 18px; display: flex; flex-direction: column; gap: 14px;
      transition: border-color var(--transition), transform var(--transition), box-shadow var(--transition);
      animation: fadeUp 0.5s ease both;
    }
    .stat-card:hover { border-color: var(--border-md); transform: translateY(-2px); box-shadow: var(--shadow-card); }
    .stat-card:nth-child(1){animation-delay:.05s} .stat-card:nth-child(2){animation-delay:.1s} .stat-card:nth-child(3){animation-delay:.15s} .stat-card:nth-child(4){animation-delay:.2s}

    @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }

    .stat-header { display: flex; align-items: center; justify-content: space-between; }
    .stat-label { font-size: 12px; font-weight: 500; color: var(--text-secondary); }
    .stat-icon { width: 34px; height: 34px; border-radius: 9px; background: var(--bg-card-2); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 13px; color: var(--text-secondary); }
    .stat-value { font-size: 28px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.03em; line-height: 1; }
    .stat-footer { display: flex; align-items: center; gap: 6px; }
    .stat-change { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
    .stat-change.up   { background: rgba(34,197,94,0.12); color: var(--green); }
    .stat-change.down { background: rgba(239,68,68,0.12);  color: var(--red); }
    .stat-change.warn { background: rgba(234,179,8,0.12);  color: var(--yellow); }
    .stat-period { font-size: 11px; color: var(--text-muted); }

    /* CHARTS */
    .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 24px; }

    .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-card); padding: 22px; animation: fadeUp 0.5s ease both; }
    .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .card-title { font-size: 14px; font-weight: 700; color: var(--text-primary); }
    .card-subtitle { font-size: 11.5px; color: var(--text-secondary); margin-top: 2px; }
    .card-action { font-size: 11.5px; color: var(--text-muted); background: var(--bg-card-2); border: 1px solid var(--border); padding: 5px 12px; border-radius: 8px; cursor: pointer; transition: color var(--transition), border-color var(--transition); }
    .card-action:hover { color: var(--text-primary); border-color: var(--border-md); }
    .chart-wrap { position: relative; height: 220px; }

    /* BOTTOM GRID */
    .bottom-grid { display: grid; grid-template-columns: 3fr 1.3fr; gap: 16px; margin-bottom: 24px; }

    /* TABLE */
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th { font-size: 10.5px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; padding: 0 14px 14px; text-align: left; border-bottom: 1px solid var(--border); }
    .data-table td { padding: 13px 14px; font-size: 13px; color: var(--text-secondary); border-bottom: 1px solid var(--border); transition: background var(--transition); }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover td { background: var(--bg-hover); }

    .td-name { display: flex; align-items: center; gap: 10px; }
    .td-avatar { width: 28px; height: 28px; border-radius: 50%; background: var(--bg-card-2); border: 1px solid var(--border-md); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: var(--text-secondary); flex-shrink: 0; }
    .td-fullname { font-size: 13px; font-weight: 600; color: var(--text-primary); }
    .td-email-sub { font-size: 11px; color: var(--text-muted); }

    .badge { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
    .badge-active   { background: rgba(34,197,94,0.10); color: var(--green); }
    .badge-inactive { background: rgba(255,255,255,0.05); color: var(--text-muted); }
    .badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

    .role-tag { font-size: 11px; font-weight: 500; color: var(--text-muted); background: var(--bg-card-2); border: 1px solid var(--border); padding: 3px 10px; border-radius: 6px; }

    /* ACTIVITY */
    .activity-list { display: flex; flex-direction: column; gap: 2px; }
    .activity-item { display: flex; align-items: flex-start; gap: 12px; padding: 10px 8px; border-radius: 10px; transition: background var(--transition); cursor: default; }
    .activity-item:hover { background: var(--bg-hover); }
    .activity-icon { width: 30px; height: 30px; border-radius: 8px; background: var(--bg-card-2); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--text-secondary); flex-shrink: 0; margin-top: 1px; }
    .activity-text { flex: 1; }
    .activity-title { font-size: 12.5px; font-weight: 600; color: var(--text-primary); line-height: 1.4; }
    .activity-time  { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

    /* RESPONSIVE */
    @media (max-width: 1100px) {
      .stats-grid { grid-template-columns: repeat(2,1fr); }
      .charts-grid { grid-template-columns: 1fr; }
      .bottom-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 700px) {
      .stats-grid { grid-template-columns: 1fr; }
      .content-wrap { padding: 16px; }
      .topbar { flex-wrap: wrap; gap: 12px; }
      .date-badge { display: none; }
      .sidebar { width: var(--sidebar-collapsed) !important; }
      .sidebar .logo-text,.sidebar .nav-text,.sidebar .nav-badge,.sidebar .user-info,.sidebar .nav-label { opacity: 0; pointer-events: none; }
      .main { margin-left: var(--sidebar-collapsed) !important; }
    }
  </style>

</head>

<body>
<div class="layout">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="logo-area">
        <div class="logo-circle"><i class="fa-solid fa-bolt"></i></div>
        <div class="logo-text">
          <span class="logo-name">NexAdmin</span>
          <span class="logo-tagline">Control Panel</span>
        </div>
      </div>
      <button class="collapse-btn" id="collapseBtn">
        <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
      </button>
    </div>

    <nav class="sidebar-nav">
      <span class="nav-label">Main Menu</span>
      <a class="nav-item active" href="#" data-tooltip="Dashboard">
        <span class="nav-icon"><i class="fa-solid fa-house"></i></span>
        <span class="nav-text">Dashboard</span>
      </a>
      <a class="nav-item" href="#" data-tooltip="Calendar">
        <span class="nav-icon"><i class="fa-solid fa-calendar-days"></i></span>
        <span class="nav-text">Calendar</span>
      </a>
      <a class="nav-item" href="#" data-tooltip="Notifications">
        <span class="nav-icon"><i class="fa-solid fa-bell"></i></span>
        <span class="nav-text">Notifications</span>
        <span class="nav-badge">4</span>
      </a>
      <a class="nav-item" href="#" data-tooltip="Team">
        <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
        <span class="nav-text">Team</span>
      </a>
      <div class="nav-divider"></div>
      <span class="nav-label">Insights</span>
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
      </a>
    </nav>

    <div class="sidebar-footer">
      <a class="nav-item" href="#" data-tooltip="Profile" style="padding:10px 12px;">
        <div class="user-avatar-sm">AN</div>
        <div class="user-info">
          <div class="user-name-sm">Ahmad Nabil</div>
          <div class="user-role-sm">Super Admin</div>
        </div>
      </a>
      <a class="nav-item" href="#" data-tooltip="Logout" style="color:#555;">
        <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
        <span class="nav-text" style="color:#555;">Logout</span>
      </a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main" id="main">
    <div class="content-wrap">

      <!-- TOP BAR -->
      <div class="topbar">
        <div class="topbar-left">
          <h1>Dashboard</h1>
          <p>Welcome back, Ahmad — here's what's happening today.</p>
        </div>
        <div class="topbar-right">
          <div class="date-badge">
            <i class="fa-regular fa-calendar" style="font-size:12px;"></i>
            <span id="todayDate"></span>
          </div>
          <div class="notif-btn">
            <i class="fa-solid fa-bell" style="font-size:13px;"></i>
            <span class="notif-dot"></span>
          </div>
          <div class="topbar-user">
            <div class="topbar-avatar">AN</div>
            <span class="topbar-username">Ahmad Nabil</span>
          </div>
        </div>
      </div>

      <!-- STAT CARDS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-header">
            <span class="stat-label">Total Produk</span>
            <div class="stat-icon"><i class="fa-solid fa-box"></i></div>
          </div>
          <div class="stat-value">1,284</div>
          <div class="stat-footer">
            <span class="stat-change up"><i class="fa-solid fa-arrow-up" style="font-size:9px;"></i> +12%</span>
            <span class="stat-period">vs bulan lalu</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-header">
            <span class="stat-label">Stok Tersedia</span>
            <div class="stat-icon"><i class="fa-solid fa-warehouse"></i></div>
          </div>
          <div class="stat-value">847</div>
          <div class="stat-footer">
            <span class="stat-change up"><i class="fa-solid fa-arrow-up" style="font-size:9px;"></i> +5%</span>
            <span class="stat-period">vs bulan lalu</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-header">
            <span class="stat-label">Stok Menipis</span>
            <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
          </div>
          <div class="stat-value">43</div>
          <div class="stat-footer">
            <span class="stat-change warn"><i class="fa-solid fa-arrow-up" style="font-size:9px;"></i> +8 item</span>
            <span class="stat-period">perlu restock</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-header">
            <span class="stat-label">Transaksi Hari Ini</span>
            <div class="stat-icon"><i class="fa-solid fa-receipt"></i></div>
          </div>
          <div class="stat-value">128</div>
          <div class="stat-footer">
            <span class="stat-change down"><i class="fa-solid fa-arrow-down" style="font-size:9px;"></i> -3%</span>
            <span class="stat-period">vs kemarin</span>
          </div>
        </div>
      </div>

      <!-- CHARTS -->
      <div class="charts-grid">
        <div class="card" style="animation-delay:.25s">
          <div class="card-header">
            <div>
              <div class="card-title">Sales Overview</div>
              <div class="card-subtitle">Tren penjualan 6 bulan terakhir</div>
            </div>
            <button class="card-action">Export</button>
          </div>
          <div class="chart-wrap"><canvas id="lineChart"></canvas></div>
        </div>
        <div class="card" style="animation-delay:.3s">
          <div class="card-header">
            <div>
              <div class="card-title">Monthly Stats</div>
              <div class="card-subtitle">Transaksi per bulan</div>
            </div>
          </div>
          <div class="chart-wrap"><canvas id="barChart"></canvas></div>
        </div>
      </div>

      <!-- TABLE + ACTIVITY -->
      <div class="bottom-grid">

        <div class="card" style="animation-delay:.35s; padding:22px 0;">
          <div class="card-header" style="padding:0 22px;">
            <div>
              <div class="card-title">Data Pengguna</div>
              <div class="card-subtitle">Daftar akun terdaftar</div>
            </div>
            <button class="card-action">Lihat Semua</button>
          </div>
          <div style="overflow-x:auto; margin-top:4px;">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><div class="td-name"><div class="td-avatar">AN</div><div><div class="td-fullname">Ahmad Nabil</div><div class="td-email-sub">ahmad@nexadmin.io</div></div></div></td>
                  <td>ahmad@nexadmin.io</td>
                  <td><span class="role-tag">Super Admin</span></td>
                  <td><span class="badge badge-active"><span class="badge-dot"></span>Active</span></td>
                </tr>
                <tr>
                  <td><div class="td-name"><div class="td-avatar">SR</div><div><div class="td-fullname">Siti Rahma</div><div class="td-email-sub">siti@nexadmin.io</div></div></div></td>
                  <td>siti@nexadmin.io</td>
                  <td><span class="role-tag">Manager</span></td>
                  <td><span class="badge badge-active"><span class="badge-dot"></span>Active</span></td>
                </tr>
                <tr>
                  <td><div class="td-name"><div class="td-avatar">BP</div><div><div class="td-fullname">Budi Prasetyo</div><div class="td-email-sub">budi@nexadmin.io</div></div></div></td>
                  <td>budi@nexadmin.io</td>
                  <td><span class="role-tag">Editor</span></td>
                  <td><span class="badge badge-inactive"><span class="badge-dot"></span>Inactive</span></td>
                </tr>
                <tr>
                  <td><div class="td-name"><div class="td-avatar">DK</div><div><div class="td-fullname">Dewi Kusuma</div><div class="td-email-sub">dewi@nexadmin.io</div></div></div></td>
                  <td>dewi@nexadmin.io</td>
                  <td><span class="role-tag">Viewer</span></td>
                  <td><span class="badge badge-active"><span class="badge-dot"></span>Active</span></td>
                </tr>
                <tr>
                  <td><div class="td-name"><div class="td-avatar">RA</div><div><div class="td-fullname">Rizky Aditya</div><div class="td-email-sub">rizky@nexadmin.io</div></div></div></td>
                  <td>rizky@nexadmin.io</td>
                  <td><span class="role-tag">Editor</span></td>
                  <td><span class="badge badge-inactive"><span class="badge-dot"></span>Inactive</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card" style="animation-delay:.4s;">
          <div class="card-header">
            <div>
              <div class="card-title">Recent Activity</div>
              <div class="card-subtitle">Aktivitas terbaru</div>
            </div>
          </div>
          <div class="activity-list">
            <div class="activity-item">
              <div class="activity-icon"><i class="fa-solid fa-user-plus"></i></div>
              <div class="activity-text">
                <div class="activity-title">User baru mendaftar</div>
                <div class="activity-time">2 menit yang lalu</div>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon"><i class="fa-solid fa-cart-shopping"></i></div>
              <div class="activity-text">
                <div class="activity-title">Order #4821 dibuat</div>
                <div class="activity-time">15 menit yang lalu</div>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
              <div class="activity-text">
                <div class="activity-title">Stok produk menipis</div>
                <div class="activity-time">1 jam yang lalu</div>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
              <div class="activity-text">
                <div class="activity-title">Invoice #INV-009 dibayar</div>
                <div class="activity-time">2 jam yang lalu</div>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon"><i class="fa-solid fa-gear"></i></div>
              <div class="activity-text">
                <div class="activity-title">Pengaturan sistem diubah</div>
                <div class="activity-time">3 jam yang lalu</div>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon"><i class="fa-solid fa-shield-halved"></i></div>
              <div class="activity-text">
                <div class="activity-title">Login dari perangkat baru</div>
                <div class="activity-time">5 jam yang lalu</div>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon"><i class="fa-solid fa-box"></i></div>
              <div class="activity-text">
                <div class="activity-title">Produk baru ditambahkan</div>
                <div class="activity-time">Kemarin, 22:14</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>

</div>

<script>
  /* Collapse */
  const sidebar = document.getElementById('sidebar');
  const main    = document.getElementById('main');
  document.getElementById('collapseBtn').addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    main.classList.toggle('shifted');
  });

  /* Active nav */
  document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
      document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
      this.classList.add('active');
    });
  });

  /* Date */
  document.getElementById('todayDate').textContent = new Date().toLocaleDateString('id-ID', {
    weekday:'short', day:'numeric', month:'short', year:'numeric'
  });

  /* Chart global defaults */
  Chart.defaults.color = '#555';
  Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
  Chart.defaults.font.size = 11;

  const gridColor = 'rgba(255,255,255,0.05)';
  const tickColor = '#444';

  /* Line Chart */
  new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
      labels: ['Februari','Maret','April','Mei','Juni','Juli'],
      datasets: [
        {
          label: 'Penjualan',
          data: [42,68,55,90,74,112],
          borderColor: '#ffffff',
          backgroundColor: 'rgba(255,255,255,0.04)',
          borderWidth: 2,
          pointRadius: 4,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#111',
          pointBorderWidth: 2,
          fill: true,
          tension: 0.4,
        },
        {
          label: 'Target',
          data: [50,60,70,80,85,100],
          borderColor: '#333',
          backgroundColor: 'transparent',
          borderWidth: 1.5,
          borderDash: [5,4],
          pointRadius: 0,
          tension: 0.4,
        }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { position:'top', align:'end', labels:{ boxWidth:10, padding:14, color:'#666' } },
        tooltip: { backgroundColor:'#1a1a1a', borderColor:'#2a2a2a', borderWidth:1, padding:10, titleColor:'#fff', bodyColor:'#888' }
      },
      scales: {
        x: { grid:{ color:gridColor }, ticks:{ color:tickColor } },
        y: { grid:{ color:gridColor }, ticks:{ color:tickColor }, beginAtZero:true }
      }
    }
  });

  /* Bar Chart */
  new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
      labels: ['Feb','Mar','Apr','Mei','Jun','Jul'],
      datasets: [{
        label: 'Transaksi',
        data: [88,120,95,140,108,128],
        backgroundColor: 'rgba(255,255,255,0.10)',
        hoverBackgroundColor: 'rgba(255,255,255,0.22)',
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { display:false },
        tooltip: { backgroundColor:'#1a1a1a', borderColor:'#2a2a2a', borderWidth:1, padding:10, titleColor:'#fff', bodyColor:'#888' }
      },
      scales: {
        x: { grid:{ display:false }, ticks:{ color:tickColor } },
        y: { grid:{ color:gridColor }, ticks:{ color:tickColor }, beginAtZero:true }
      }
    }
  });
</script>
</body>
</html>