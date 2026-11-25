<?php
// Ambil koneksi database & header langsung dari root folder
require_once 'config/database.php';
include 'includes/header.php';

$setting = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT stok_min, ed_alert FROM setting_profile WHERE id=1')) ?: [];
$stok_min = max(0, (int)($setting['stok_min'] ?? 20));
$ed_alert = max(1, (int)($setting['ed_alert'] ?? 3));

mysqli_query($conn, "UPDATE inventaris SET status='Tersedia' WHERE status IS NULL OR status='' ");

$tgl_awal = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tgl_awal'] ?? '') ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tgl_akhir'] ?? '') ? $_GET['tgl_akhir'] : date('Y-m-d');
if ($tgl_awal > $tgl_akhir) { $tmp = $tgl_awal; $tgl_awal = $tgl_akhir; $tgl_akhir = $tmp; }

$today = date('Y-m-d');
$near_ed = date('Y-m-d', strtotime("+$ed_alert months"));
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;

$aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS batch_count, COALESCE(SUM(sisa_stok),0) AS stok FROM inventaris WHERE status='Tersedia'"));
$kadaluarsa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS batch_count FROM inventaris WHERE (expired IS NOT NULL AND expired <= '$today') OR status='Karantina'"));
$masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(sisa_stok),0) AS stok FROM inventaris WHERE status='Tersedia' AND tanggal_input BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'"));
$keluar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(sisa_stok),0) AS stok FROM inventaris WHERE status='Karantina'"));

