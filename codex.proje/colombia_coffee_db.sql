-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 04 May 2026, 00:38:03
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `colombia_coffee_db`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `anasayfa_subeler`
--

CREATE TABLE `anasayfa_subeler` (
  `id` int(11) NOT NULL,
  `il` varchar(50) DEFAULT '',
  `ilce` varchar(100) DEFAULT '',
  `resim` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `anasayfa_subeler`
--

INSERT INTO `anasayfa_subeler` (`id`, `il`, `ilce`, `resim`) VALUES
(1, 'Sakarya', 'Karasu', '1777841528_vitrin_colombiacoffekarasşube.webp'),
(2, 'İstanbul', 'Ümraniye', '1777841722_vitrin_ümraniyeşubecolombia.webp'),
(3, 'Yakında', 'Hizmetinizdeyiz', ''),
(4, 'Yakında', 'Hizmetinizdeyiz', ''),
(5, 'Yakında', 'Hizmetinizdeyiz', ''),
(6, 'Yakında', 'Hizmetinizdeyiz', '');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ayarlar`
--

CREATE TABLE `ayarlar` (
  `id` int(11) NOT NULL,
  `hakkimizda_metin` varchar(1000) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `ayarlar`
--

INSERT INTO `ayarlar` (`id`, `hakkimizda_metin`) VALUES
(1, 'Colombia Coffee ailesi olarak, en kaliteli kahve çekirdeklerini özenle seçiyor ve sizlerle buluşturuyoruz..');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `iletisim_mesajlari`
--

CREATE TABLE `iletisim_mesajlari` (
  `id` int(11) NOT NULL,
  `ad_soyad` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `talep_turu` varchar(50) NOT NULL,
  `mesaj` text NOT NULL,
  `yildiz` int(11) DEFAULT 5,
  `il` varchar(50) DEFAULT '',
  `ilce` varchar(100) DEFAULT '',
  `tarih` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `iletisim_mesajlari`
--

INSERT INTO `iletisim_mesajlari` (`id`, `ad_soyad`, `email`, `talep_turu`, `mesaj`, `yildiz`, `il`, `ilce`, `tarih`) VALUES
(4, 'Fatih Eren ipek', 'fatiherenipek15@gmail.com', 'Hizmet', 'selamlar ben adal', 5, '', '', '2026-05-03 21:49:01'),
(6, 'Fatih Eren ipek', 'fatiherenipek15@gmail.com', 'Menü', 'işletme menüsü kötü detaylandırın', 2, 'İstanbul', 'Tuzla', '2026-05-03 22:24:04'),
(11, 'Fatih Eren ipek', 'fatiherenipek15@gmail.com', 'Hizmet', 'aaaa', 5, 'İstanbul', 'Esenyurt', '2026-05-03 22:32:52');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `slider`
--

CREATE TABLE `slider` (
  `id` int(11) NOT NULL,
  `resim` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `slider`
--

INSERT INTO `slider` (`id`, `resim`) VALUES
(1, '1775463370_slider_1775326294_Strawberry_milk_shake_(cropped).jpg'),
(2, '1775332714_slider_menü-reklam-panosu-kahve1.png'),
(3, '1775332717_slider_menü-reklam-pano-kahve2.png'),
(4, '1775333245_slider_icon.png');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sosyal_medya`
--

CREATE TABLE `sosyal_medya` (
  `id` int(11) NOT NULL,
  `platform` varchar(50) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `sosyal_medya`
--

INSERT INTO `sosyal_medya` (`id`, `platform`, `url`) VALUES
(2, 'Instagram', 'https://www.instagram.com/colombiatr_/'),
(3, 'Facebook', 'https://www.facebook.com/colombiaacoffe/?locale=ku_TR'),
(4, 'Twitter', 'https://x.com/colombiaturkey_'),
(5, 'YouTube', 'https://www.youtube.com/@colombiacoffeeturkiye9951'),
(6, 'LinkedIn', 'https://tr.linkedin.com/company/colombiacoffee'),
(7, 'TikTok', 'https://www.tiktok.com/discover/colombia-kahve');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `subeler`
--

CREATE TABLE `subeler` (
  `id` int(11) NOT NULL,
  `il` varchar(50) NOT NULL,
  `ilce` varchar(100) NOT NULL,
  `resim` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `subeler`
--

INSERT INTO `subeler` (`id`, `il`, `ilce`, `resim`) VALUES
(1, 'İstanbul', 'Ümraniye', '1777841963_sube_ümraniyeşubecolombia.webp'),
(2, 'İstanbul', 'Tuzla', '1777845204_sube_colombiacoffekarasşube.webp');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `urunler`
--

CREATE TABLE `urunler` (
  `id` int(11) NOT NULL,
  `urun_adi` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL DEFAULT 'kahveler',
  `aciklama` text DEFAULT NULL,
  `resim` varchar(255) DEFAULT NULL,
  `fiyat` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `urunler`
--

INSERT INTO `urunler` (`id`, `urun_adi`, `kategori`, `aciklama`, `resim`, `fiyat`) VALUES
(2, 'Salep', 'kahveler', NULL, '1775326543_salep.png', 200.00),
(6, 'frappe', 'kahveler', NULL, '1775330443_frappe.png', 1.00),
(7, 'frappe', 'kahveler', NULL, '1775330450_frappe.png', 1.00),
(8, 'frappe', 'kahveler', NULL, '1775330462_frappe.png', 1.00),
(9, 'frappe', 'kahveler', NULL, '1775330471_frappe.png', 1.00),
(10, 'frappe', 'kahveler', NULL, '1775330487_frappe.png', 1.00),
(19, 'kupa1', 'kupalar', NULL, '1775815383_kupa1.jpg', 300.00),
(21, 'kupa1', 'kupalar', NULL, '1775815428_kupa1.jpg', 4000.00);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `yoneticiler`
--

CREATE TABLE `yoneticiler` (
  `id` int(11) NOT NULL,
  `kullanici_adi` varchar(50) NOT NULL,
  `sifre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `yoneticiler`
--

INSERT INTO `yoneticiler` (`id`, `kullanici_adi`, `sifre`) VALUES
(1, 'admin', '123456');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `anasayfa_subeler`
--
ALTER TABLE `anasayfa_subeler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `ayarlar`
--
ALTER TABLE `ayarlar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `iletisim_mesajlari`
--
ALTER TABLE `iletisim_mesajlari`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `slider`
--
ALTER TABLE `slider`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `sosyal_medya`
--
ALTER TABLE `sosyal_medya`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `subeler`
--
ALTER TABLE `subeler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `urunler`
--
ALTER TABLE `urunler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `yoneticiler`
--
ALTER TABLE `yoneticiler`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `anasayfa_subeler`
--
ALTER TABLE `anasayfa_subeler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `ayarlar`
--
ALTER TABLE `ayarlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `iletisim_mesajlari`
--
ALTER TABLE `iletisim_mesajlari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Tablo için AUTO_INCREMENT değeri `slider`
--
ALTER TABLE `slider`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `sosyal_medya`
--
ALTER TABLE `sosyal_medya`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `subeler`
--
ALTER TABLE `subeler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `urunler`
--
ALTER TABLE `urunler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Tablo için AUTO_INCREMENT değeri `yoneticiler`
--
ALTER TABLE `yoneticiler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
