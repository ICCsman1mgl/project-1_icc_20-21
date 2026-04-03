-- Database Schema untuk Sistem Perpustakaan
CREATE DATABASE perpustakaan;
USE perpustakaan;


-- Tabel pengguna (admin dan user)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    nama_lengkap VARCHAR(100) NOT NULL,
    alamat TEXT,
    telepon VARCHAR(20),
    foto VARCHAR(255),
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel kategori buku
CREATE TABLE kategori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel buku
CREATE TABLE buku (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_buku VARCHAR(20) UNIQUE NOT NULL,
    judul VARCHAR(200) NOT NULL,
    pengarang VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100),
    tahun_terbit YEAR,
    isbn VARCHAR(20),
    kategori_id INT,
    jumlah_total INT DEFAULT 1,
    jumlah_tersedia INT DEFAULT 1,
    cover VARCHAR(255),
    deskripsi TEXT,
    lokasi_rak VARCHAR(50),
    status ENUM('tersedia', 'tidak_tersedia') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL
);

-- Tabel transaksi peminjaman
CREATE TABLE transaksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_transaksi VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    buku_id INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali_rencana DATE NOT NULL,
    tanggal_kembali_aktual DATE NULL,
    denda DECIMAL(10,2) DEFAULT 0,
    status ENUM('dipinjam', 'dikembalikan', 'terlambat') DEFAULT 'dipinjam',
    catatan TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel denda
CREATE TABLE denda (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaksi_id INT NOT NULL,
    jumlah_hari INT NOT NULL,
    tarif_per_hari DECIMAL(10,2) DEFAULT 1000,
    total_denda DECIMAL(10,2) NOT NULL,
    status ENUM('belum_bayar', 'lunas') DEFAULT 'belum_bayar',
    tanggal_bayar DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (username, password, email, role, nama_lengkap, alamat, telepon) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@perpustakaan.com', 'admin', 'Administrator', 'Jl. Admin No. 1', '081234567890');

-- Insert default categories
INSERT INTO kategori (nama_kategori, deskripsi) VALUES 
('Fiksi', 'Buku-buku fiksi dan novel'),
('Non-Fiksi', 'Buku-buku ilmu pengetahuan dan referensi'),
('Teknologi', 'Buku-buku tentang teknologi informasi'),
('Sejarah', 'Buku-buku sejarah dan biografi'),
('Pendidikan', 'Buku-buku pendidikan dan pembelajaran');

-- Create indexes for better performance
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_buku_kode ON buku(kode_buku);
CREATE INDEX idx_buku_judul ON buku(judul);
CREATE INDEX idx_transaksi_kode ON transaksi(kode_transaksi);
CREATE INDEX idx_transaksi_user ON transaksi(user_id);
CREATE INDEX idx_transaksi_status ON transaksi(status);
