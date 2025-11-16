<?php
// Start session and fetch admin details
session_start();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'Role';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>NYAPUI Radio — Admin Dashboard</title>
  <link rel="stylesheet" href="../../styles/global.css">
  <link rel="stylesheet" href="../styles/admin.css">
</head>
<body>
  <div class="admin-root">
    <aside class="admin-sidebar">
      <div class="sidebar-logo">
        <!-- Logo component -->
        <img src="../../assets/nyapui.jpeg" alt="Nyapui Radio Logo" class="sidebar-logo-img">
        <h1 class="sidebar-logo-text">NYAPUI RADIO</h1>
      </div>
      <div class="sidebar-header">
        <div class="admin-info">
          <p class="admin-role"><?php echo htmlspecialchars($adminRole); ?></p>
          <h2 class="admin-name"><?php echo htmlspecialchars($adminName); ?></h2>
        </div>
      </div>
      <nav class="sidebar-nav">
        <ul class="sidebar-nav-list">
          <li><a href="#user-management" class="sidebar-nav-link">User Management</a></li>
          <li><a href="#web-management" class="sidebar-nav-link">Web Management</a></li>
          <li><a href="#programs" class="sidebar-nav-link">Programs</a></li>
          <li><a href="#news" class="sidebar-nav-link">News</a></li>
          <li><a href="#notifications" class="sidebar-nav-link">Notifications</a></li>
          <li><a href="#audit-logs" class="sidebar-nav-link">Audit Logs</a></li>
          <li><a href="#settings" class="sidebar-nav-link">Settings</a></li>
          <li><a href="../../pages/login.html" class="sidebar-nav-link">Logout</a></li>
        </ul>
      </nav>
    </aside>

    <div class="admin-main">
      <header class="admin-topbar">
        <h2 class="page-title">Dashboard</h2>
        <div class="topbar-actions">
          <input class="search" type="search" placeholder="Search stations, users...">
          <button class="btn btn-primary">Quick Start</button>
        </div>
      </header>

      <section class="admin-content">
        <!-- Content sections will be added here -->
      </section>

      <footer class="admin-footer">NYAPUI Radio • Admin dashboard</footer>
    </div>
  </div>
</body>
</html>