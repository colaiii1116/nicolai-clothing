<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function.php';

require_admin();
$adminUser = current_user();

if (!isset($adminTitle)) $adminTitle = 'Admin Dashboard';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($adminTitle) ?> | Nicolai Admin</title>
<link rel="stylesheet" href="../css/style.css">
<style>
.admin-shell {
  min-height: 100vh;
  background-color: var(--ink);
  color: var(--paper);
  display: flex;
  flex-direction: column;
}
.admin-navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 5vw;
  background: rgba(20, 18, 16, 0.95);
  border-bottom: 1px solid rgba(193, 138, 53, 0.25);
  position: sticky;
  top: 0;
  z-index: 100;
}
.admin-nav-links {
  display: flex;
  gap: 24px;
  align-items: center;
}
.admin-nav-links a {
  color: #b8afa3;
  text-decoration: none;
  font-size: 11px;
  letter-spacing: 0.15em;
  font-weight: 500;
  transition: color 0.2s;
}
.admin-nav-links a:hover,
.admin-nav-links a.active {
  color: var(--gold);
}
.admin-body {
  flex: 1;
  padding: 40px 5vw;
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
  box-sizing: border-box;
}
.stat-card {
  background: rgba(30, 27, 24, 0.85);
  border: 1px solid rgba(193, 138, 53, 0.22);
  padding: 24px;
  border-radius: 2px;
}
.stat-card span {
  display: block;
  font-size: 11px;
  color: #a39b90;
  letter-spacing: 0.15em;
  margin-bottom: 8px;
}
.stat-card strong {
  display: block;
  font-family: 'Cormorant Garamond', serif;
  font-size: 36px;
  color: #f6f4ef;
  font-weight: 500;
}
.stat-card small {
  color: var(--gold);
  font-size: 11px;
}
.admin-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
  text-align: left;
  background: rgba(30, 27, 24, 0.75);
  border: 1px solid rgba(193, 138, 53, 0.2);
}
.admin-table th {
  padding: 14px 12px;
  border-bottom: 2px solid rgba(193, 138, 53, 0.3);
  color: var(--gold);
  font-size: 11px;
  letter-spacing: 0.1em;
}
.admin-table td {
  padding: 14px 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  color: #cfc6b7;
}
.admin-table tr:hover td {
  background: rgba(255, 255, 255, 0.02);
}
.badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 2px;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.05em;
}
.badge-pending { background: rgba(245, 127, 23, 0.15); color: #f57f17; border: 1px solid #f57f1740; }
.badge-processing { background: rgba(33, 150, 243, 0.15); color: #2196f3; border: 1px solid #2196f340; }
.badge-shipped { background: rgba(156, 39, 176, 0.15); color: #ab47bc; border: 1px solid #ab47bc40; }
.badge-delivered { background: rgba(76, 175, 80, 0.15); color: #4caf50; border: 1px solid #4caf5040; }
.badge-cancelled { background: rgba(244, 67, 54, 0.15); color: #f44336; border: 1px solid #f4433640; }
.btn-sm {
  padding: 6px 14px;
  font-size: 11px;
  letter-spacing: 0.1em;
  text-decoration: none;
  display: inline-block;
  border-radius: 2px;
}
</style>
</head>
<body class="admin-shell">
<nav class="admin-navbar">
  <div style="display: flex; align-items: center; gap: 15px;">
    <a href="index.php" style="text-decoration: none; color: #fff; font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 600; letter-spacing: 0.1em;">
      NICOLAI <span style="color: var(--gold); font-size: 14px; font-family: Montserrat; letter-spacing: 0.2em;">ADMIN</span>
    </a>
  </div>
  <div class="admin-nav-links">
    <a href="index.php" class="<?= $activeTab === 'dashboard' ? 'active' : '' ?>">DASHBOARD</a>
    <a href="products.php" class="<?= $activeTab === 'products' ? 'active' : '' ?>">PRODUCTS</a>
    <a href="orders.php" class="<?= $activeTab === 'orders' ? 'active' : '' ?>">ORDERS</a>
    <a href="messages.php" class="<?= $activeTab === 'messages' ? 'active' : '' ?>">INQUIRIES</a>
    <a href="../index.php" target="_blank" style="color: #6edb8f;">VIEW STORE ↗</a>
    <a href="../logout.php" style="color: #ff8c8c;">LOG OUT</a>
  </div>
</nav>
<main class="admin-body">
