-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2025 at 11:52 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpustakaan`
--

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id` int(11) NOT NULL,
  `kode_buku` varchar(20) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `pengarang` varchar(100) NOT NULL,
  `penerbit` varchar(100) DEFAULT NULL,
  `tahun_terbit` year(4) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `jumlah_total` int(11) DEFAULT 1,
  `jumlah_tersedia` int(11) DEFAULT 1,
  `cover` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `lokasi_rak` varchar(50) DEFAULT NULL,
  `status` enum('tersedia','tidak_tersedia') DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id`, `kode_buku`, `judul`, `pengarang`, `penerbit`, `tahun_terbit`, `isbn`, `kategori_id`, `jumlah_total`, `jumlah_tersedia`, `cover`, `deskripsi`, `lokasi_rak`, `status`, `created_at`, `updated_at`) VALUES
(4, 'BK831HQ9', 'Kisah Dashyat dari Negeri Padang Pasir', 'samsul m.kom', 'sumsal M.Kom', '2025', '', 1, 20, 20, 'covers/685854382ec94.jpg', 'kisah dashyaat amat dashyatt', 'A1-02', 'tersedia', '2025-06-22 19:06:32', '2025-06-23 20:51:55'),
(5, 'BKKJUZEM', 'Akselarasi-TeknologI-Informasi', 'Muhammad Athoillah, S.Si., M.Si', 'PT Refika Aditama', '2025', '132-341-dsc-gfs', 3, 30, 30, 'covers/68597f80c6e8c.jpg', 'Buku ini hadir sebagai panduan praktis dan komprehensif dalam memahami teknologi informasi modern, lengkap dengan wawasan etis dan hukum yang sangat relevan untuk menghadapi tantangan era digital saat ini. Sangat cocok bagi siapa saja yang ingin menguasai TI secara bertanggung jawab.', 'A5-02', 'tersedia', '2025-06-23 16:23:28', '2025-06-23 18:46:01'),
(6, 'DCKAKAC', 'Ranselo Dan Galang', 'Kak Thifa', 'Laksana', '2025', '089-979-ksc-ssa', 1, 39, 39, 'covers/6859a0b8b7e0a.jpg', '“Pus pus pus…,” panggil Galang, “hmmm, rupanya kakinya terluka.” Galang segera menggendong si anak kucing. “Bawa pulang saja, Galang,” kata Ranselo. Galang menggendong si anak kucing dan membawanya pulang. … Ranselo adalah si ransel biru yang bisa bicara. Dia selalu menemani Galang ke mana pun Galang pergi.', 'A1-03', 'tersedia', '2025-06-23 18:45:12', '2025-06-23 18:45:12'),
(7, 'CDCAKCXX', 'Akibat perbuatan Edo dan teman-teman', 'Siti Munfarijah, M.Pd.', 'Pustaka Anak, Yogyakarta (Cetakan Pertama, 2018)', '2024', '299-353-dcf-zzz', 1, 34, 34, 'covers/6859a19d70e54.jpg', 'Buku ini mengangkat tema tentang tindakan Edo dan teman-temannya—baik disengaja maupun tidak—yang menimbulkan akibat kurang baik bagi diri dan lingkungan sekitar. Melalui kisah sederhana dan mudah dipahami, anak-anak diajak mengenali tanggung jawab atas perbuatan mereka, belajar introspeksi, serta menyadarkan pentingnya sikap bijaksana dalam pergaulan', 'A1-02', 'tersedia', '2025-06-23 18:49:01', '2025-06-23 18:49:01'),
(8, 'SLFOFVJD', 'Kerajaan Majapahit', 'Slamet Muljana', 'LKIS, Yogyakarta (2005 – Cet. 4)', '2005', '122-453-sda-grw', 4, 40, 40, 'covers/6859a256cb18c.jpg', 'Buku ini mengupas perjalanan berdirinya hingga masa kejayaan Kerajaan Majapahit. Dimulai dari pendirian oleh Raden Wijaya, mengulas puncak kekuasaan pada masa Hayam Wuruk dan Gajah Mada, hingga periode kemunduran akibat konflik politik internal. Disertai analisis mendalam, ilustrasi, dan latar belakang sejarah yang kaya—menjadikannya karya penting untuk memahami sejarah Nusantara', 'K1-01', 'tersedia', '2025-06-23 18:52:06', '2025-06-23 18:55:53'),
(9, 'BK7YI45Y', 'Trilogi pendidikan seksual untuk anak sekolah dasar', 'Dr. Falasifah Ani Yuniarti, S.Kep., Ns., MAN.', 'Penerbit Deepublish, Yogyakarta', '2003', '141-563-ggt-hof', 5, 43, 43, 'covers/6859a2f672f43.png', 'Buku ini menghadirkan model edukasi seksual bagi anak usia Sekolah Dasar—disebut TRIPS (Trilogi Pendidikan Seksual untuk Persiapan Pubertas)—yang dikembangkan berdasarkan kajian keperawatan dan nilai-nilai Islam. Materi mencakup aspek biologis, psikologis, sosial, dan spiritual yang dialami anak memasuki masa pubertas, lengkap dengan panduan untuk orang tua, guru, dan tenaga kesehatan.', 'A1-05', 'tersedia', '2025-06-23 18:54:46', '2025-06-23 18:54:46'),
(10, 'BKG8Q5S4', 'Teori Teori pendidikan', 'Idham Azwar', 'Edupedia Publisher', '2004', '131-456-mvn-ric', 5, 56, 56, 'covers/6859a3ccd35f8.png', 'Menyajikan ulasan lengkap 11 aliran teori pendidikan—perennialisme, essentialisme, progresivisme, rekonstruksionisme, behaviorisme, kognitivisme, konstruktivisme, humanisme, pendidikan kritis, hingga postmodernisme. Setiap bab mencakup prinsip, praktik, kritikan, serta tantangannya', 'A5-03', 'tersedia', '2025-06-23 18:58:20', '2025-06-23 18:58:20'),
(11, 'BKGGKN2O', 'Teknologi pembelajaran', 'Deni Darmawan', 'Bandung, Remaja Rosda Karya', '2017', '143-344-vbs-dfs', 5, 28, 28, 'covers/6859b73ceaf23.png', '221 halaman, termasuk ilustrasi. Buku ini membahas dasar‑dasar teknologi pendidikan dan kajian teoritisnya dalam konteks pembelajaran', 'A1-05', 'tersedia', '2025-06-23 20:21:16', '2025-06-23 20:21:16'),
(12, 'BKMG88P9', 'Sosiologi Pendidikan', 'Dr. Sutomo, M.Sos.', 'Litnus', '2024', '132-423-fgr-yyt', 5, 77, 77, 'covers/6859b7c6d9fad.jpg', 'Memberikan pengantar tentang hubungan antara pendidikan, masyarakat, budaya, dan struktur sosial. Mengupas konsep dasar, teori, stratifikasi sosial, metode penelitian, hingga isu-isu kontemporer dalam sosiologi pendidikan', 'A5-01', 'tersedia', '2025-06-23 20:23:34', '2025-06-23 20:23:34'),
(13, 'BKCHNA2K', 'Siapakah Bangsa Palestina', 'Yulian', 'DIVA Press', '2024', '878-876-jdk-ccv', 4, 34, 33, 'covers/6859b8572dc0c.jpg', 'Buku ini membahas sejarah Palestina sejak lebih dari 4.000 tahun yang lalu. Dengan Yerusalem (al‑Quds) sebagai pusat, Palestina dijelaskan sebagai tanah suci bagi agama Yahudi, Kristen, dan Islam. Penulis menjabarkan berbagai kerajaan dan bangsa yang pernah menduduki kawasan tersebut dan menyoroti peran strategis Palestina dalam geopolitik dan peradaban dunia.\r\nDalam perjalanan sejarahnya, Palestina sering kali menjadi rebutan oleh berbagai bangsa—sering kali karena kekayaan agama, budaya, dan aspeknya sebagai “keistimewaan” dunia', 'A5-03', 'tersedia', '2025-06-23 20:25:59', '2025-06-23 21:03:55'),
(14, 'BKQIHXJQ', 'Rona Bahasa dan Sastra Indonesia', 'Sugihastuti', 'Pustaka Pelajar (Yogyakarta),', '2009', '122-873-jjk=mvm', 5, 46, 46, 'covers/6859b91abb62e.jpg', 'Buku ini merupakan karya non-fiksi yang mengupas berbagai aspek dari bahasa dan sastra Indonesia. Disusun oleh Sugihastuti, edisi kedua (2009) memuat tujuh bab utama yang terbagi menjadi dua bagian:\r\n\r\nBagian 1 (4 bab): kajian mendalam terhadap karya atau pengarang tertentu, seperti Toeti Heraty hingga Ayu Utami.\r\n\r\nBagian 2 (5 bab): membahas aspek praktis terkait bahasa Indonesia, mulai dari penggunaan stiker plesetan hingga bagaimana menjadi editor yang baik', 'A5-03', 'tersedia', '2025-06-23 20:29:14', '2025-06-23 20:29:14'),
(15, 'BKBPTO52', 'Kumpulan Kisa Teladan Tokoh-Tokoh Indonesia', 'Hari Santoso', 'Deepublish', '2019', '832-393-zxz-dad', 2, 30, 30, 'covers/6859b9c05c874.jpg', 'Buku ini berisi kumpulan kisah inspiratif tokoh‑tokoh penting dalam sejarah Indonesia, disajikan untuk dibaca sebagai cerita pengantar tidur anak-anak. Dengan total 168 tokoh yang pernah ditetapkan sebagai pahlawan nasional, buku ini bertujuan menanamkan nilai-nilai moral dan teladan dari masa ke masa', 'B1-01', 'tersedia', '2025-06-23 20:32:00', '2025-06-23 20:32:00'),
(16, 'BKQHMMHT', 'Pendidikan anti KORUPSI', 'Dr. Meiliyah Ariani, S.E., M.Ak', 'PT. Sonpedia Publishing Indonesia', '2024', '123-564-gpv-eds', 5, 100, 100, 'covers/6859c49752bdb.png', 'Buku ini membahas secara komprehensif tentang korupsi dan strategi pencegahannya sejak usia dini. Disusun oleh tim ahli dari berbagai disiplin, buku ini terbagi dalam beberapa bagian utama:\r\n\r\nDasar Teori Korupsi – Penjelasan bentuk-bentuk korupsi dan dampaknya terhadap masyarakat.\r\n\r\nNilai &amp; Etika – Pembentukan nilai-nilai integritas melalui pendidikan.\r\n\r\nStrategi Pengajaran – Panduan praktis bagi pendidik untuk mengintegrasikan pembelajaran anti korupsi dalam kurikulum.\r\n\r\nStudi Internasional – Pembelajaran dari pengalaman negara-negara sukses dalam pendidikan anti korupsi.', 'A5-01', 'tersedia', '2025-06-23 21:18:15', '2025-06-23 21:18:15'),
(17, 'BKZYPNYC', 'Augmeted dan Virtual Reality', 'Lis Andriani &amp; Richardus Eko Indrajit', 'Teknosain', '2024', '233-234-11-vxz-vsa', 3, 45, 45, 'covers/6859c5439d8a9.jpg', 'Membahas evolusi &amp; konsep dasar AR dan VR, termasuk transisi menuju Metaverse. Buku ini mengupas sejarah perkembangan, adopsi di sektor seperti pariwisata, medis, dan pendidikan, serta hambatan seperti cost‑barrier dan relevansi bisnis. Meski begitu, potensi penggunaan luas diprediksi akan meningkat dengan inovasi aplikasi yang makin berkembang', 'A5-02', 'tersedia', '2025-06-23 21:21:07', '2025-06-23 21:21:07'),
(18, 'BKEEW9U6', 'Para filsuf yang mempengaruhi dunia pendidikan kita', 'A. Palmer', 'IRCiSoD', '2024', '543-343-bbc-vxz', 2, 30, 30, 'covers/6859c5e972af2.jpg', 'Mengupas pemikiran tokoh-tokoh seperti Jean Piaget, A.S. Neil, Susan Isaacs, Wittgenstein, Heidegger, Vygotsky, Carl Rogers, dan lainnya—membahas bagaimana gagasan mereka membentuk pemahaman pendidikan modern', 'A5-01', 'tersedia', '2025-06-23 21:23:53', '2025-06-23 21:23:53');

-- --------------------------------------------------------

--
-- Table structure for table `denda`
--

CREATE TABLE `denda` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `jumlah_hari` int(11) NOT NULL,
  `tarif_per_hari` decimal(10,2) DEFAULT 1000.00,
  `total_denda` decimal(10,2) NOT NULL,
  `status` enum('belum_bayar','lunas') DEFAULT 'belum_bayar',
  `tanggal_bayar` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `denda`
--

INSERT INTO `denda` (`id`, `transaksi_id`, `jumlah_hari`, `tarif_per_hari`, `total_denda`, `status`, `tanggal_bayar`, `created_at`) VALUES
(1, 2, 0, 1000.00, 1000.00, 'belum_bayar', NULL, '2025-06-23 20:51:55');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `deskripsi`, `created_at`) VALUES
(1, 'Fiksi', 'Buku-buku fiksi dan novel', '2025-06-22 07:52:18'),
(2, 'Non-Fiksi', 'Buku-buku ilmu pengetahuan dan referensi', '2025-06-22 07:52:18'),
(3, 'Teknologi', 'Buku-buku tentang teknologi informasi', '2025-06-22 07:52:18'),
(4, 'Sejarah', 'Buku-buku sejarah dan biografi', '2025-06-22 07:52:18'),
(5, 'Pendidikan', 'Buku-buku pendidikan dan pembelajaran', '2025-06-22 07:52:18');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `kode_transaksi` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali_rencana` date NOT NULL,
  `tanggal_kembali_aktual` date DEFAULT NULL,
  `denda` decimal(10,2) DEFAULT 0.00,
  `status` enum('dipinjam','dikembalikan','terlambat') DEFAULT 'dipinjam',
  `catatan` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `kode_transaksi`, `user_id`, `buku_id`, `tanggal_pinjam`, `tanggal_kembali_rencana`, `tanggal_kembali_aktual`, `denda`, `status`, `catatan`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'TRXS0D2BY7Z', 2, 4, '2025-06-22', '2025-06-23', '2025-06-23', 1000.00, 'dikembalikan', 'gabut | Catatan pengembalian: sorry ges | Dikembalikan oleh admin pada 2025-06-24 03:51:55', 2, '2025-06-22 20:26:47', '2025-06-23 20:51:55'),
