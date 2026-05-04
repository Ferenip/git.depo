<?php
session_start();

// GÜVENLİK KİLİDİ: Oturum yoksa login sayfasına at!
if (!isset($_SESSION['admin_oturum'])) {
    header("Location: login.php");
    exit;
}

include 'baglan.php';

// --- ARKA PLAN (AJAX) YENİ MESAJ KONTROLÜ ---
if (isset($_GET['check_new_messages'])) {
    $max_id = $db->query("SELECT MAX(id) FROM iletisim_mesajlari")->fetchColumn();
    echo $max_id ? $max_id : 0;
    exit;
}

$sayfa = isset($_GET['sayfa']) ? $_GET['sayfa'] : 'dashboard';

// --- ANA SAYFA: VİDEO YÜKLEME ---
if (isset($_POST['video_yukle'])) {
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['video']['tmp_name'], 'uploads/home_video.mp4');
        header("Location: admin.php?sayfa=anasayfa");
        exit;
    }
}

// --- ANA SAYFA: ŞUBE EKLEME İŞLEMİ ---
if (isset($_POST['sube_ekle'])) {
    $il = $_POST['il'];
    $ilce = $_POST['ilce'];
    $resim_adi = "";

    if (isset($_FILES['sube_resim']) && $_FILES['sube_resim']['error'] == 0) {
        $resim_adi = time() . '_sube_' . $_FILES['sube_resim']['name'];
        if (!is_dir('uploads/subeler')) mkdir('uploads/subeler', 0777, true);
        move_uploaded_file($_FILES['sube_resim']['tmp_name'], 'uploads/subeler/' . $resim_adi);
    }
    $sorgu = $db->prepare("INSERT INTO subeler (il, ilce, resim) VALUES (?, ?, ?)");
    $sorgu->execute([$il, $ilce, $resim_adi]);
    header("Location: admin.php?sayfa=kurumsal_magazalar");
    exit;
}

// --- ANA SAYFA: ŞUBE SİLME İŞLEMİ ---
if (isset($_GET['sube_sil'])) {
    $id = $_GET['sube_sil'];
    $sorgu = $db->prepare("DELETE FROM subeler WHERE id = ?");
    $sorgu->execute([$id]);
    header("Location: admin.php?sayfa=kurumsal_magazalar");
    exit;
}

// --- ANA SAYFA: JSON İLE TOPLU ŞUBE EKLEME (GÜVENLİ) ---
if (isset($_POST['toplu_sube_ekle'])) {
    if (isset($_FILES['json_dosya']) && $_FILES['json_dosya']['error'] == 0) {
        $dosya_adi = $_FILES['json_dosya']['name'];
        $dosya_uzantisi = strtolower(pathinfo($dosya_adi, PATHINFO_EXTENSION));

        // GÜVENLİK 1: Sadece .json uzantısını kabul et
        if ($dosya_uzantisi === 'json') {
            $json_icerik = file_get_contents($_FILES['json_dosya']['tmp_name']);
            $veriler = json_decode($json_icerik, true);

            // GÜVENLİK 2: JSON formatı geçerli mi ve dizi mi?
            if (json_last_error() === JSON_ERROR_NONE && is_array($veriler)) {
                $eklenen_sayi = 0;
                // GÜVENLİK 3: PDO ile SQL Injection koruması
                $sorgu = $db->prepare("INSERT INTO subeler (il, ilce, resim) VALUES (?, ?, ?)");

                foreach ($veriler as $sube) {
                    if (isset($sube['il']) && isset($sube['ilce'])) {
                        $sorgu->execute([htmlspecialchars($sube['il']), htmlspecialchars($sube['ilce']), '']);
                        $eklenen_sayi++;
                    }
                }
                echo "<script>alert('Güvenli Yükleme Başarılı! Toplam $eklenen_sayi şube eklendi.'); window.location.href='admin.php?sayfa=kurumsal_magazalar';</script>";
                exit;
            } else { echo "<script>alert('Hata: Dosya içeriği geçerli bir JSON formatında değil!');</script>"; }
        } else { echo "<script>alert('Güvenlik İhlali: Sadece .json uzantılı dosyalar yüklenebilir!');</script>"; }
    }
}

// --- ANA SAYFA VİTRİN ŞUBELERİ GÜNCELLEME İŞLEMİ ---
if (isset($_POST['anasayfa_sube_guncelle'])) {
    $id = $_POST['vitrin_id'];
    $il = $_POST['il'];
    $ilce = $_POST['ilce'];
    $resim_adi = $_POST['mevcut_resim']; // Yeni resim seçilmezse eski resmi korur

    if (isset($_FILES['resim']) && $_FILES['resim']['error'] == 0) {
        $resim_adi = time() . '_vitrin_' . $_FILES['resim']['name'];
        if (!is_dir('uploads/subeler')) mkdir('uploads/subeler', 0777, true);
        move_uploaded_file($_FILES['resim']['tmp_name'], 'uploads/subeler/' . $resim_adi);
    }

    $sorgu = $db->prepare("UPDATE anasayfa_subeler SET il = ?, ilce = ?, resim = ? WHERE id = ?");
    $sorgu->execute([$il, $ilce, $resim_adi, $id]);
    header("Location: admin.php?sayfa=anasayfa");
    exit;
}

