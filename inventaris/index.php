<?php 
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';

$setting = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT stok_min, ed_alert FROM setting_profile WHERE id=1')) ?: [];
$stok_min = max(0, (int)($setting['stok_min'] ?? 20));
$ed_alert = max(1, (int)($setting['ed_alert'] ?? 3));

$keyword = trim($_GET['q'] ?? '');
$tab = ($_GET['tab'] ?? 'aktif') === 'karantina' ? 'karantina' : 'aktif';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = $tab === 'karantina'
    ? "status='Karantina'"
    : "(status != 'Karantina' OR status IS NULL OR status='')";
$search_sql = '';
$params = [];
$types = '';
if ($keyword !== '') {
    $like = '%' . $keyword . '%';
    $search_sql = ' AND (nama_obat LIKE ? OR no_batch LIKE ? OR kategori LIKE ? OR lokasi LIKE ? OR supplier LIKE ?)';
    $types = 'sssss';
    $params = [$like, $like, $like, $like, $like];
}

$count_sql = 'SELECT COUNT(*) AS total FROM inventaris WHERE ' . $where . $search_sql;
if ($keyword !== '') {
    $stmt_count = mysqli_prepare($conn, $count_sql);
    mysqli_stmt_bind_param($stmt_count, $types, ...$params);
    mysqli_stmt_execute($stmt_count);
    $total_rows = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_count))['total'];
} else {
    $total_rows = (int)mysqli_fetch_assoc(mysqli_query($conn, $count_sql))['total'];
}
$total_pages = max(1, (int)ceil($total_rows / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$list_sql = 'SELECT * FROM inventaris WHERE ' . $where . $search_sql . ' ORDER BY nama_obat ASC, expired ASC LIMIT ' . $per_page . ' OFFSET ' . $offset;
if ($keyword !== '') {
    $stmt = mysqli_prepare($conn, $list_sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $query = mysqli_stmt_get_result($stmt);
} else {
    $query = mysqli_query($conn, $list_sql);
}

$stats_total = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COALESCE(SUM(sisa_stok),0) AS total FROM inventaris'));
$total_item = (int)($stats_total['total'] ?? 0);
$tgl_sekarang = date('Y-m-d');
$tgl_3_bulan = date('Y-m-d', strtotime("+$ed_alert months"));
$stok_menipis = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM inventaris WHERE sisa_stok <= $stok_min AND status != 'Karantina'"));
$mendekati_ed = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM inventaris WHERE status != 'Karantina' AND expired > '$tgl_sekarang' AND expired <= '$tgl_3_bulan'"));
$update_terakhir = date("H:i");
$karantina_count = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM inventaris WHERE status='Karantina'"))['total'];
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root { --primary: #2B5B7C; --bg-light: #f8fafc; --border-color: #e2e8f0; --text-main: #1e293b; --text-muted: #64748b; }
    .main-wrapper { padding: 30px; background: var(--bg-light); min-height: 100vh; font-family: 'Inter', sans-serif; }
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; }
    .btn-tambah { background: var(--primary); color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 16px; border: 1px solid var(--border-color); border-left: 5px solid #cbd5e1; }
    .stat-card.blue { border-left-color: #3b82f6; }
    .stat-card.red { border-left-color: #ef4444; }
    .stat-card.orange { border-left-color: #f59e0b; }
    .stat-card.green { border-left-color: #10b981; }
    .stat-label { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; }
    .stat-value { font-size: 24px; font-weight: 800; color: var(--text-main); }
    .stat-unit { font-size: 14px; color: var(--text-muted); font-weight: 400; }
    .content-card { background: white; border-radius: 16px; border: 1px solid var(--border-color); overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .table-header { display: grid; grid-template-columns: 2.5fr 1.5fr 1fr 1fr 1fr 0.8fr; background: #f8fafc; padding: 15px 25px; border-bottom: 1px solid var(--border-color); font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; }
    .table-row { display: grid; grid-template-columns: 2.5fr 1.5fr 1fr 1fr 1fr 0.8fr; padding: 15px 25px; align-items: center; border-bottom: 1px solid #f1f5f9; transition: 0.2s; }
    .pill { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: #f1f5f9; color: var(--text-muted); }
    .btn-action { width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none; }
    .inventory-tabs{display:flex;gap:8px;margin-bottom:16px}.inventory-tab{padding:10px 16px;border:1px solid #e2e8f0;border-radius:9px;background:white;color:#64748b;text-decoration:none;font-size:13px;font-weight:700}.inventory-tab.active{background:#2B5B7C;border-color:#2B5B7C;color:white}.modern-pagination{display:flex;justify-content:flex-end;align-items:center;gap:6px;padding:14px 20px}.modern-pagination a{min-width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #e2e8f0;border-radius:8px;color:#64748b;text-decoration:none;font-size:12px}.modern-pagination a.active{background:#2B5B7C;color:white;border-color:#2B5B7C}
</style>

<div class="main-wrapper">
    <?php if(isset($_GET['pesan'])): ?>
        <script>
            const pesan = "<?= $_GET['pesan'] ?>";
            if(pesan === "tambah_berhasil") Swal.fire('Berhasil!', 'Data obat ditambahkan.', 'success');
            if(pesan === "update_berhasil") Swal.fire('Berhasil!', 'Data obat diperbarui.', 'success');
            if(pesan === "hapus_berhasil") Swal.fire('Terhapus!', 'Data obat telah dihapus.', 'success');
        </script>
    <?php endif; ?>

    <div class="page-header">
        <div>
            <h1 style="margin:0; font-size: 26px; font-weight: 800; color: var(--text-main);">Inventaris Obat</h1>
            <p style="margin:5px 0 0; color: var(--text-muted); font-size: 14px;">Kelola stok obat, pantau masa berlaku, dan organisasi penyimpanan.</p>
        </div>
        <a href="/smart-ed/inventaris/tambah.php" class="btn-tambah">Tambah Obat Baru</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card blue"><div class="stat-label">Total Stok</div><div class="stat-value"><?= number_format($total_item); ?> <span class="stat-unit">Unit</span></div></div>
        <div class="stat-card red"><div class="stat-label">Stok Menipis</div><div class="stat-value" style="color:#ef4444;"><?= $stok_menipis; ?> <span class="stat-unit">Item</span></div></div>
        <div class="stat-card orange"><div class="stat-label">Mendekati ED</div><div class="stat-value" style="color:#f59e0b;"><?= $mendekati_ed; ?> <span class="stat-unit">Item</span></div></div>
        <div class="stat-card green"><div class="stat-label">Update</div><div class="stat-value"><?= $update_terakhir; ?> <span class="stat-unit">WIB</span></div></div>
    </div>

    <div class="inventory-tabs">
        <a href="/smart-ed/inventaris/index.php?tab=aktif<?= $keyword !== '' ? '&q=' . urlencode($keyword) : '' ?>" class="inventory-tab <?= $tab==='aktif'?'active':'' ?>">Stok Aktif</a>
        <a href="/smart-ed/inventaris/index.php?tab=karantina<?= $keyword !== '' ? '&q=' . urlencode($keyword) : '' ?>" class="inventory-tab <?= $tab==='karantina'?'active':'' ?>">Gudang Karantina <span style="background:#fee2e2;color:#b91c1c;border-radius:999px;padding:2px 7px;margin-left:5px;"><?= $karantina_count ?></span></a>
    </div>

    <div class="content-card">
        <div class="table-header">
            <div>Nama Obat & Sediaan</div><div>No Batch</div><div>Stok</div><div>Lokasi</div><div>Supplier</div><div style="text-align: right;">Aksi</div>
        </div>

        <?php while($row = mysqli_fetch_array($query)) { ?>
        <div class="table-row">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">
                    <?= $row['nama_obat']; ?><br>
                    <small style="font-weight:400; color:var(--text-muted)"><?= $row['kategori']; ?></small>
                </div>
            </div>
            <div style="font-family: monospace; color: var(--text-muted);"><?= $row['no_batch']; ?></div>
            <div style="font-weight: 700;"><?= (int)$row['sisa_stok']; ?> <span style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($row['satuan'] ?? 'Unit') ?></span></div>
            
            <div><span class="pill"><?= !empty($row['lokasi']) ? $row['lokasi'] : 'Belum Atur'; ?></span></div>
            
            <div style="color: var(--text-muted); font-size: 13px;"><?= !empty($row['supplier']) ? $row['supplier'] : '-'; ?></div>

            <div style="text-align: right;">
                <?php if ($tab === 'karantina'): ?>
                    <a href="/smart-ed/inventaris/pulihkan.php?id=<?= $row['id'] ?>" onclick="return confirm('Pulihkan obat ini ke stok aktif/rak penyimpanan?')" style="display:inline-block;padding:7px 9px;border-radius:7px;background:#dcfce7;color:#166534;text-decoration:none;font-size:11px;font-weight:700;margin-right:6px;">Pulihkan ke Rak</a>
                    <a href="/smart-ed/inventaris/hapus.php?id=<?= $row['id'] ?>" onclick="return confirm('Hapus permanen data ini?')" style="color:#ef4444;font-weight:700;font-size:11px;text-decoration:none;">Hapus Permanen</a>
                <?php else: ?>
                    <a href="/smart-ed/inventaris/edit.php?id=<?= $row['id'] ?>" style="color:#64745b;margin-right:10px;">✎</a>
                    <a href="/smart-ed/inventaris/hapus.php?id=<?= $row['id'] ?>" onclick="return confirm('Hapus data?')" style="color:#ef4444;">🗑</a>
                <?php endif; ?>
            </div>
        </div>
        <?php } ?>
    </div>

    <div class="modern-pagination" style="margin-top: 20px;">
        <?php if($total_pages > 1):
            $base_query = 'tab=' . urlencode($tab) . ($keyword !== '' ? '&q=' . urlencode($keyword) : '');
        ?>
            <?php if($page > 1): ?><a href="?<?= $base_query ?>&page=<?= $page-1 ?>">‹ Prev</a><?php endif; ?>
            <?php for($i=1;$i<=$total_pages;$i++): ?>
                <a href="?<?= $base_query ?>&page=<?= $i ?>" class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if($page < $total_pages): ?><a href="?<?= $base_query ?>&page=<?= $page+1 ?>">Next ›</a><?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>