$total_category_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT COALESCE(NULLIF(kategori,''),'Tanpa Kategori')) AS total FROM inventaris"));
$total_pages = max(1, (int)ceil($total_category_row['total'] / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$sql = "SELECT COALESCE(NULLIF(kategori,''),'Tanpa Kategori') AS kategori, COUNT(*) AS batch_count,
 COALESCE(SUM(sisa_stok),0) AS stok_akhir,
 COALESCE(SUM(CASE WHEN status='Karantina' THEN sisa_stok ELSE 0 END),0) AS keluar,
 COALESCE(SUM(CASE WHEN status='Tersedia' AND tanggal_input BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59' THEN sisa_stok ELSE 0 END),0) AS masuk,
 SUM(CASE WHEN status='Tersedia' AND expired > '$near_ed' THEN 1 ELSE 0 END) AS aman,
 SUM(CASE WHEN status='Tersedia' AND expired > '$today' AND expired <= '$near_ed' THEN 1 ELSE 0 END) AS mendekati
 FROM inventaris GROUP BY COALESCE(NULLIF(kategori,''),'Tanpa Kategori') ORDER BY kategori ASC LIMIT $per_page OFFSET $offset";
$rows = mysqli_query($conn, $sql);

$footer = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS batch_count, COALESCE(SUM(sisa_stok),0) AS stok_akhir,
 COALESCE(SUM(CASE WHEN status='Karantina' THEN sisa_stok ELSE 0 END),0) AS keluar,
 COALESCE(SUM(CASE WHEN status='Tersedia' AND tanggal_input BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59' THEN sisa_stok ELSE 0 END),0) AS masuk FROM inventaris"));
$total_item_aktif = (int)$aktif['batch_count'];
$stok_masuk = (int)$masuk['stok'];
$stok_keluar = (int)$keluar['stok'];
?>

<style>
.report-page{padding:35px}.report-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:22px}.report-header h1{margin:0;font-size:28px;color:#1e293b}.report-header p{margin:6px 0 0;color:#64748b}.control-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px;margin-bottom:22px;display:flex;justify-content:space-between;align-items:end}.control-card form{display:flex;gap:12px;align-items:end}.control-card label{display:block;font-size:11px;font-weight:800;color:#64748b;margin-bottom:7px}.control-card input{height:40px;padding:0 12px;border:1px solid #cbd5e1;border-radius:8px}.filter-btn{height:42px;border:0;border-radius:8px;background:#2B5B7C;color:#fff;font-weight:700;padding:0 18px}.print-btn{height:42px;border:0;border-radius:8px;background:#2B6CB0;color:#fff;font-weight:700;padding:0 18px}.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:24px}.stat-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 20px;border-left:5px solid #94a3b8}.stat-card.red{border-left-color:#ef4444}.stat-card.blue{border-left-color:#93c5fd}.stat-card h2{font-size:29px;margin:8px 0 4px;color:#1e293b}.stat-card.red h2{color:#ef4444}.stat-label{font-size:11px;font-weight:800;color:#64748b}.stat-sub{font-size:12px;color:#64748b}.table-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden}.report-table{width:100%;border-collapse:collapse}.report-table th{background:#f1f5f9;padding:14px 16px;font-size:11px;text-align:left;color:#64748b}.report-table td{padding:16px;border-bottom:1px solid #edf2f7;font-size:14px}.total-row{background:#f8fafc;font-weight:800}.stock-pill{display:inline-block;padding:5px 10px;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-weight:700}.stock-pill.danger{background:#fee2e2;color:#b91c1c}.pagination{display:flex;justify-content:flex-end;gap:6px;padding:14px}.pagination a{padding:7px 10px;border:1px solid #cbd5e1;border-radius:7px;text-decoration:none;color:#475569}.pagination a.active{background:#2B5B7C;color:#fff}.print-head,.signature-block{display:none}@media print{.sidebar,.top-bar,.control-card,.pagination,footer,button{display:none!important}.main-content{margin-left:0!important}.report-page{padding:20px}.print-head{display:block;text-align:center;border-bottom:2px solid #1e293b;margin-bottom:20px;padding-bottom:12px}.signature-block{display:flex;justify-content:flex-end;margin-top:55px}.table-card{break-inside:avoid}}
</style>

<div class="report-page">
<div class="print-head"><h2>APOTEK BINTANG SURYA</h2><h3>LAPORAN REKAPITULASI INVENTARIS &amp; MONITORING ED</h3><small>Tanggal cetak: <?= date('d/m/Y H:i') ?></small></div>

<div class="report-header"><div><h1>Laporan Rekapitulasi</h1><p>Data ringkasan inventaris dan stok farmasi periode berjalan.</p></div></div>

<div class="control-card">
<form method="GET" action="/smart-ed/laporan.php">
<div><label>TANGGAL MULAI</label><input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>"></div>
<div><label>TANGGAL AKHIR</label><input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>"></div>
<button class="filter-btn" type="submit">Filter Periode</button>
</form>
<button class="print-btn" type="button" onclick="window.print()">Ekspor PDF</button>
</div>

<div class="stats-grid">
<div class="stat-card"><div class="stat-label">TOTAL ITEM AKTIF</div><h2><?= number_format($total_item_aktif) ?></h2><div class="stat-sub">Jenis/batch tersedia</div></div>
<div class="stat-card red"><div class="stat-label">ITEM KADALUWARSA</div><h2><?= number_format((int)$kadaluarsa['batch_count']) ?></h2><div class="stat-sub">Perlu penanganan segera</div></div>
<div class="stat-card"><div class="stat-label">STOK MASUK (PERIODE)</div><h2><?= number_format($stok_masuk) ?></h2><div class="stat-sub">Terverifikasi sistem</div></div>
<div class="stat-card blue"><div class="stat-label">STOK KELuar / KARANTINA</div><h2><?= number_format($stok_keluar) ?></h2><div class="stat-sub">Unit dipindahkan karantina</div></div>
</div>

<div class="table-card">
<table class="report-table"><thead><tr><th>KATEGORI OBAT</th><th>STOK AWAL</th><th>MASUK</th><th>KELUAR / KARANTINA</th><th>STOK AKHIR</th><th>STATUS RISIKO</th></tr></thead><tbody>
<?php $stok_awal_footer=0; $total_masuk_footer=0; $total_keluar_footer=0; $total_akhir_footer=0; while($row=mysqli_fetch_assoc($rows)){ $stok_awal=(int)$row['stok_akhir']-(int)$row['masuk']+(int)$row['keluar']; $stok_awal_footer+=(int)$stok_awal; $total_masuk_footer+=(int)$row['masuk']; $total_keluar_footer+=(int)$row['keluar']; $total_akhir_footer+=(int)$row['stok_akhir']; ?>
<tr><td><strong><?= htmlspecialchars($row['kategori']) ?></strong><br><small style="color:#64748b"><?= (int)$row['batch_count'] ?> batch</small></td><td><?= number_format($stok_awal) ?> Unit</td><td style="color:#2563eb;font-weight:700"><?= number_format((int)$row['masuk']) ?></td><td style="color:#dc2626;font-weight:700"><?= number_format((int)$row['keluar']) ?></td><td><span class="stock-pill <?= (int)$row['stok_akhir'] < $stok_min ? 'danger' : '' ?>"><?= number_format((int)$row['stok_akhir']) ?> Unit</span></td><td><span style="color:#166534;font-weight:700"><?= (int)$row['aman'] ?> Aman</span><br><span style="color:#b45309;font-weight:700"><?= (int)$row['mendekati'] ?> Mendekati ED</span></td></tr>
<?php } if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM inventaris LIMIT 1"))===0){ ?><tr><td colspan="6" style="text-align:center;color:#94a3b8">Belum ada data inventaris</td></tr><?php } ?>
<tr class="total-row"><td>TOTAL KESELURUHAN<br><small><?= (int)$footer['batch_count'] ?> batch</small></td><td><?= number_format($stok_awal_footer) ?> Unit</td><td style="color:#2563eb"><?= number_format($total_masuk_footer) ?></td><td style="color:#dc2626"><?= number_format($total_keluar_footer) ?></td><td><span class="stock-pill <?= (int)$footer['stok_akhir'] < $stok_min ? 'danger' : '' ?>"><?= number_format((int)$footer['stok_akhir']) ?> Unit</span></td><td>-</td></tr>
</tbody></table>
<div class="pagination"><span style="margin-right:auto;color:#64748b;font-size:12px;padding:7px">Halaman <?= $page ?> dari <?= $total_pages ?></span><?php for($i=1;$i<=$total_pages;$i++){ ?><a class="<?= $i==$page?'active':'' ?>" href="/smart-ed/laporan.php?tgl_awal=<?= urlencode($tgl_awal) ?>&tgl_akhir=<?= urlencode($tgl_akhir) ?>&page=<?= $i ?>"><?= $i ?></a><?php } ?></div>
</div>
<div class="signature-block"><div style="width:260px;text-align:center"><strong>Apoteker Penanggung Jawab</strong><br><br><br><span>________________________</span></div></div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>