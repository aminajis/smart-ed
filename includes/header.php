<?php
// 1. Jalankan session untuk membaca data user yang sedang login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Proteksi Halaman: Jika tidak ada session login, tendang balik ke login.php
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: /smart-ed/login.php");
    exit;
}

// 3. Ambil data user yang aktif dari session login
$username_login = $_SESSION['username'] ?? '';
$nama_lengkap   = $_SESSION['nama_lengkap'] ?? $username_login;
$role_login     = $_SESSION['role'] ?? 'Admin';

// 4. Konfigurasi URL dasar aplikasi
$base_url = '/smart-ed/';
$current_page = basename($_SERVER['PHP_SELF']);

$request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$active_home = ($request_uri === '/smart-ed/' || $script_name === '/smart-ed/index.php' || $request_uri === '/smart-ed/index.php');
$active_inventaris = strpos($request_uri, '/smart-ed/inventaris/') === 0;
$active_monitoring = strpos($request_uri, '/smart-ed/monitoring/') === 0;
$active_laporan = strpos($request_uri, '/smart-ed/laporan.php') === 0;
$active_pengaturan = strpos($request_uri, '/smart-ed/pengaturan.php') === 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMART ED - Apotek Bintang Surya</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { margin: 0; font-family: 'Inter', sans-serif; background: #f8fafc; overflow-x: hidden; }
        .dashboard-container { display: flex; min-height: 100vh; width: 100%; }
        
        .sidebar { 
            width: 260px; 
            background: white; 
            border-right: 1px solid #e2e8f0; 
            flex-shrink: 0; 
            position: fixed; 
            top: 0; 
            left: 0;
            height: 100vh; 
            display: flex; 
            flex-direction: column; 
            z-index: 1000;
        }

        .main-content { 
            flex: 1; 
            margin-left: 260px; 
            min-width: 0; 
            display: flex; 
            flex-direction: column; 
            background: #f8fafc;
        }

        .nav-item { padding: 12px 25px; margin: 4px 15px; color: #64748b; text-decoration: none; border-radius: 10px; display: flex; align-items: center; gap: 12px; font-weight: 500; transition: 0.3s; }
        .nav-item:hover { background: #f8fafc; color: #2B5B7C; }
        .nav-item.active { background: #f1f5f9; color: #2B5B7C; font-weight: 700; border-left: 4px solid #2B5B7C; border-radius: 4px 10px 10px 4px; }
        
        .top-bar { 
            background: white; 
            padding: 15px 40px; 
            border-bottom: 1px solid #e2e8f0; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            position: sticky;
            top: 0;
            z-index: 999;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div style="padding: 30px 25px;">
                <strong style="font-size: 18px; color: #1e293b; display: block;">Apotek Bintang Surya</strong>
                <span style="font-size: 10px; color: #94a3b8; font-weight: 700; letter-spacing: 1px;">SMART ED</span>
            </div>
            
            <nav style="flex: 1;">
                <a href="/smart-ed/index.php" class="nav-item <?php echo $active_home ? 'active' : ''; ?>">
                    <i class="fa fa-home"></i> Home
                </a>

                <a href="/smart-ed/inventaris/index.php" class="nav-item <?php echo $active_inventaris ? 'active' : ''; ?>">
                    <i class="fa fa-box"></i> Inventaris
                </a>

                <a href="/smart-ed/monitoring/index.php" class="nav-item <?php echo $active_monitoring ? 'active' : ''; ?>">
                    <i class="fa fa-clock"></i> Monitoring ED
                </a>

                <a href="/smart-ed/laporan.php" class="nav-item <?php echo $active_laporan ? 'active' : ''; ?>">
                    <i class="fa fa-file-alt"></i> Laporan
                </a>
                
                <a href="/smart-ed/pengaturan.php" class="nav-item <?php echo $active_pengaturan ? 'active' : ''; ?>">
                    <i class="fa fa-cog"></i> Pengaturan
                </a>
            </nav>

            <div style="padding: 20px; border-top: 1px solid #e2e8f0;">
                <a href="/smart-ed/logout.php" class="nav-item" style="color: #ef4444;"><i class="fa fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <form action="/smart-ed/inventaris/index.php" method="GET" style="position: relative;">
                    <i class="fa fa-search" style="position: absolute; left: 15px; top: 13px; color: #94a3b8;"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Cari obat atau batch..." style="padding: 10px 15px 10px 45px; width: 300px; background: #f1f5f9; border: none; border-radius: 8px;">
                </form>
                
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="text-align: right;">
                        <span style="font-weight: 700; font-size: 14px; color: #1e293b; display: block;"><?= htmlspecialchars($nama_lengkap) ?></span>
                        <span style="font-size: 11px; color: #64748b; text-transform: uppercase;"><?= htmlspecialchars($role_login) ?></span>
                    </div>
                    <div style="width: 38px; height: 38px; background: #e2e8f0; border-radius: 10px; display:flex; align-items:center; justify-content:center;">
                         <i class="fa fa-user" style="color:#64748b"></i>
                    </div>
                </div>
            </header>