// --- 1. ÜRÜN EKLEME İŞLEMİ (Kategori eklendi) ---
if (isset($_POST['urun_ekle'])) {
    $urun_adi = $_POST['urun_adi'];
    $kategori = $_POST['kategori']; // Yeni kategori verisi
    $fiyat = $_POST['fiyat'];
    $resim_adi = "";

    if (isset($_FILES['resim']) && $_FILES['resim']['error'] == 0) {
        $resim_adi = time() . '_' . $_FILES['resim']['name'];
        move_uploaded_file($_FILES['resim']['tmp_name'], 'uploads/' . $resim_adi);
    }

    $sorgu = $db->prepare("INSERT INTO urunler (urun_adi, kategori, fiyat, resim) VALUES (?, ?, ?, ?)");
    $sorgu->execute([$urun_adi, $kategori, $fiyat, $resim_adi]);
        header("Location: admin.php?sayfa=menu"); 
    exit;
}

// --- 2. ÜRÜN SİLME İŞLEMİ ---
if (isset($_GET['sil'])) {
    $id = $_GET['sil'];
    $sorgu = $db->prepare("DELETE FROM urunler WHERE id = ?");
    $sorgu->execute([$id]);
        header("Location: admin.php?sayfa=menu");
    exit;
}

// --- 3. SLIDER GÜNCELLEME İŞLEMİ ---
if (isset($_POST['slider_guncelle'])) {
    $slider_id = $_POST['slider_id'];
    
    if (isset($_FILES['slider_resim']) && $_FILES['slider_resim']['error'] == 0) {
        $resim_adi = time() . '_slider_' . $_FILES['slider_resim']['name'];
        move_uploaded_file($_FILES['slider_resim']['tmp_name'], 'uploads/' . $resim_adi);
        $sorgu = $db->prepare("UPDATE slider SET resim = ? WHERE id = ?");
        $sorgu->execute([$resim_adi, $slider_id]);
            header("Location: admin.php?sayfa=menu");
        exit;
    }
}

// --- 4. HAKKIMIZDA GÜNCELLEME İŞLEMİ ---
if (isset($_POST['hakkimizda_guncelle'])) {
    $metin = $_POST['hakkimizda_metin'];
    $sorgu = $db->prepare("UPDATE ayarlar SET hakkimizda_metin = ? WHERE id = 1");
    $sorgu->execute([$metin]);
    header("Location: admin.php?sayfa=kurumsal_hakkimizda&durum=ok");
    exit;
}

// --- 5. SOSYAL MEDYA EKLEME İŞLEMİ ---
if (isset($_POST['sosyal_ekle'])) {
    $platform = $_POST['platform'];
    $url = $_POST['url'];
    $sorgu = $db->prepare("INSERT INTO sosyal_medya (platform, url) VALUES (?, ?)");
    $sorgu->execute([$platform, $url]);
    header("Location: admin.php?sayfa=iletisim_sosyal");
    exit;
}

// --- 6. SOSYAL MEDYA SİLME İŞLEMİ ---
if (isset($_GET['sosyal_sil'])) {
    $id = $_GET['sosyal_sil'];
    $sorgu = $db->prepare("DELETE FROM sosyal_medya WHERE id = ?");
    $sorgu->execute([$id]);
    header("Location: admin.php?sayfa=iletisim_sosyal");
    exit;
}

