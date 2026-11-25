<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: /smart-ed/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - SMART ED</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="dashboard-layout">
    <nav class="sidebar">
        <div class="brand-area">
            <h2>Apotek Bintang Surya</h2>
            <p>Smart ED System</p>
        </div>
        
        <div style="margin-top: 2rem; display: flex; flex-direction: column; gap: 8px;">
            <a href="/smart-ed/index.php" class="btn btn-primary" style="justify-content: flex-start;">
                <i class="fas fa-home"></i> Beranda
            </a>
            <a href="/smart-ed/inventaris/index.php" class="btn btn-outline" style="color: white; border: none; background: none; justify-content: flex-start;">
                <i class="fas fa-boxes"></i> Inventaris
            </a>
            <a href="/smart-ed/inventaris/tambah.php" class="btn btn-outline" style="color: white; border: none; background: none; justify-content: flex-start;">
                <i class="fas fa-plus-circle"></i> Input Stok
            </a>
        </div>
    </nav>

    <div style="flex: 1; display: flex; flex-direction: column;">
        
        <header class="top-header">
            <div class="search-box">
                <i class="fas fa-search" style="color: var(--text-muted)"></i>
                <input type="text" placeholder="Cari info sistem...">
            </div>
            
            <div class="profile-section">
                <div style="text-align: right">
                    <span style="display: block; font-size: 14px; font-weight: 600;">amin_ajis01</span>
                    <small style="color: var(--text-muted); font-size: 11px;">Apoteker</small>
                </div>
                <div class="avatar">
                    </div>
            </div>
        </header>

        <main class="content">
            <div class="mb-4">
                <h1 style="font-size: 26px; font-weight: 700;">Selamat Datang, Admin!</h1>
                <p style="color: var(--text-muted);">Pantau kondisi stok apotek Anda hari ini.</p>
            </div>

            <div class="stats-grid">
                <div class="card stat-card">
                    <div class="stat-icon" style="background: #e0f2fe; color: #0369a1;">📦</div>
                    <div class="stat-info">
                        <h3>Total Obat</h3>
                        <p>124 Item</p>
                    </div>
                </div>

                <div class="card stat-card" style="border-left: 4px solid #ef4444;">
                    <div class="stat-icon" style="background: #fee2e2; color: #991b1b;">⚠️</div>
                    <div class="stat-info">
                        <h3>Hampir Expired</h3>
                        <p>12 Batch</p>
                    </div>
                </div>

                <div class="card stat-card">
                    <div class="stat-icon" style="background: #dcfce7; color: #166534;">✅</div>
                    <div class="stat-info">
                        <h3>Stok Aman</h3>
                        <p>112 Item</p>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 20px; padding: 30px; text-align: center;">
                <h2 style="color: var(--primary);">Smart ED (Earliest Deadline)</h2>
                <p style="color: var(--text-muted); max-width: 600px; margin: 10px auto;">                    
            </div>
        </main>
    </div>
</div>

</body>
</html>