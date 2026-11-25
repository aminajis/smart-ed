<?php
include 'config/database.php';

$tgl_mulai   = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';

if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $where_clause = "WHERE expired BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
} else {
    $where_clause = "";
}

# mengambil data dari inventaris sesuai filter tanggal atau semua data jika tidak ada filter
$query_data = "SELECT * FROM inventaris $where_clause ORDER BY nama_obat ASC";
$sql_data = mysqli_query($conn, $query_data);
$total_rows = mysqli_num_rows($sql_data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inventaris Obat</title>
    <style>
        body { font-family: 'Arial', sans-serif; color: #000; line-height: 1.4; padding: 0; margin: 0; background: #f8fafc; }
        .download-toolbar { background: #eff6ff; border: 1px solid #bfdbfe; padding: 15px; border-radius: 8px; margin: 20px; display: flex; justify-content: space-between; align-items: center; font-size: 14px; }
        .btn-download { background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-download:hover { background: #059669; }
        .print-area { background: white; padding: 50px; border-radius: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); max-width: 210mm; margin: 0 auto; }
        .print-header { text-align: center; margin-bottom: 20px; border-bottom: 3px double #000; padding-bottom: 15px; position: relative; }
        .print-header h2 { margin: 0; font-size: 22px; text-transform: uppercase; letter-spacing: 0.5px; }
        .print-header p { margin: 4px 0 0; font-size: 12px; color: #334155; }
        .print-meta-time { position: absolute; right: 0; bottom: 5px; font-size: 11px; font-style: italic; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 8px 10px; text-align: left; font-size: 12px; }
        thead { display: table-header-group; } 
        th { background-color: #f1f5f9 !important; text-transform: uppercase; font-size: 11px; font-weight: bold; }
        tr { page-break-inside: avoid; } 
        .total-row-box { font-weight: bold; background: #f8fafc; text-align: right; }

        # print 
        @media print {
            @page { 
                margin: 0; 
            }
            body { 
                background: white; 
                padding: 1.5cm; 
            }
            .download-toolbar { display: none !important; }
            .print-area { padding: 0; border-radius: 0; box-shadow: none; max-width: 100%; }
            th { background-color: #e2e8f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .total-row-box { background-color: #e2e8f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="download-toolbar">
        <div>
            <h4 style="margin: 0 0 5px; color: #1e3a8a;">📄 Dokumen Laporan Siap (Total: <?= $total_rows ?> Item Obat)</h4>
            <p style="margin: 0; font-size: 13px; color: #1e40af;">Sistem otomatis membagi halaman secara proporsional dan mengulang judul kolom di setiap lembar kertas jika data berjumlah ratusan.</p>
        </div>
        <button onclick="window.print()" class="btn-download">💾 Simpan / Download PDF</button>
    </div>

    <div class="print-area">
        <div class="print-header">
            <h2>Apotek Bintang Surya</h2>
            <p>Jl. Raya Utama No. 123, Kota Banjarsari | Telp: (021) 8888-xxxx</p>
            <h3 style="margin-top:15px; margin-bottom:0; font-size: 14px;">LAPORAN DATA INVENTARIS OBAT MUTASI</h3>
            <?php if(!empty($tgl_mulai)): ?>
                <p style="margin: 3px 0 0; font-size:12px;">Periode Kadaluwarsa: <?= date('d M Y', strtotime($tgl_mulai)) ?> s/d <?= date('d M Y', strtotime($tgl_selesai)) ?></p>
            <?php else: ?>
                <p style="margin: 3px 0 0; font-size:12px;">Semua Master Data Transaksi Aktif Berjalan</p>
            <?php endif; ?>
            
            <div class="print-meta-time">Tgl Cetak: <?= date('d/m/Y H:i') ?> WIB</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th>Nama Obat</th>
                    <th>No. Batch</th>
                    <th>Sisa Stok</th>
                    <th>Tanggal Kadaluwarsa</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if($total_rows == 0) {
                    echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>Tidak ada data obat ditemukan.</td></tr>";
                }
                $no = 1;
                while($r = mysqli_fetch_array($sql_data)) { 
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= $r['nama_obat'] ?></strong></td>
                    <td><?= $r['no_batch'] ?></td>
                    <td><?= $r['sisa_stok'] ?> Unit</td>
                    <td><?= date('d M Y', strtotime($r['expired'])) ?></td>
                </tr>
                <?php } ?>
                
                <tr class="total-row-box">
                    <td colspan="4" style="text-align: right; font-weight: bold; text-transform: uppercase;">Total Inventaris:</td>
                    <td style="font-weight: bold;"><?= $total_rows ?> Item Terdata</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
</html>