(3, 'TRXPZD8UNBW', 3, 13, '2025-06-23', '2025-06-25', NULL, 0.00, 'dipinjam', 'seru nih', 3, '2025-06-23 21:03:55', '2025-06-23 21:03:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `nama_lengkap` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `nama_lengkap`, `alamat`, `telepon`, `foto`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$4i7G/ky95b00V6vbu7brOOFFN0LYec9POZ.shWe6zVV5xnuApw4n2', 'admin@perpustakaan.com', 'admin', 'Admin Perpustakaan', 'Jakarta Timur, Kramat Jati, Kampung Tengah', '087818894504', 'profiles/6857f326c6acb.jpg', 'aktif', '2025-06-22 07:52:18', '2025-06-23 21:06:02'),
(2, 'rickysilaban', '$2y$10$g1OvTf59irozXM64/IoFq.Cfqxj242dgg7kUHMPsoARdfdcs7WDZm', 'rickysilaban384@gmail.com', 'user', 'ricky steven silaban', 'Jakarta Timur, Kramat Jati, Kampung Tengah', '087818894504', 'profiles/6858653117f54.jpg', 'aktif', '2025-06-22 07:59:17', '2025-06-23 20:52:28'),
(3, 'Steven', '$2y$10$0kSw1Fi65lLqNW4uuJzJo.dOXVCUe5PNm5JFV1Dq7ljfDPiOb1M2y', 'steven112@gmail.com', 'user', 'Stevenky', 'medan', '087812423433', 'profiles/6859bcd94c72d.jpg', 'aktif', '2025-06-23 20:45:13', '2025-06-23 21:28:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_buku` (`kode_buku`),
  ADD KEY `kategori_id` (`kategori_id`),
  ADD KEY `idx_buku_kode` (`kode_buku`),
  ADD KEY `idx_buku_judul` (`judul`);

--
-- Indexes for table `denda`
--
ALTER TABLE `denda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `buku_id` (`buku_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_transaksi_kode` (`kode_transaksi`),
  ADD KEY `idx_transaksi_user` (`user_id`),
  ADD KEY `idx_transaksi_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `idx_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `denda`
--
ALTER TABLE `denda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `buku_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `denda`
--
ALTER TABLE `denda`
  ADD CONSTRAINT `denda_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
