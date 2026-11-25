CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    role VARCHAR(30) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS inventaris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_obat VARCHAR(100),
    no_batch VARCHAR(50),
    sisa_stok INT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'Tersedia',
    expired DATE,
    kategori VARCHAR(50),
    lokasi VARCHAR(100) DEFAULT '-',
    supplier VARCHAR(100) DEFAULT '-',
    lokasi_penyimpanan VARCHAR(100) DEFAULT '-',
    tanggal_input DATETIME DEFAULT CURRENT_TIMESTAMP,
    satuan VARCHAR(30) DEFAULT 'Tablet'
);

CREATE TABLE IF NOT EXISTS setting_profile (
    id INT PRIMARY KEY,
    nama_lengkap VARCHAR(100) DEFAULT '',
    jabatan VARCHAR(100) DEFAULT '',
    email VARCHAR(150) DEFAULT '',
    sipa VARCHAR(100) DEFAULT '',
    nama_apotek VARCHAR(150) DEFAULT 'Apotek Bintang Surya',
    telp_apotek VARCHAR(50) DEFAULT '',
    alamat_apotek TEXT,
    password_akun VARCHAR(255) DEFAULT '',
    stok_min INT DEFAULT 20,
    ed_alert INT DEFAULT 3
);

INSERT INTO users (nama_lengkap, role, username, email, password)
VALUES ('Administrator Utama', 'Admin', 'admin', 'admin@smart-ed.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT IGNORE INTO setting_profile (id) VALUES (1);