// --- 7. MESAJ SİLME İŞLEMİ ---
if (isset($_GET['mesaj_sil'])) {
    $id = $_GET['mesaj_sil'];
    $sorgu = $db->prepare("DELETE FROM iletisim_mesajlari WHERE id = ?");
    $sorgu->execute([$id]);
    header("Location: admin.php?sayfa=iletisim_mesajlar");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Colombia Coffee - Yönetim Paneli</title>
    <style>
        body { font-family: Arial, sans-serif; background: url('back-ground.png') center/cover fixed; padding: 20px; color: #fff; }
        .admin-kutu { background: rgba(0, 37, 6, 0.95); /* Şık koyu mavi */ padding: 30px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); max-width: 800px; margin: 0 auto 30px auto; color: #fff; }
        .admin-kutu h2 { color: #c6a87c; }
        .admin-kutu p { color: #e0e0e0 !important; }
        input, select, textarea, button { padding: 10px; margin: 5px 0; width: 100%; box-sizing: border-box; }
        button { background: #153523; color: white; border: none; cursor: pointer; font-weight: bold; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: rgba(255,255,255,0.05); }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: middle; }
        .sil-btn { background: #d9534f; color: white; padding: 6px 12px; text-decoration: none; border-radius: 3px; font-size: 14px; }
        .flex-form { display: flex; gap: 10px; align-items: center; }
        .flex-form input[type="file"] { flex: 1; }
        .flex-form button { width: auto; padding: 8px 15px; margin: 0; }
        .kategori-etiket { font-size: 12px; background: #e0e0e0; padding: 3px 8px; border-radius: 10px; }
        /* Yönetim Paneli Yeni Stilleri */
        .dashboard-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; margin-top: 30px; }
        .dash-btn { background: #153523; color: #c6a87c; text-decoration: none; padding: 40px 20px; text-align: center; border-radius: 10px; font-size: 20px; font-weight: bold; box-shadow: 0 5px 15px rgba(0,0,0,0.2); transition: all 0.3s ease; width: 45%; box-sizing: border-box; }
        .dash-btn:hover { transform: translateY(-5px); background: #1e4d34; color: white; }
        .admin-nav { max-width: 800px; margin: 0 auto 20px auto; display: flex; justify-content: space-between; align-items: center;}
        .geri-btn { background: #c6a87c; color: #153523; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .geri-btn:hover { background: #b5986c; }
        /* Ana Dashboard Menüsünü Ortalamak İçin Kapsayıcı */
        .dashboard-wrapper { display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 85vh; }
        /* Şube Arama ve Liste Stilleri */
        .search-input { width: 100%; padding: 10px; margin-bottom: 5px; border-radius: 4px; border: 1px solid #ccc; color: #333; box-sizing: border-box; }
        select[size] { height: auto; max-height: 125px; overflow-y: auto; color: #333; }
        .toggle-btn-list { background: #c6a87c; color: #153523; width: 100%; padding: 12px; margin-top: 15px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; }
    </style>
</head>
<body>

<?php if ($sayfa == 'dashboard'): ?>
<div class="dashboard-wrapper">
    <!-- Güvenli Çıkış Butonu -->
    <div style="max-width: 800px; width: 100%; margin: 0 auto 20px auto; text-align: right;">
        <a href="logout.php" style="background: #d9534f; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold;">Güvenli Çıkış Yap</a>
    </div>
        <div class="admin-kutu" style="text-align: center; padding: 50px 20px; width: 100%;">
            <h1 style="color: #c6a87c; margin-bottom: 10px;">Admin Paneline Hoşgeldiniz</h1>
            <p style="color: #e0e0e0; font-size: 18px; margin-bottom: 30px;">Lütfen işlem yapmak istediğiniz menüyü seçin.</p>
        <div class="dashboard-grid">
            <a href="?sayfa=anasayfa" class="dash-btn">Ana Sayfa Yönetimi</a>
            <a href="?sayfa=kurumsal" class="dash-btn">Kurumsal Yönetimi</a>
            <a href="?sayfa=menu" class="dash-btn">Menü Yönetimi</a>
            <a href="?sayfa=iletisim" class="dash-btn">İletişim Yönetimi</a>
        </div>
    </div>
</div>
<?php elseif ($sayfa == 'anasayfa'): ?>
    <div class="admin-nav">
        <a href="admin.php" class="geri-btn">&laquo; Panele Dön</a>
        <a href="logout.php" class="sil-btn">Çıkış Yap</a>
    </div>
    <div class="admin-kutu" style="max-width: 1000px;">
        <h2>Ana Sayfa Vitrin Şubeleri (Maksimum 6 Adet)</h2>
        <p style="color:#ccc;">Ana sayfada dönen galerideki sadece 6 adet vitrin mağazasını (Slider gibi) buradan güncelleyebilirsiniz.</p>
        <table>
            <tr>
                <th width="5%">Sıra</th>
                <th width="15%">Mevcut Görsel</th>
                <th width="80%">Bilgileri Güncelle (İl / İlçe Yazarak)</th>
            </tr>
            <?php
            $vitrin_sorgu = $db->query("SELECT * FROM anasayfa_subeler ORDER BY id ASC");
            while ($vitrin = $vitrin_sorgu->fetch(PDO::FETCH_ASSOC)) {
                $resim = !empty($vitrin['resim']) ? 'uploads/subeler/'.$vitrin['resim'] : 'store-placeholder.jpg';
            ?>
            <tr>
                <td><strong><?php echo $vitrin['id']; ?></strong></td>
                <td><img src="<?php echo $resim; ?>" style="max-width: 80px; border-radius:4px;"></td>
                <td>
                    <form method="POST" enctype="multipart/form-data" class="flex-form" style="gap: 15px; align-items: flex-end;">
                        <input type="hidden" name="vitrin_id" value="<?php echo $vitrin['id']; ?>">
                        <input type="hidden" name="mevcut_resim" value="<?php echo $vitrin['resim']; ?>">
                        
                        <div style="flex: 1;">
                            <label style="font-size:12px; color:#aaa;">İl:</label>
                            <input type="text" name="il" value="<?php echo htmlspecialchars($vitrin['il']); ?>" required style="padding: 6px; font-size:14px;">
                        </div>
                        <div style="flex: 1;">
                            <label style="font-size:12px; color:#aaa;">İlçe:</label>
                            <input type="text" name="ilce" value="<?php echo htmlspecialchars($vitrin['ilce']); ?>" required style="padding: 6px; font-size:14px;">
                        </div>
                        <div style="flex: 1;">
                            <label style="font-size:12px; color:#aaa;">Yeni Resim:</label>
                            <input type="file" name="resim" accept="image/*" style="padding: 4px; font-size:12px; background:#fff;">
                        </div>
                        <button type="submit" name="anasayfa_sube_guncelle" style="padding: 10px 15px; background: #c6a87c; color: #153523;">Güncelle</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
<?php elseif ($sayfa == 'menu'): ?>
    <div class="admin-nav">
        <a href="admin.php" class="geri-btn">&laquo; Panele Dön</a>
        <a href="logout.php" class="sil-btn">Çıkış Yap</a>
    </div>
<div class="admin-kutu">
    <h2>Reklam Panosu (Slider) Yönetimi</h2>
    <table>
        <tr>
            <th width="10%">Sıra</th>
            <th width="20%">Mevcut Resim</th>
            <th width="70%">Yeni Resim Yükle</th>
        </tr>
        <?php
        $slider_sorgu = $db->query("SELECT * FROM slider ORDER BY id ASC");
        while ($slider = $slider_sorgu->fetch(PDO::FETCH_ASSOC)) {
            $resim_goster = file_exists('uploads/'.$slider['resim']) ? 'uploads/'.$slider['resim'] : $slider['resim'];
        ?>
        <tr>
            <td><strong>Görsel <?php echo $slider['id']; ?></strong></td>
            <td><img src="<?php echo $resim_goster; ?>" style="max-height: 50px; border-radius:4px;"></td>
            <td>
                <form method="POST" enctype="multipart/form-data" class="flex-form">
                    <input type="hidden" name="slider_id" value="<?php echo $slider['id']; ?>">
                    <input type="file" name="slider_resim" accept="image/*" required>
                    <button type="submit" name="slider_guncelle">Değiştir</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<div class="admin-kutu">
    <h2>Yeni Menü Ürünü Ekle</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Kategori Seçin:</label>
        <select name="kategori" required>
            <option value="kahveler">Kahveler ve İçecekler</option>
            <option value="kupalar">Kupalar ve Termoslar</option>
        </select>

        <label>Ürün Adı:</label>
        <input type="text" name="urun_adi" required>
        
        <label>Fiyat (₺):</label>
        <input type="number" step="0.01" name="fiyat" required>
        
        <label>Ürün Fotoğrafı:</label>
        <input type="file" name="resim" accept="image/*" required>
        
        <button type="submit" name="urun_ekle">Menüye Ekle</button>
    </form>
</div>

<div class="admin-kutu">
    <h2>Mevcut Menü Ürünleri</h2>
    <table>
        <tr>
            <th>Resim</th>
            <th>Ürün Adı</th>
            <th>Kategori</th>
            <th>Fiyat</th>
            <th>İşlem</th>
        </tr>
        <?php
        $sorgu = $db->query("SELECT * FROM urunler ORDER BY id DESC");
        while ($urun = $sorgu->fetch(PDO::FETCH_ASSOC)) {
            $resim = !empty($urun['resim']) ? 'uploads/'.$urun['resim'] : 'Yok';
        ?>
        <tr>
            <td><img src="<?php echo $resim; ?>" width="40"></td>
            <td><?php echo $urun['urun_adi']; ?></td>
            <td><span class="kategori-etiket"><?php echo strtoupper($urun['kategori']); ?></span></td>
            <td>₺<?php echo $urun['fiyat']; ?></td>
            <td><a href="?sil=<?php echo $urun['id']; ?>" class="sil-btn">Sil</a></td>
        </tr>
        <?php } ?>
    </table>
</div>

<?php elseif ($sayfa == 'kurumsal'): ?>
    <div class="admin-nav">
        <a href="admin.php" class="geri-btn">&laquo; Panele Dön</a>
        <a href="logout.php" class="sil-btn">Çıkış Yap</a>
    </div>
    <div class="admin-kutu" style="text-align: center; max-width: 900px; padding: 40px 20px;">
        <h2 style="color: #c6a87c; margin-bottom: 10px;">Kurumsal Yönetimi</h2>
        <p style="color: #e0e0e0; font-size: 16px; margin-bottom: 30px;">Lütfen düzenlemek istediğiniz bölümü seçin.</p>
        <div class="dashboard-grid" style="justify-content: center; gap: 15px;">
            <a href="?sayfa=kurumsal_hakkimizda" class="dash-btn" style="width: 30%; padding: 30px 10px; font-size: 18px;">Hakkımızda</a>
            <a href="?sayfa=kurumsal_magazalar" class="dash-btn" style="width: 30%; padding: 30px 10px; font-size: 18px;">Mağazalarımız</a>
            <a href="?sayfa=kurumsal_kariyer" class="dash-btn" style="width: 30%; padding: 30px 10px; font-size: 18px;">Kariyer</a>
        </div>
    </div>

<?php elseif ($sayfa == 'kurumsal_hakkimizda'): ?>
    <?php $hakkimizda = $db->query("SELECT hakkimizda_metin FROM ayarlar WHERE id = 1")->fetchColumn(); ?>
    <div class="admin-nav">
        <a href="admin.php?sayfa=kurumsal" class="geri-btn">&laquo; Kurumsal Panele Dön</a>
        <a href="logout.php" class="sil-btn">Çıkış Yap</a>
    </div>
    <div class="admin-kutu">
        <h2>Hakkımızda Yazısını Düzenle</h2>
        <?php if(isset($_GET['durum']) && $_GET['durum'] == 'ok') echo "<p style='color: lime; font-weight:bold;'>Yazı başarıyla güncellendi!</p>"; ?>
        <form method="POST">
            <label style="color: #ccc; font-size: 14px;">Hakkımızda Metni (Maksimum 800 Karakter - Sayfaya tam oturması için sınırlandırılmıştır):</label>
            <!-- maxlength="800" özelliği ile yöneticinin daha fazla girmesi engellenir -->
            <textarea name="hakkimizda_metin" rows="12" maxlength="800" required style="width: 100%; box-sizing: border-box; padding: 15px; margin-top: 10px; border-radius: 5px; border: 1px solid #ccc; color: #333; resize: vertical; font-size: 16px; font-family: 'Poppins', sans-serif;"><?php echo htmlspecialchars($hakkimizda); ?></textarea>
            <button type="submit" name="hakkimizda_guncelle" style="margin-top: 15px; background-color: #c6a87c; color: #153523; font-size: 16px;">Yazıyı Kaydet</button>
        </form>
    </div>

<?php elseif ($sayfa == 'kurumsal_magazalar'): ?>
    <div class="admin-nav">
        <a href="admin.php?sayfa=kurumsal" class="geri-btn">&laquo; Kurumsal Panele Dön</a>
        <a href="logout.php" class="sil-btn">Çıkış Yap</a>
    </div>
    <!-- TEK BİR KABUK VE İÇİNDE 3 KARE ALAN -->
    <div class="admin-kutu" style="max-width: 1300px;">
        <h2>Kurumsal Tüm Mağazalar Yönetimi</h2>
        <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap; margin-top: 20px;">
            
            <!-- KARE 1: TEKLİ EKLE -->
            <div style="flex: 1; min-width: 280px; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px;">
                <h3 style="color:#c6a87c; margin-bottom:15px; font-size:18px;">Tekli Şube Ekle</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="text" id="citySearch" class="search-input" placeholder="İl Ara..." onkeyup="filterCities()">
                    <select name="il" id="cityList" size="4" required style="width: 100%; margin-bottom: 10px;" onchange="syncCitySelection()">
                        <?php 
                        $iller = ["Adana", "Adıyaman", "Afyonkarahisar", "Ağrı", "Amasya", "Ankara", "Antalya", "Artvin", "Aydın", "Balıkesir", "Bilecik", "Bingöl", "Bitlis", "Bolu", "Burdur", "Bursa", "Çanakkale", "Çankırı", "Çorum", "Denizli", "Diyarbakır", "Edirne", "Elazığ", "Erzincan", "Erzurum", "Eskişehir", "Gaziantep", "Giresun", "Gümüşhane", "Hakkari", "Hatay", "Isparta", "Mersin", "İstanbul", "İzmir", "Kars", "Kastamonu", "Kayseri", "Kırklareli", "Kırşehir", "Kocaeli", "Konya", "Kütahya", "Malatya", "Manisa", "Kahramanmaraş", "Mardin", "Muğla", "Muş", "Nevşehir", "Niğde", "Ordu", "Rize", "Sakarya", "Samsun", "Siirt", "Sinop", "Sivas", "Tekirdağ", "Tokat", "Trabzon", "Tunceli", "Şanlıurfa", "Uşak", "Van", "Yozgat", "Zonguldak", "Aksaray", "Bayburt", "Karaman", "Kırıkkale", "Batman", "Şırnak", "Bartın", "Ardahan", "Iğdır", "Yalova", "Karabük", "Kilis", "Osmaniye", "Düzce"];
                        sort($iller);
                        foreach ($iller as $il) { echo "<option value='$il'>$il</option>"; }
                        ?>
                    </select>
                    <input type="text" id="districtSearch" class="search-input" placeholder="İlçe Ara..." onkeyup="filterDistricts()">
                    <select name="ilce" id="districtList" size="4" required style="width: 100%; margin-bottom: 10px; color: #333; background: #fff;" onchange="syncDistrictSelection()">
                        <option value="">Önce İl Seçiniz...</option>
                    </select>
                    <label style="font-size:12px; color:#aaa;">Şube Fotoğrafı:</label>
                    <input type="file" name="sube_resim" accept="image/*" required style="background:#fff; color:#333; padding:6px; border-radius:4px; margin-bottom:10px;">
                    <button type="submit" name="sube_ekle">Sisteme Ekle</button>
                </form>
            </div>

            <!-- KARE 2: TOPLU YÜKLE -->
            <div style="flex: 1; min-width: 280px; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px;">
                <h3 style="color:#c6a87c; margin-bottom:15px; font-size:18px;">Toplu Yükle (JSON)</h3>
                <p style="font-size: 13px; color: #ccc; margin-bottom: 15px;">Örnek Kullanım: <br><code style="color:#c6a87c;">[{"il":"Ankara", "ilce":"Çankaya"}]</code></p>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="json_dosya" accept=".json" required style="background:#fff; color:#333; padding:6px; border-radius:4px; margin-bottom: 10px; width:100%;">
                    <button type="submit" name="toplu_sube_ekle" style="background-color: #c6a87c; color: #153523;">Toplu Şube Yükle</button>
                </form>
            </div>

            <!-- KARE 3: LİSTE -->
            <div style="flex: 1.5; min-width: 350px; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px; max-height: 550px; overflow-y: auto;">
                <h3 style="color:#c6a87c; margin-bottom:15px; font-size:18px;">Mevcut Kurumsal Şubeler</h3>
                <input type="text" id="storeSearch" class="search-input" placeholder="Şubelerde Ara..." onkeyup="filterStores()" style="margin-bottom:10px;">
                <table id="storeTable">
                    <tr>
                        <th>Resim</th>
                        <th>İl / İlçe</th>
                        <th>İşlem</th>
                    </tr>
                    <?php
                    $sorgu = $db->query("SELECT * FROM subeler ORDER BY id DESC");
                    while ($sube = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                        $resim = !empty($sube['resim']) ? 'uploads/subeler/'.$sube['resim'] : 'store-placeholder.jpg';
                    ?>
                    <tr class="store-row">
                        <td><img src="<?php echo $resim; ?>" width="50" style="border-radius:4px;"></td>
                        <td><?php echo $sube['il'] . ' - ' . $sube['ilce']; ?></td>
                        <td><a href="?sayfa=kurumsal_magazalar&sube_sil=<?php echo $sube['id']; ?>" class="sil-btn" onclick="return confirm('Bu şubeyi tamamen silmek istediğinize emin misiniz?');">Sil</a></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($sayfa == 'iletisim'): ?>
    <div class="admin-nav">
        <a href="admin.php" class="geri-btn">&laquo; Panele Dön</a>
        <a href="logout.php" class="sil-btn">Çıkış Yap</a>
    </div>
    <div class="admin-kutu" style="text-align: center; max-width: 900px; padding: 40px 20px;">
        <h2 style="color: #c6a87c; margin-bottom: 10px;">İletişim Yönetimi</h2>
        <p style="color: #e0e0e0; font-size: 16px; margin-bottom: 30px;">Lütfen işlem yapmak istediğiniz alanı seçin.</p>
        <div class="dashboard-grid" style="justify-content: center; gap: 15px;">
            <a href="?sayfa=iletisim_sosyal" class="dash-btn" style="width: 45%; padding: 30px 10px; font-size: 18px;">Sosyal Medya Bilgi Düzenleme</a>
            <a href="?sayfa=iletisim_mesajlar" class="dash-btn" style="width: 45%; padding: 30px 10px; font-size: 18px;">Öneri ve Görüş Formları</a>
        </div>
    </div>

<?php elseif ($sayfa == 'iletisim_sosyal'): ?>
    <div class="admin-nav">
        <a href="admin.php?sayfa=iletisim" class="geri-btn">&laquo; İletişim Panele Dön</a>
        <a href="logout.php" class="sil-btn">Çıkış Yap</a>
    </div>
    <div class="admin-kutu">
        <h2>Sosyal Medya Adresleri Yönetimi</h2>
        <p style="margin-bottom: 20px;">Sitede görünmesini istediğiniz platformu seçip URL'sini (bağlantısını) girin. İkonlar otomatik olarak eklenecektir.</p>
        
        <form method="POST" style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px;">
            <label style="color: #c6a87c;">Platform (İkon) Seçin:</label>
            <select name="platform" required style="margin-bottom: 15px; cursor: pointer;">
                <option value="Instagram">Instagram</option>
                <option value="Facebook">Facebook</option>
                <option value="Twitter">Twitter (X)</option>
                <option value="YouTube">YouTube</option>
                <option value="LinkedIn">LinkedIn</option>
                <option value="TikTok">TikTok</option>
                <option value="WhatsApp">WhatsApp</option>
            </select>
            
            <label style="color: #c6a87c;">Bağlantı Adresi (URL):</label>
            <input type="url" name="url" placeholder="Örn: https://instagram.com/colombiacoffee" required>
            
            <button type="submit" name="sosyal_ekle" style="margin-top: 15px;">Listeye Ekle</button>
        </form>
        
        <table style="margin-top: 30px;">
            <tr><th>Platform</th><th>URL Bağlantısı</th><th width="10%">İşlem</th></tr>
            <?php
            $sosyal_sorgu = $db->query("SELECT * FROM sosyal_medya ORDER BY id DESC");
            while ($sosyal = $sosyal_sorgu->fetch(PDO::FETCH_ASSOC)) {
            ?>
            <tr>
                <td><strong style="color: #c6a87c;"><?php echo $sosyal['platform']; ?></strong></td>
                <td><a href="<?php echo $sosyal['url']; ?>" target="_blank" style="color: #a0a0a0;"><?php echo $sosyal['url']; ?></a></td>
                <td><a href="?sayfa=iletisim&sosyal_sil=<?php echo $sosyal['id']; ?>" class="sil-btn" onclick="return confirm('Silmek istediğinize emin misiniz?');">Sil</a></td>
            </tr>
            <?php } ?>
        </table>
    </div>

<?php elseif ($sayfa == 'iletisim_mesajlar'): ?>
    <div class="admin-nav">
        <a href="admin.php?sayfa=iletisim" class="geri-btn">&laquo; İletişim Panele Dön</a>
        <a href="logout.php" class="sil-btn">Çıkış Yap</a>
    </div>
    <div class="admin-kutu" style="max-width: 1200px;">
        <?php
        // Puan Ortalamasını Hesapla
        $puan_sorgu = $db->query("SELECT AVG(yildiz) as ortalama, COUNT(id) as kisi FROM iletisim_mesajlari WHERE yildiz > 0")->fetch(PDO::FETCH_ASSOC);
        $ortalama = round($puan_sorgu['ortalama'], 1);
        $kisi = $puan_sorgu['kisi'];
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <h2>Gelen Öneri ve Talepler</h2>
            <?php if ($kisi > 0): ?>
            <div style="background: rgba(0,0,0,0.4); padding: 10px 20px; border-radius: 8px; border: 1px solid var(--gold); display: flex; align-items: center;">
                <div style="color: var(--gold); margin-right: 10px; font-size: 18px;">
                    <?php
                    for($i=1; $i<=5; $i++) {
                        if($i <= floor($ortalama)) echo "<i class='fas fa-star'></i>";
                        elseif($i == ceil($ortalama) && $ortalama > floor($ortalama)) echo "<i class='fas fa-star-half-alt'></i>";
                        else echo "<i class='far fa-star' style='color:rgba(255,255,255,0.2)'></i>";
                    }
                    ?>
                </div>
                <span style="font-size: 18px; color: var(--gold); font-weight: bold;"><?php echo $ortalama; ?> / 5</span>
                <span style="font-size: 14px; color: #aaa; margin-left: 10px;">(<?php echo $kisi; ?> Kişi Puan Verdi)</span>
            </div>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap; margin-top: 20px;">
            <!-- SOL: MENÜ TALEPLERİ -->
            <div style="flex: 1; min-width: 300px; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px;">
                <h3 style="color:#c6a87c; margin-bottom:15px; font-size:18px;">Menü Talepleri</h3>
                <?php
                $menu_sorgu = $db->query("SELECT * FROM iletisim_mesajlari WHERE talep_turu = 'Menü' ORDER BY id DESC");
                $menu_mesajlar = $menu_sorgu->fetchAll(PDO::FETCH_ASSOC);
                if (count($menu_mesajlar) > 0) {
                    foreach ($menu_mesajlar as $mesaj) {
                        echo "<div style='background:rgba(0,0,0,0.4); padding: 15px; border-radius:5px; margin-bottom:15px; border-left: 4px solid var(--gold);'>";
                        echo "<div style='display:flex; justify-content:space-between; margin-bottom:5px;'>";
                        echo "<strong style='color:#c6a87c;'>{$mesaj['ad_soyad']}</strong>";
                        echo "<span style='font-size:12px; color:#aaa;'>" . date('d.m.Y H:i', strtotime($mesaj['tarih'])) . "</span>";
                        echo "</div>";
                        echo "<div style='font-size:13px; color:#ccc; margin-bottom:5px;'><i class='fas fa-envelope'></i> {$mesaj['email']}</div>";
                        echo "<div style='font-size:12px; color:#c6a87c; margin-bottom:10px;'>";
                        for($i=1; $i<=5; $i++) {
                            if($i <= $mesaj['yildiz']) echo "<i class='fas fa-star'></i>";
                            else echo "<i class='far fa-star' style='color:rgba(255,255,255,0.2)'></i>";
                        }
                        echo "</div>";
                        echo "<p style='font-size:14px; line-height:1.5; margin-bottom:10px;'>{$mesaj['mesaj']}</p>";
                        
                        echo "<div style='display:flex; justify-content:space-between; align-items:center;'>";
                        echo "  <a href='?sayfa=iletisim_mesajlar&mesaj_sil={$mesaj['id']}' class='sil-btn' onclick='return confirm(\"Bu mesajı silmek istediğinize emin misiniz?\");'>Mesajı Sil</a>";
                        if (!empty($mesaj['il'])) {
                            echo "  <span style='font-size:12px; color:#c6a87c; background: rgba(0,0,0,0.5); padding: 4px 8px; border-radius: 4px;'><i class='fas fa-map-marker-alt'></i> {$mesaj['il']} / {$mesaj['ilce']}</span>";
                        }
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p style='color:#aaa; font-size:14px;'>Henüz menü talebi bulunmuyor.</p>";
                }
                ?>
            </div>

            <!-- SAĞ: HİZMET TALEPLERİ -->
            <div style="flex: 1; min-width: 300px; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px;">
                <h3 style="color:#c6a87c; margin-bottom:15px; font-size:18px;">Hizmet Talepleri</h3>
                <?php
                $hizmet_sorgu = $db->query("SELECT * FROM iletisim_mesajlari WHERE talep_turu = 'Hizmet' ORDER BY id DESC");
                $hizmet_mesajlar = $hizmet_sorgu->fetchAll(PDO::FETCH_ASSOC);
                if (count($hizmet_mesajlar) > 0) {
                    foreach ($hizmet_mesajlar as $mesaj) {
                        echo "<div style='background:rgba(0,0,0,0.4); padding: 15px; border-radius:5px; margin-bottom:15px; border-left: 4px solid #d9534f;'>";
                        echo "<div style='display:flex; justify-content:space-between; margin-bottom:5px;'>";
                        echo "<strong style='color:#c6a87c;'>{$mesaj['ad_soyad']}</strong>";
                        echo "<span style='font-size:12px; color:#aaa;'>" . date('d.m.Y H:i', strtotime($mesaj['tarih'])) . "</span>";
                        echo "</div>";
                        echo "<div style='font-size:13px; color:#ccc; margin-bottom:5px;'><i class='fas fa-envelope'></i> {$mesaj['email']}</div>";
                        echo "<div style='font-size:12px; color:#c6a87c; margin-bottom:10px;'>";
                        for($i=1; $i<=5; $i++) {
                            if($i <= $mesaj['yildiz']) echo "<i class='fas fa-star'></i>";
                            else echo "<i class='far fa-star' style='color:rgba(255,255,255,0.2)'></i>";
                        }
                        echo "</div>";
                        echo "<p style='font-size:14px; line-height:1.5; margin-bottom:10px;'>{$mesaj['mesaj']}</p>";
                        
                        echo "<div style='display:flex; justify-content:space-between; align-items:center;'>";
                        echo "  <a href='?sayfa=iletisim_mesajlar&mesaj_sil={$mesaj['id']}' class='sil-btn' onclick='return confirm(\"Bu mesajı silmek istediğinize emin misiniz?\");'>Mesajı Sil</a>";
                        if (!empty($mesaj['il'])) {
                            echo "  <span style='font-size:12px; color:#c6a87c; background: rgba(0,0,0,0.5); padding: 4px 8px; border-radius: 4px;'><i class='fas fa-map-marker-alt'></i> {$mesaj['il']} / {$mesaj['ilce']}</span>";
                        }
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p style='color:#aaa; font-size:14px;'>Henüz hizmet talebi bulunmuyor.</p>";
                }
                ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// Türkiye İlçe Veri Seti (Önemli iller ve ilçeler; hepsini eklemek için bu listeyi genişletebilirsin)
const turkiyeData = {
    "İstanbul": ["Adalar", "Arnavutköy", "Ataşehir", "Avcılar", "Bağcılar", "Bahçelievler", "Bakırköy", "Başakşehir", "Bayrampaşa", "Beşiktaş", "Beykoz", "Beylikdüzü", "Beyoğlu", "Büyükçekmece", "Çatalca", "Çekmeköy", "Esenler", "Esenyurt", "Eyüpsultan", "Fatih", "Gaziosmanpaşa", "Güngören", "Kadıköy", "Kağıthane", "Kartal", "Küçükçekmece", "Maltepe", "Pendik", "Sancaktepe", "Sarıyer", "Silivri", "Sultanbeyli", "Sultangazi", "Şile", "Şişli", "Tuzla", "Ümraniye", "Üsküdar", "Zeytinburnu"],
    "Ankara": ["Akyurt", "Altındağ", "Ayaş", "Bala", "Beypazarı", "Çamlıdere", "Çankaya", "Çubuk", "Elmadağ", "Etimesgut", "Evren", "Gölbaşı", "Güdül", "Haymana", "Kahramankazan", "Kalecik", "Keçiören", "Kızılcahamam", "Mamak", "Nallıhan", "Polatlı", "Pursaklar", "Sincan", "Şereflikoçhisar", "Yenimahalle"],
    "İzmir": ["Aliağa", "Balçova", "Bayındır", "Bayraklı", "Bergama", "Beydağ", "Bornova", "Buca", "Çeşme", "Çiğli", "Dikili", "Foça", "Gaziemir", "Güzelbahçe", "Karabağlar", "Karaburun", "Karşıyaka", "Kemalpaşa", "Kınık", "Kiraz", "Konak", "Menderes", "Menemen", "Narlıdere", "Ödemiş", "Seferihisar", "Selçuk", "Tire", "Torbalı", "Urla"],
    "Sakarya": ["Adapazarı", "Akyazı", "Arifiye", "Erenler", "Ferizli", "Geyve", "Hendek", "Karapürçek", "Karasu", "Kaynarca", "Kocaali", "Pamukova", "Sapanca", "Serdivan", "Söğütlü", "Taraklı"],
    // Diğer tüm illeri bu şekilde eklemeye devam edebilirsin.
};

function filterCities() {
    let input = document.getElementById('citySearch').value.toLocaleLowerCase('tr-TR');
    let options = document.getElementById('cityList').options;
    for (let i = 0; i < options.length; i++) {
        let text = options[i].text.toLocaleLowerCase('tr-TR');
        options[i].style.display = text.includes(input) ? '' : 'none';
    }
}

// İL SEÇİLDİĞİNDE İLÇELERİ DOLDURAN ASIL FONKSİYON
function syncCitySelection() {
    const cityList = document.getElementById('cityList');
    const citySearch = document.getElementById('citySearch');
    const districtList = document.getElementById('districtList');
    const districtSearch = document.getElementById('districtSearch');
    
    citySearch.value = cityList.value; // İl arama kutusunu doldur
    districtList.innerHTML = ""; // İlçe listesini temizle
    districtSearch.value = ""; // İlçe arama kutusunu temizle

    if (turkiyeData[cityList.value]) {
        turkiyeData[cityList.value].forEach(dist => {
            let option = document.createElement("option");
            option.value = dist;
            option.text = dist;
            districtList.appendChild(option);
        });
    } else {
        let option = document.createElement("option");
        option.text = "İlçeler yüklenemedi";
        districtList.appendChild(option);
    }
}

function filterDistricts() {
    let input = document.getElementById('districtSearch').value.toLocaleLowerCase('tr-TR');
    let options = document.getElementById('districtList').options;
    for (let i = 0; i < options.length; i++) {
        let text = options[i].text.toLocaleLowerCase('tr-TR');
        options[i].style.display = text.includes(input) ? '' : 'none';
    }
}

function syncDistrictSelection() {
    document.getElementById('districtSearch').value = document.getElementById('districtList').value;
}

function filterStores() {
    let input = document.getElementById('storeSearch').value.toLocaleLowerCase('tr-TR');
    let rows = document.getElementsByClassName('store-row');
    for (let i = 0; i < rows.length; i++) {
        let text = rows[i].innerText.toLocaleLowerCase('tr-TR');
        rows[i].style.display = text.includes(input) ? '' : 'none';
    }
}

// --- 1. OTOMATİK ÇIKIŞ SİSTEMİ (5 DAKİKA HAREKETSİZLİK) ---
let inactivityTimer;
function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(() => {
        window.location.href = 'logout.php'; // 5 dakika (300.000 ms) dolunca çıkış yap
    }, 300000); 
}

// Kullanıcı hareketlerini dinle ve süreyi sıfırla
window.onload = resetInactivityTimer;
document.onmousemove = resetInactivityTimer;
document.onkeydown = resetInactivityTimer;
document.onclick = resetInactivityTimer;
document.onscroll = resetInactivityTimer;

// --- 2. YENİ MESAJ GELDİĞİNDE SAYFAYI YENİLEME SİSTEMİ ---
<?php $mevcut_max_id = $db->query("SELECT MAX(id) FROM iletisim_mesajlari")->fetchColumn() ?: 0; ?>
let currentMaxId = <?php echo $mevcut_max_id; ?>;

setInterval(() => {
    fetch('admin.php?check_new_messages=1')
        .then(response => response.text())
        .then(data => {
            let fetchedMaxId = parseInt(data);
            if (fetchedMaxId > currentMaxId) {
                // Yeni bir mesaj geldiğinde sayfayı yenile
                window.location.reload();
            }
        })
        .catch(err => console.error(err));
}, 10000); // Her 10 saniyede bir sessizce kontrol eder
</script>
</body>
</html>