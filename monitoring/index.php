<?php 
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php'; 

$tgl_sekarang = date('Y-m-d');
$setting = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT stok_min, ed_alert FROM setting_profile WHERE id=1')) ?: [];
$stok_min = max(0, (int)($setting['stok_min'] ?? 20));
$ed_alert = max(1, (int)($setting['ed_alert'] ?? 3));
$tgl_3_bulan  = date('Y-m-d', strtotime("+$ed_alert months"));

$range = $_GET['range'] ?? 'all';
$search = trim($_GET['search'] ?? '');
$per_page = 5;
$expired_page = max(1, (int)($_GET['expired_page'] ?? 1));
$warning_page = max(1, (int)($_GET['warning_page'] ?? 1));
$aman_page = max(1, (int)($_GET['aman_page'] ?? 1));

mysqli_query($conn, "UPDATE inventaris SET status='Tersedia' WHERE status IS NULL OR status='' ");
$active_status = "(status='Tersedia' OR status='Aktif')";

function monitoringPageData($conn, $where, $search, $page, $per_page) {
    $search_sql = '';$params=[];$types='';
    if ($search !== '') {
        $search_sql = ' AND (nama_obat LIKE ? OR no_batch LIKE ? OR kategori LIKE ?)';

        $like='%'.$search.'%';$params=[$like,$like,$like];$types='sss';
    }
    $count_sql='SELECT COUNT(*) AS total FROM inventaris WHERE '.$where.$search_sql;
    if ($search !== '') {
        $s=mysqli_prepare($conn,$count_sql);
        mysqli_stmt_bind_param($s,$types,...$params);
        mysqli_stmt_execute($s);
        $total=(int)mysqli_fetch_assoc(mysqli_stmt_get_result($s))['total'];
    }
    else {
        $total=(int)mysqli_fetch_assoc(mysqli_query($conn,$count_sql))['total'];
    }
    $pages=max(1,(int)ceil($total/$per_page));$page=min($page,$pages);$offset=($page-1)*$per_page;
    $list_sql='SELECT * FROM inventaris WHERE '.$where.$search_sql.' ORDER BY expired ASC, nama_obat ASC LIMIT '.$per_page.' OFFSET '.$offset;
    if ($search !== '') {
        $s=mysqli_prepare($conn,$list_sql);
        mysqli_stmt_bind_param($s,$types,...$params);
        mysqli_stmt_execute($s);
        $list=mysqli_stmt_get_result($s);
    }
    else {
        $list=mysqli_query($conn,$list_sql);
    }
    return [$list,$pages,$page,$total];
}

$expired_data=monitoringPageData($conn,$active_status." AND expired <= '$tgl_sekarang'",$search,$expired_page,$per_page);
$warning_data=monitoringPageData($conn,$active_status." AND expired > '$tgl_sekarang' AND expired <= '$tgl_3_bulan'",$search,$warning_page,$per_page);
$aman_data=monitoringPageData($conn,$active_status." AND expired > '$tgl_3_bulan'",$search,$aman_page,$per_page);
$q1=$expired_data[0];$q2=$warning_data[0];$q3=$aman_data[0];

$count_expired=(int)mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM inventaris WHERE $active_status AND expired <= '$tgl_sekarang'"))['total'];
$count_warning=(int)mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM inventaris WHERE $active_status AND expired > '$tgl_sekarang' AND expired <= '$tgl_3_bulan'"))['total'];
$count_aman=(int)mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM inventaris WHERE $active_status AND expired > '$tgl_3_bulan'"))['total'];
$count_stok_min=(int)mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM inventaris WHERE $active_status AND sisa_stok <= $stok_min"))['total'];
?>

