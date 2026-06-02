<?php
/**
 * VERİTABANI BAĞLANTI VE OTOMATİK KURULUM DOSYASI (MIGRATION)
 * Bu dosya uluslararası PDO standartlarına uygun olarak UTF-8 (mb4) formatında veritabanı bağlantısı sağlar.
 * Ayrıca sistemde eksik tablo veya sütun varsa (Spagetti kodu engellemek için tek merkezden) otomatik oluşturur.
 */

// --- 1. VERİTABANI BAĞLANTI AYARLARI ---
$sunucu_adresi = 'localhost';
$veritabanı_ismi = 'colombia_coffee_db'; 
$veritabanı_kullanıcısı = 'root'; 
$veritabanı_şifresi = ''; 

try {
    // PDO sürücüsü ile MySQL veritabanına güvenli UTF-8 bağlantısı başlatılıyor
    $db = new PDO("mysql:host=$sunucu_adresi;dbname=$veritabanı_ismi;charset=utf8mb4", $veritabanı_kullanıcısı, $veritabanı_şifresi);
    
    // Hata yönetim modunu aktif ediyoruz; kodda bir SQL hatası olursa PHP bunu ekrana basacak
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // --- OTOMATİK TABLO OLUŞTURUCU BARİYERLERİ ---
    // Eğer 'subeler' tablosu veritabanında yoksa sistemin çökmemesi için otomatik oluşturuluyor
    $db->exec("CREATE TABLE IF NOT EXISTS subeler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        il VARCHAR(50) NOT NULL,
        ilce VARCHAR(100) NOT NULL,
        resim VARCHAR(255) NOT NULL
    )");

    // Ana sayfa vitrin şubelerini tutacak tablo yoksa oluşturuluyor
    $db->exec("CREATE TABLE IF NOT EXISTS anasayfa_subeler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        il VARCHAR(50) DEFAULT '',
        ilce VARCHAR(100) DEFAULT '',
        resim VARCHAR(255) DEFAULT ''
    )");
    
    // Eğer vitrin şubeleri tablosu bomboşsa, başlangıç için 6 adet boş slot yükleniyor
    $vitrin_kontrol_sorgusu = $db->query("SELECT COUNT(*) FROM anasayfa_subeler")->fetchColumn();
    if ($vitrin_kontrol_sorgusu == 0) {
        for ($i = 1; $i <= 6; $i++) {
            $db->exec("INSERT INTO anasayfa_subeler (il, ilce, resim) VALUES ('', '', '')");
        }
    }

    // Kullanıcıların web sitesinden göndereceği form mesajları için tablo kontrolü yapılıyor
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

    // --- SÜTUN GÜNCELLEME KONTROLLERİ (MİGRATION) ---
    // Eğer tablo daha önceden oluştuysa ve yeni sütunlar eksikse hata vermeden sessizce ekleniyor
    try {
        $db->exec("ALTER TABLE iletisim_mesajlari ADD COLUMN yildiz INT DEFAULT 5 AFTER mesaj");
    } catch (PDOException $hata) { /* Sütun zaten varsa hata fırlatmasını engelliyoruz */ }

    try {
        $db->exec("ALTER TABLE iletisim_mesajlari ADD COLUMN il VARCHAR(50) DEFAULT '' AFTER yildiz");
        $db->exec("ALTER TABLE iletisim_mesajlari ADD COLUMN ilce VARCHAR(100) DEFAULT '' AFTER il");
    } catch (PDOException $hata) { /* Sütun zaten varsa hata fırlatmasını engelliyoruz */ }

    try {
        $db->exec("ALTER TABLE urunler ADD COLUMN durum TINYINT(1) DEFAULT 1");
    } catch (PDOException $hata) { /* Sütun zaten varsa hata fırlatmasını engelliyoruz */ }

    try {
        // Kalite bölümü resimleri için ayarlar tablosuna yeni sütunlar ekleniyor
        $db->exec("ALTER TABLE ayarlar ADD COLUMN kalite_merkez VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE ayarlar ADD COLUMN kalite_1 VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE ayarlar ADD COLUMN kalite_2 VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE ayarlar ADD COLUMN kalite_3 VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE ayarlar ADD COLUMN kalite_4 VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE ayarlar ADD COLUMN kalite_5 VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE ayarlar ADD COLUMN kalite_6 VARCHAR(255) DEFAULT ''");
    } catch (PDOException $hata) { /* Sütunlar zaten varsa yoksay */ }

    try {
        // Kariyer url ayarları için yeni sütun
        $db->exec("ALTER TABLE ayarlar ADD COLUMN kariyer_url VARCHAR(255) DEFAULT ''");
    } catch (PDOException $hata) { /* Sütunlar zaten varsa yoksay */ }

    // Ayarlar tablosunda id=1 satırı yoksa sistemin sessizce başarısız olmaması için oluştur
    $ayar_kontrol = $db->query("SELECT COUNT(*) FROM ayarlar WHERE id = 1")->fetchColumn();
    if ($ayar_kontrol == 0) {
        $db->exec("INSERT INTO ayarlar (id) VALUES (1)");
    }

} catch (PDOException $veritabanı_hatası) {
    // Bağlantı esnasında bir kopma veya hata olursa çalışacak acil durum mesajı
    die("Veritabanı bağlantı hatası yaşandı: " . $veritabanı_hatası->getMessage());
}
?>