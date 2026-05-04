<?php
$host = 'localhost';
$dbname = 'colombia_coffee_db'; 
$kullanici = 'root'; 
$sifre = ''; 

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $kullanici, $sifre);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Şubeler için otomatik tablo oluşturucu (Eğer yoksa oluşturur)
    $db->exec("CREATE TABLE IF NOT EXISTS subeler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        il VARCHAR(50) NOT NULL,
        ilce VARCHAR(100) NOT NULL,
        resim VARCHAR(255) NOT NULL
    )");

    // --- YENİ: Ana Sayfa Vitrin Şubeleri (Maksimum 6 Adet) ---
    $db->exec("CREATE TABLE IF NOT EXISTS anasayfa_subeler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        il VARCHAR(50) DEFAULT '',
        ilce VARCHAR(100) DEFAULT '',
        resim VARCHAR(255) DEFAULT ''
    )");
    
    $check_vitrin = $db->query("SELECT COUNT(*) FROM anasayfa_subeler")->fetchColumn();
    if ($check_vitrin == 0) {
        for ($i = 1; $i <= 6; $i++) {
            $db->exec("INSERT INTO anasayfa_subeler (il, ilce, resim) VALUES ('Yakında', 'Hizmetinizdeyiz', '')");
        }
    }

    // Ayarlar (Hakkımızda) için tablo oluşturucu
    $db->exec("CREATE TABLE IF NOT EXISTS ayarlar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hakkimizda_metin VARCHAR(1000) DEFAULT ''
    )");

    // Tablo boşsa varsayılan metni ekle
    $check = $db->query("SELECT COUNT(*) FROM ayarlar")->fetchColumn();
    if ($check == 0) {
        $db->exec("INSERT INTO ayarlar (hakkimizda_metin) VALUES ('Colombia Coffee ailesi olarak, en kaliteli kahve çekirdeklerini özenle seçiyor ve sizlerle buluşturuyoruz...')");
    }

    // --- YENİ: Sosyal Medya Hesapları Tablosu ---
    $db->exec("CREATE TABLE IF NOT EXISTS sosyal_medya (
        id INT AUTO_INCREMENT PRIMARY KEY,
        platform VARCHAR(50) NOT NULL,
        url VARCHAR(255) NOT NULL
    )");

    // --- YENİ: İletişim Formundan Gelen Mesajlar Tablosu ---
    $db->exec("CREATE TABLE IF NOT EXISTS iletisim_mesajlari (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ad_soyad VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        talep_turu VARCHAR(50) NOT NULL,
        mesaj TEXT NOT NULL,
        yildiz INT DEFAULT 5,
        il VARCHAR(50) DEFAULT '',
        ilce VARCHAR(100) DEFAULT '',
        tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Daha önce oluşturulmuş tabloya "yildiz" sütununu ekler (Hata vermemesi için sessiz çalışır)
    try {
        $db->exec("ALTER TABLE iletisim_mesajlari ADD COLUMN yildiz INT DEFAULT 5 AFTER mesaj");
    } catch (PDOException $e) { }

    // Daha önce oluşturulmuş tabloya "il" ve "ilce" sütunlarını ekler
    try {
        $db->exec("ALTER TABLE iletisim_mesajlari ADD COLUMN il VARCHAR(50) DEFAULT '' AFTER yildiz");
        $db->exec("ALTER TABLE iletisim_mesajlari ADD COLUMN ilce VARCHAR(100) DEFAULT '' AFTER il");
    } catch (PDOException $e) { }

} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>