<style>
    .main-wrapper { padding: 30px; background: #f8fafc; min-height: 100vh; font-family: 'Inter', sans-serif; }
    .header-section { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; }
    .filter-box { background: white; padding: 15px 20px; border-radius: 12px; border: 1px solid #eef2f6; display: flex; align-items: center; gap: 10px; }
    .filter-box select { padding: 8px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; outline: none; }
    .btn-filter { background: #2B5B7C; color: white; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; }

    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
    .card-stat { background: white; padding: 20px; border-radius: 15px; border-left: 5px solid #2B5B7C; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
    .stat-val { font-size: 28px; font-weight: 800; color: #1e293b; margin: 0; }
    .stat-label { font-size: 13px; color: #64748b; margin-top: 5px; }

    .status-title { font-size: 15px; font-weight: 700; margin: 30px 0 15px; display: flex; align-items: center; gap: 10px; }
    .table-card { background: white; border-radius: 15px; border: 1px solid #eef2f6; overflow: hidden; margin-bottom: 25px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f8fafc; padding: 12px 20px; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #eef2f6; }
    td { padding: 15px 20px; font-size: 14px; border-bottom: 1px solid #f8fafc; }

    .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    .dot-red { background: #ef4444; box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1); }
    .dot-yellow { background: #f59e0b; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); }
    .dot-green { background: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }
    
    .badge-date { padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 12px; }
    .bg-red { background: #fee2e2; color: #ef4444; }
    .bg-yellow { background: #fef3c7; color: #d97706; }
    .bg-green { background: #f0fdf4; color: #16a34a; }
</style>

<div class="main-wrapper">
    <div class="header-section">
        <div>
            <h1 style="margin:0; font-size: 24px; font-weight: 800; color: #1e293b;">Monitoring Expiry Date</h1>
            <p style="color: #64748b; font-size: 14px; margin: 5px 0 0;">Kelola keamanan obat berdasarkan masa berlaku.</p>
        </div>
        
        <div class="filter-box">
            <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama atau batch" style="padding:8px;border:1px solid #e2e8f0;border-radius:8px">
                <span style="font-size: 13px; font-weight: 700;">Filter:</span>
                <select name="range">
                    <option value="all" <?= $range == 'all' ? 'selected' : '' ?>>Semua Status</option>
                    <option value="expired" <?= $range == 'expired' ? 'selected' : '' ?>>Sudah Expired</option>
                    <option value="warning" <?= $range == 'warning' ? 'selected' : '' ?>>Segera Expired (3 Bln)</option>
                </select>
                <button type="submit" class="btn-filter">Terapkan</button>
            </form>
        </div>
    </div>

    <div class="stats-grid">
        <div class="card-stat" style="border-left-color: #ef4444;">
            <div><p class="stat-val"><?= $count_expired ?></p><p class="stat-label">Kedaluwarsa</p></div>
            <span style="font-size: 10px; font-weight: 800; color: #ef4444; background: #fee2e2; padding: 4px 8px; border-radius: 5px;">MENDESAK</span>
        </div>
        <div class="card-stat" style="border-left-color: #f59e0b;">
            <div><p class="stat-val"><?= $count_warning ?></p><p class="stat-label">Segera Expired</p></div>
            <span style="font-size: 10px; font-weight: 800; color: #d97706; background: #fef3c7; padding: 4px 8px; border-radius: 5px;">PERINGATAN</span>
        </div>
        <div class="card-stat" style="border-left-color: #10b981;">
            <div><p class="stat-val"><?= $count_aman ?></p><p class="stat-label">Status Aman</p></div>
            <span style="font-size: 10px; font-weight: 800; color: #16a34a; background: #f0fdf4; padding: 4px 8px; border-radius: 5px;">OPTIMAL</span>
        </div>
        <div class="card-stat" style="border-left-color: #3b82f6;">
            <div><p class="stat-val"><?= $count_stok_min ?></p><p class="stat-label">Stok Menipis (&le; <?= $stok_min ?>)</p></div>
            <span style="font-size:10px;font-weight:800;color:#2563eb;background:#dbeafe;padding:4px 8px;border-radius:5px;">PERLU RESTOCK</span>
        </div>
    </div>

    <?php if($range == 'all' || $range == 'expired'): ?>
    <div class="status-title" style="color: #ef4444;"><span class="dot dot-red"></span> Status: Kedaluwarsa</div>
    <div class="table-card">
        <table>
            <thead>
                <tr><th>Nama Produk & Batch</th><th>Kategori</th><th>Stok</th><th>Tanggal ED</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php 
                if(mysqli_num_rows($q1) == 0) echo "<tr><td colspan='5' style='text-align:center; padding:20px; color:#94a3b8;'>Data tidak ditemukan</td></tr>";
                while($r = mysqli_fetch_array($q1)) { ?>
                <tr>
                    <td width="30%"><strong><?= $r['nama_obat'] ?></strong><br><small style="color:#94a3b8"><?= $r['no_batch'] ?></small></td>
                    <td width="20%"><?= $r['kategori'] ?></td>
                    <td width="15%"><?= (int)$r['sisa_stok'] ?> <?= htmlspecialchars($r['satuan'] ?? 'Unit') ?></td>
                    <td width="25%"><span class="badge-date bg-red"><?= date('d M Y', strtotime($r['expired'])) ?></span></td>
                    <td><a href="/smart-ed/inventaris/karantina.php?id=<?= $r['id'] ?>" onclick="return confirm('Pindahkan obat ini ke Gudang Karantina?')" style="color:#ef4444;font-weight:700;text-decoration:none;">Karantina</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <div style="text-align:right;margin-top:15px;display:flex;justify-content:flex-end;gap:6px;align-items:center">
        <?php if($expired_page>1): ?><a href="?range=<?= urlencode($range) ?>&search=<?= urlencode($search) ?>&expired_page=<?= $expired_page-1 ?>" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:#475569">Prev</a><?php endif; ?>
        <?php for($i=1;$i<=$expired_data[1];$i++): ?><a href="?range=<?= urlencode($range) ?>&search=<?= urlencode($search) ?>&expired_page=<?= $i ?>" style="padding:8px 12px;border-radius:8px;text-decoration:none;<?= $i==$expired_page?'background:#2B5B7C;color:white':'border:1px solid #e2e8f0;color:#475569' ?>"><?= $i ?></a><?php endfor; ?>
        <?php if($expired_page<$expired_data[1]): ?><a href="?range=<?= urlencode($range) ?>&search=<?= urlencode($search) ?>&expired_page=<?= $expired_page+1 ?>" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:#475569">Next</a><?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if($range == 'all' || $range == 'warning'): ?>
    <div class="status-title" style="color: #f59e0b;"><span class="dot dot-yellow"></span> Status: Segera Kedaluwarsa (Dalam 3 Bulan)</div>
    <div class="table-card">
        <table>
            <tbody>
                <?php 
                if(mysqli_num_rows($q2) == 0) echo "<tr><td colspan='5' style='text-align:center; padding:20px; color:#94a3b8;'>Data tidak ditemukan</td></tr>";
                while($r = mysqli_fetch_array($q2)) { ?>
                <tr>
                    <td width="30%"><strong><?= $r['nama_obat'] ?></strong><br><small style="color:#94a3b8"><?= $r['no_batch'] ?></small></td>
                    <td width="20%"><?= $r['kategori'] ?></td>
                    <td width="15%"><?= (int)$r['sisa_stok'] ?> <?= htmlspecialchars($r['satuan'] ?? 'Unit') ?></td>
                    <td width="25%"><span class="badge-date bg-yellow"><?= date('d M Y', strtotime($r['expired'])) ?></span></td>
                    <td><a href="/smart-ed/inventaris/edit.php?id=<?= $r['id'] ?>" style="color:#d97706;font-weight:700;text-decoration:none;">Prioritaskan FEFO</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <div style="text-align:right;margin-top:15px;display:flex;justify-content:flex-end;gap:6px;align-items:center">
        <?php if($warning_page>1): ?><a href="?range=<?= urlencode($range) ?>&search=<?= urlencode($search) ?>&warning_page=<?= $warning_page-1 ?>" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:#475569">Prev</a><?php endif; ?>
        <?php for($i=1;$i<=$warning_data[1];$i++): ?><a href="?range=<?= urlencode($range) ?>&search=<?= urlencode($search) ?>&warning_page=<?= $i ?>" style="padding:8px 12px;border-radius:8px;text-decoration:none;<?= $i==$warning_page?'background:#2B5B7C;color:white':'border:1px solid #e2e8f0;color:#475569' ?>"><?= $i ?></a><?php endfor; ?>
        <?php if($warning_page<$warning_data[1]): ?><a href="?range=<?= urlencode($range) ?>&search=<?= urlencode($search) ?>&warning_page=<?= $warning_page+1 ?>" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:#475569">Next</a><?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if($range == 'all'): ?>
    <div class="status-title" style="color: #10b981;"><span class="dot dot-green"></span> Status: Aman</div>
    <div class="table-card">
        <table>
            <tbody>
                <?php 
                if(mysqli_num_rows($q3) == 0) echo "<tr><td colspan='5' style='text-align:center; padding:20px; color:#94a3b8;'>Data tidak ditemukan</td></tr>";
                while($r = mysqli_fetch_array($q3)) { ?>
                <tr>
                    <td width="30%"><strong><?= $r['nama_obat'] ?></strong><br><small style="color:#94a3b8"><?= $r['no_batch'] ?></small></td>
                    <td width="20%"><?= $r['kategori'] ?></td>
                    <td width="15%"><?= $r['sisa_stok'] ?> <?= htmlspecialchars($r['satuan'] ?? 'Unit') ?></td>
                    <td width="25%"><span class="badge-date bg-green"><?= date('d M Y', strtotime($r['expired'])) ?></span></td>
                    <td><a href="/smart-ed/inventaris/edit.php?id=<?= $r['id'] ?>" style="color:#16a34a;font-weight:700;text-decoration:none;">Tinjau</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <div style="text-align:right;margin-top:15px;display:flex;justify-content:flex-end;gap:6px;align-items:center">
        <?php if($aman_page>1): ?><a href="?range=<?= urlencode($range) ?>&search=<?= urlencode($search) ?>&aman_page=<?= $aman_page-1 ?>" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:#475569">Prev</a><?php endif; ?>
        <?php for($i=1;$i<=$aman_data[1];$i++): ?><a href="?range=<?= urlencode($range) ?>&search=<?= urlencode($search) ?>&aman_page=<?= $i ?>" style="padding:8px 12px;border-radius:8px;text-decoration:none;<?= $i==$aman_page?'background:#2B5B7C;color:white':'border:1px solid #e2e8f0;color:#475569' ?>"><?= $i ?></a><?php endfor; ?>
        <?php if($aman_page<$aman_data[1]): ?><a href="?range=<?= urlencode($range) ?>&search=<?= urlencode($search) ?>&aman_page=<?= $aman_page+1 ?>" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:#475569">Next</a><?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>