<?php
session_start();

// GÜVENLİK KİLİDİ: Oturum yoksa login sayfasına at!
if (!isset($_SESSION['admin_oturum'])) {
    header("Location: login.php");
    exit;
}

include 'baglan.php'; // Veritabanı bağlantısı
include 'admin_actions.php'; // Tüm PHP işlemleri ve güvenlik kontrolleri
?>

<!DOCTYPE html>
<!-- Admin paneli çeviriden etkilenmemesi için Google Translate engellendi -->
<html lang="tr" translate="no" class="notranslate">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colombia Coffee - Yönetim Paneli</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css?v=<?php echo time(); ?>">
</head>

<body>

    <?php if ($sayfa == 'dashboard'): ?>
    <div class="dashboard-wrapper">
        <!-- Güvenli Çıkış Butonu -->
        <div class="admin-nav">
            <span></span> <!-- Boşluk için -->
            <a href="logout.php" class="cikis-btn">Güvenli Çıkış Yap</a>
        </div>
        <div class="admin-kutu admin-kutu-merkez">
            <h1>Admin Paneline Hoşgeldiniz</h1>
            <p>Lütfen işlem yapmak istediğiniz menüyü seçin.</p>
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
        <a href="logout.php" class="cikis-btn">Çıkış Yap</a>
    </div>

    <div class="admin-kutu admin-kutu-genis">
        <h2>Ana Sayfa Arka Plan Videosu Yönetimi</h2>
        <?php if (isset($_GET['durum']) && $_GET['durum'] == 'gecersiz_video'): ?>
        <p class="uyari-mesaji uyari-hata">❌ Hata: Sadece MP4 ve WEBM formatında video yükleyebilirsiniz!</p>
        <?php elseif (isset($_GET['durum']) && $_GET['durum'] == 'kalite_ok'): ?>
        <p class="uyari-mesaji uyari-basari">✅ Kalite bölümü görselleri başarıyla güncellendi!</p>
        <?php elseif (isset($_GET['durum']) && $_GET['durum'] == 'kalite_kismen'): ?>
        <p class="uyari-mesaji uyari-basari">⚠️ Resimlerin bir kısmı yüklendi ancak bazıları desteklenmeyen format veya boyut aşımı nedeniyle reddedildi!</p>
        <?php elseif (isset($_GET['durum']) && $_GET['durum'] == 'kalite_hata'): ?>
        <p class="uyari-mesaji uyari-hata">❌ Hata: Yüklemek istediğiniz görseller geçersiz (Sadece JPG, PNG, WEBP, GIF kabul edilir) veya dosya boyutu çok yüksek!</p>
        <?php endif; ?>
        <p>Ana sayfanın en üstünde dönen videoyu belirlemek için aşağıdaki iki seçenekten birini kullanın (Birini
            kaydettiğinizde diğeri iptal olur).</p>

        <div class="flex-container">
            <!-- 1. Seçenek: PC'den Video Yükle -->
            <div class="flex-item">
                <h3>1. Bilgisayardan Video Yükle</h3>
                <form action="?sayfa=anasayfa" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="file" name="video" accept="video/mp4,video/webm" required>
                    <button type="submit" name="video_yukle" class="btn-gold">Videoyu Yükle</button>
                </form>
            </div>

            <!-- 2. Seçenek: URL Gir -->
            <?php 
            $ayar_sorgu = $db->query("SELECT * FROM ayarlar WHERE id = 1");
            $ayarlar = $ayar_sorgu ? $ayar_sorgu->fetch(PDO::FETCH_ASSOC) : [];
            $mevcut_url = $ayarlar['video_url'] ?? '';
            ?>
            <div class="flex-item">
                <h3>2. Video URL'si Gir (YouTube vb.)</h3>
                <form action="?sayfa=anasayfa" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="text" name="video_url" placeholder="Örn: https://www.youtube.com/embed/..."
                        value="<?php echo htmlspecialchars($mevcut_url); ?>" required>
                    <button type="submit" name="url_kaydet" class="btn-gold">URL'yi Kaydet</button>
                </form>
            </div>
        </div>
    </div>

    <div class="admin-kutu admin-kutu-genis">
        <h2>Kalite Bölümü Etkileşimli Görselleri (Hover)</h2>
        <p>Ana sayfadaki "Kaliteli Kahve" bölümündeki 6 butona fareyle gelindiğinde çıkacak resimleri ve ortadaki sabit
            resmi buradan yükleyebilirsiniz.</p>
        <form action="?sayfa=anasayfa" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="flex-container">
                <div class="flex-item" style="min-width: 250px;">
                    <label style="color:#c6a87c; font-weight:bold;">Ortadaki Varsayılan Resim</label><br>
                    <small>(Hiçbirine dokunulmadığında görünen)</small>
                    <input type="file" name="kalite_merkez" accept="image/*">
                    <?php if(!empty($ayarlar['kalite_merkez'])) echo "<img src='{$ayarlar['kalite_merkez']}' style='height:40px; margin-top:5px; border-radius:4px;'>"; ?>
                </div>
                <div class="flex-item" style="min-width: 250px;">
                    <label style="color:#c6a87c; font-weight:bold;">1. Kutu: Rahat Hissederim</label><br>
                    <small>(Rahat tavırda kahve içen vb.)</small>
                    <input type="file" name="kalite_1" accept="image/*">
                    <?php if(!empty($ayarlar['kalite_1'])) echo "<img src='{$ayarlar['kalite_1']}' style='height:40px; margin-top:5px; border-radius:4px;'>"; ?>
                </div>
                <div class="flex-item" style="min-width: 250px;">
                    <label style="color:#c6a87c; font-weight:bold;">2. Kutu: Her Gün Yenilen</label><br>
                    <small>(İş yerinde kahve içen kadın vb.)</small>
                    <input type="file" name="kalite_2" accept="image/*">
                    <?php if(!empty($ayarlar['kalite_2'])) echo "<img src='{$ayarlar['kalite_2']}' style='height:40px; margin-top:5px; border-radius:4px;'>"; ?>
                </div>
                <div class="flex-item" style="min-width: 250px;">
                    <label style="color:#c6a87c; font-weight:bold;">3. Kutu: Taze Ürün</label><br>
                    <small>(Çekirdekleri çeken adam vb.)</small>
                    <input type="file" name="kalite_3" accept="image/*">
                    <?php if(!empty($ayarlar['kalite_3'])) echo "<img src='{$ayarlar['kalite_3']}' style='height:40px; margin-top:5px; border-radius:4px;'>"; ?>
                </div>
                <div class="flex-item" style="min-width: 250px;">
                    <label style="color:#c6a87c; font-weight:bold;">4. Kutu: Önce Kalite</label><br>
                    <small>(Logo olan kahve paketi vb.)</small>
                    <input type="file" name="kalite_4" accept="image/*">
                    <?php if(!empty($ayarlar['kalite_4'])) echo "<img src='{$ayarlar['kalite_4']}' style='height:40px; margin-top:5px; border-radius:4px;'>"; ?>
                </div>
                <div class="flex-item" style="min-width: 250px;">
                    <label style="color:#c6a87c; font-weight:bold;">5. Kutu: Güne Erken Başla</label><br>
                    <small>(Camdan dışarı bakan erkek vb.)</small>
                    <input type="file" name="kalite_5" accept="image/*">
                    <?php if(!empty($ayarlar['kalite_5'])) echo "<img src='{$ayarlar['kalite_5']}' style='height:40px; margin-top:5px; border-radius:4px;'>"; ?>
                </div>
                <div class="flex-item" style="min-width: 250px;">
                    <label style="color:#c6a87c; font-weight:bold;">6. Kutu: Dostlara Yer Açın</label><br>
                    <small>(Kafede oturan arkadaşlar vb.)</small>
                    <input type="file" name="kalite_6" accept="image/*">
                    <?php if(!empty($ayarlar['kalite_6'])) echo "<img src='{$ayarlar['kalite_6']}' style='height:40px; margin-top:5px; border-radius:4px;'>"; ?>
                </div>
            </div>
            <button type="submit" name="kalite_gorsel_guncelle" class="btn-gold" style="margin-top: 20px;">Görselleri
                Kaydet</button>
        </form>
    </div>

    <div class="admin-kutu" style="max-width: 1000px;">
        <h2>Ana Sayfa Vitrin Şubeleri (Maksimum 6 Adet)</h2>
        <p>Ana sayfada dönen galerideki sadece 6 adet vitrin mağazasını (Slider gibi) buradan güncelleyebilirsiniz.</p>
        <table>
            <tr>
                <th width="5%">Sıra</th>
                <th width="15%">Mevcut Görsel</th>
                <th width="80%">Bilgileri Güncelle (İl / İlçe Yazarak)</th>
            </tr>
            <?php
                $vitrin_sorgu = $db->query("SELECT * FROM anasayfa_subeler ORDER BY id ASC");
                while ($vitrin = $vitrin_sorgu->fetch(PDO::FETCH_ASSOC)) {
                    $resim = !empty($vitrin['resim']) ? 'uploads/subeler/' . $vitrin['resim'] : 'store-placeholder.jpg';
                    ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($vitrin['id']); ?></strong></td>
                <td><img src="<?php echo $resim; ?>" alt="Vitrin" width="80" style="border-radius:4px;"></td>
                <td>
                    <form action="?sayfa=anasayfa" method="POST" enctype="multipart/form-data" class="flex-form">
                        <input type="hidden" name="vitrin_id" value="<?php echo $vitrin['id']; ?>">
                        <input type="hidden" name="mevcut_resim" value="<?php echo $vitrin['resim']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <div>
                            <label>İl:</label>
                            <input type="text" name="il" value="<?php echo htmlspecialchars($vitrin['il']); ?>"
                                required>
                        </div>
                        <div>
                            <label>İlçe:</label>
                            <input type="text" name="ilce" value="<?php echo htmlspecialchars($vitrin['ilce']); ?>"
                                required>
                        </div>
                        <div>
                            <label>Yeni Resim:</label>
                            <input type="file" name="resim" accept="image/*">
                        </div>
                        <button type="submit" name="anasayfa_sube_guncelle" class="btn-gold">Güncelle</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
    <?php elseif ($sayfa == 'menu'): ?>
    <div class="admin-nav">
        <a href="admin.php" class="geri-btn">&laquo; Panele Dön</a>
        <a href="logout.php" class="cikis-btn">Çıkış Yap</a>
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
                    $resim_goster = file_exists('uploads/' . $slider['resim']) ? 'uploads/' . $slider['resim'] : $slider['resim'];
                    ?>
            <tr>
                <td><strong>Görsel <?php echo htmlspecialchars($slider['id']); ?></strong></td>
                <td><img src="<?php echo $resim_goster; ?>" alt="Slider" height="50" style="border-radius:4px;"></td>
                <td>
                    <form method="POST" enctype="multipart/form-data" class="flex-form">
                        <input type="hidden" name="slider_id" value="<?php echo $slider['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
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

        <?php if (isset($_GET['durum'])): ?>
        <?php if ($_GET['durum'] == 'bos_alan'): ?>
        <p class="uyari-mesaji uyari-hata">⚠️ Hata: Formu boş gönderemezsiniz!</p>
        <?php elseif ($_GET['durum'] == 'gecersiz_fiyat'): ?>
        <p class="uyari-mesaji uyari-hata">⚠️ Hata: Lütfen geçerli ve pozitif bir fiyat giriniz!</p>
        <?php elseif ($_GET['durum'] == 'gecersiz_dosya'): ?>
        <p class="uyari-mesaji uyari-hata">❌ Hata: Sadece JPG, JPEG, PNG, WEBP formatında resimler yükleyebilirsiniz!
        </p>
        <?php elseif ($_GET['durum'] == 'eklendi'): ?>
        <p class="uyari-mesaji uyari-basari">✅ Ürün başarıyla menüye eklendi.</p>
        <?php elseif ($_GET['durum'] == 'guncellendi'): ?>
        <p class="uyari-mesaji uyari-basari">✅ Toplu değişiklikler ve güncellemeler başarıyla kaydedildi.</p>
        <?php endif; ?>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="text" name="urun_adi" placeholder="Ürün Adı" required>
            <select name="kategori" required>
                <option value="kahveler">Kahveler</option>
                <option value="kupalar">Kupalar</option>
            </select>
            <input type="number" name="fiyat" placeholder="Fiyat (Örn: 25.50)" step="0.01" required>
            <input type="file" name="resim" accept="image/*" required>
            <button type="submit" name="urun_ekle">Ürünü Ekle</button>
        </form>
    </div>

    <div class="admin-kutu">
        <form method="POST" action="admin.php?sayfa=menu" enctype="multipart/form-data">
            <div class="admin-nav" style="max-width: none; margin-bottom: 15px;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <h2>Mevcut Menü Ürünleri</h2>
                <button type="submit" name="toplu_durum_kaydet" class="toplu-kaydet-btn">Değişiklikleri Kaydet</button>
            </div>
            <table>
                <tr>
                    <th>Resim</th>
                    <th>Ürün Adı</th>
                    <th>
                        Kategori
                        <select id="kategoriFiltre"
                            style="width: auto; display: inline-block; margin-left: 5px; padding: 3px; font-size: 13px;">
                            <option value="tumu">Tümü</option>
                            <option value="kahveler">Kahveler</option>
                            <option value="kupalar">Kupalar</option>
                        </select>
                    </th>
                    <th>Fiyat</th>
                    <th>İşlem</th>
                </tr>
                <?php
                    $sorgu = $db->query("SELECT * FROM urunler ORDER BY id DESC");
                    while ($urun = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                        $resim = !empty($urun['resim']) ? 'uploads/' . $urun['resim'] : 'Yok';
                        ?>
                <tr class="urun-satiri" data-kategori="<?php echo $urun['kategori']; ?>">
                    <td>
                        <img src="<?php echo $resim; ?>" width="40"
                            alt="<?php echo htmlspecialchars($urun['urun_adi']); ?>"
                            id="img_<?php echo $urun['id']; ?>">
                        <input type="file" name="resimler[<?php echo $urun['id']; ?>]"
                            id="file_<?php echo $urun['id']; ?>" accept="image/*"
                            style="display:none; width: 100px; padding: 2px; font-size:11px;">
                    </td>
                    <td>
                        <span
                            id="text_ad_<?php echo $urun['id']; ?>"><?php echo htmlspecialchars($urun['urun_adi']); ?></span>
                        <input type="text" name="urun_adlari[<?php echo $urun['id']; ?>]"
                            id="input_ad_<?php echo $urun['id']; ?>"
                            value="<?php echo htmlspecialchars($urun['urun_adi']); ?>"
                            style="display:none; padding: 5px;">
                    </td>
                    <td>
                        <span id="text_kat_<?php echo $urun['id']; ?>"
                            class="kategori-etiket"><?php echo strtoupper(htmlspecialchars($urun['kategori'])); ?></span>
                        <select name="kategoriler[<?php echo $urun['id']; ?>]" id="input_kat_<?php echo $urun['id']; ?>"
                            style="display:none; padding: 5px;">
                            <option value="kahveler" <?php echo $urun['kategori'] == 'kahveler' ? 'selected' : ''; ?>>
                                Kahveler</option>
                            <option value="kupalar" <?php echo $urun['kategori'] == 'kupalar' ? 'selected' : ''; ?>>
                                Kupalar</option>
                        </select>
                    </td>
                    <td>
                        <span
                            id="text_fiyat_<?php echo $urun['id']; ?>">₺<?php echo htmlspecialchars($urun['fiyat']); ?></span>
                        <input type="number" step="0.01" name="fiyatlar[<?php echo $urun['id']; ?>]"
                            id="input_fiyat_<?php echo $urun['id']; ?>" value="<?php echo $urun['fiyat']; ?>"
                            style="display:none; padding: 5px; width: 80px;">
                    </td>
                    <td>
                        <?php $durum = isset($urun['durum']) ? $urun['durum'] : 1; ?>
                        <div class="flex-form" style="align-items: center; gap: 8px;">
                            <input type="hidden" name="durumlar[<?php echo $urun['id']; ?>]"
                                id="durum_input_<?php echo $urun['id']; ?>" value="<?php echo $durum; ?>">
                            <!-- Aktif/Pasif Butonları -->
                            <a id="btn_aktif_<?php echo $urun['id']; ?>"
                                class="durum-btn <?php echo $durum == 1 ? 'durum-secili' : 'durum-gri'; ?>"
                                onclick="setDurum(<?php echo $urun['id']; ?>, 1)">Aktif</a>
                            <a id="btn_pasif_<?php echo $urun['id']; ?>"
                                class="durum-btn <?php echo $durum == 0 ? 'durum-secili' : 'durum-gri'; ?>"
                                onclick="setDurum(<?php echo $urun['id']; ?>, 0)">Pasif</a>

                            <!-- Güncelle Butonu -->
                            <a id="btn_guncelle_<?php echo $urun['id']; ?>" class="sil-btn"
                                style="background:#153523; border: 1px solid #c6a87c; margin-left: 10px; cursor:pointer;"
                                onclick="guncelleModu(<?php echo $urun['id']; ?>)">Güncelle</a>

                            <!-- Sil Butonu -->
                            <a href="?sayfa=menu&sil=<?php echo $urun['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                                class="sil-btn" onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?');"
                                style="margin-left: 10px;">Sil</a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </form>
    </div>

    <?php elseif ($sayfa == 'kurumsal'): ?>
    <div class="admin-nav">
        <a href="admin.php" class="geri-btn">&laquo; Panele Dön</a>
        <a href="logout.php" class="cikis-btn">Çıkış Yap</a>
    </div>
    <div class="admin-kutu admin-kutu-merkez" style="max-width: 900px;">
        <h2>Kurumsal Yönetimi</h2>
        <p>Lütfen düzenlemek istediğiniz bölümü seçin.</p>
        <div class="dashboard-grid">
            <a href="?sayfa=kurumsal_hakkimizda" class="dash-btn dash-btn-small">Hakkımızda</a>
            <a href="?sayfa=kurumsal_magazalar" class="dash-btn dash-btn-small">Mağazalarımız</a>
            <a href="?sayfa=kurumsal_kariyer" class="dash-btn dash-btn-small">Kariyer</a>
        </div>
    </div>

    <?php elseif ($sayfa == 'kurumsal_hakkimizda'): ?>
    <?php $hakkimizda = $db->query("SELECT hakkimizda_metin FROM ayarlar WHERE id = 1")->fetchColumn(); ?>
    <div class="admin-nav">
        <a href="admin.php?sayfa=kurumsal" class="geri-btn">&laquo; Kurumsal Panele Dön</a>
        <a href="logout.php" class="cikis-btn">Çıkış Yap</a>
    </div>
    <div class="admin-kutu">
        <h2>Hakkımızda Yazısını Düzenle</h2>
        <?php if (isset($_GET['durum']) && $_GET['durum'] == 'ok'): ?>
        <p class="uyari-mesaji uyari-basari">Yazı başarıyla güncellendi!</p>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <label>Hakkımızda Metni (Maksimum 800 Karakter):</label>
            <textarea name="hakkimizda_metin" rows="12" maxlength="800" required
                class="hakkimizda-textarea"><?php echo htmlspecialchars($hakkimizda); ?></textarea>
            <button type="submit" name="hakkimizda_guncelle" class="btn-gold">Yazıyı Kaydet</button>
        </form>
    </div>

    <?php elseif ($sayfa == 'kurumsal_kariyer'): ?>
    <?php $kariyer_url = $db->query("SELECT kariyer_url FROM ayarlar WHERE id = 1")->fetchColumn(); ?>
    <div class="admin-nav">
        <a href="admin.php?sayfa=kurumsal" class="geri-btn">&laquo; Kurumsal Panele Dön</a>
        <a href="logout.php" class="cikis-btn">Çıkış Yap</a>
    </div>
    <div class="admin-kutu">
        <h2>Kariyer Sayfası Yönlendirme Ayarları</h2>
        <?php if (isset($_GET['durum']) && $_GET['durum'] == 'ok'): ?>
        <p class="uyari-mesaji uyari-basari">✅ Kariyer URL bağlantısı başarıyla güncellendi!</p>
        <?php endif; ?>
        <p>Kullanıcılar sitenizde "Kariyer" veya iş başvurusu butonlarına tıkladıklarında yönlendirilecekleri dış bağlantıyı (Örn: Kariyer.net, LinkedIn vb.) giriniz.</p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <label style="color: #c6a87c; font-weight: bold;">Yönlendirilecek Kariyer Adresi (URL):</label>
            <input type="url" name="kariyer_url" value="<?php echo htmlspecialchars($kariyer_url); ?>" placeholder="https://www.kariyer.net/..." required>
            <button type="submit" name="kariyer_guncelle" class="btn-gold" style="margin-top: 15px;">URL'yi Kaydet</button>
        </form>
    </div>

    <?php elseif ($sayfa == 'kurumsal_magazalar'): ?>
    <div class="admin-nav">
        <a href="admin.php?sayfa=kurumsal" class="geri-btn">&laquo; Kurumsal Panele Dön</a>
        <a href="logout.php" class="cikis-btn">Çıkış Yap</a>
    </div>
    <!-- TEK BİR KABUK VE İÇİNDE 3 KARE ALAN -->
    <div class="admin-kutu" style="max-width: 1300px;">
        <h2>Kurumsal Tüm Mağazalar Yönetimi</h2>
        <?php if (isset($_GET['durum'])): ?>
        <?php if ($_GET['durum'] == 'bos_alan'): ?>
        <p class="uyari-mesaji uyari-hata">⚠️ Hata: Lütfen il ve ilçe alanlarını boş bırakmayınız!</p>
        <?php elseif ($_GET['durum'] == 'ok'): ?>
        <p class="uyari-mesaji uyari-basari">✅ Yeni şube başarıyla sisteme eklendi.</p>
        <?php elseif ($_GET['durum'] == 'gecersiz_id'): ?>
        <p class="uyari-mesaji uyari-hata">⚠️ Hata: Geçersiz şube kimliği!</p>
        <?php endif; ?>
        <?php endif; ?>

        <div class="flex-container">

            <!-- KARE 1: TEKLİ EKLE -->
            <div class="flex-item" style="min-width: 280px;">
                <h3>Tekli Şube Ekle</h3>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <label style="color: #c6a87c; font-size:13px; font-weight:bold;">İl (Şehir):</label>
                    <input type="text" name="il" placeholder="Örn: İstanbul" required style="margin-bottom: 10px; width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #c6a87c; background: rgba(255,255,255,0.05); color: white;">
                    
                    <label style="color: #c6a87c; font-size:13px; font-weight:bold;">İlçe:</label>
                    <input type="text" name="ilce" placeholder="Örn: Kadıköy" required style="margin-bottom: 15px; width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #c6a87c; background: rgba(255,255,255,0.05); color: white;">
                    <button type="submit" name="sube_ekle"
                        onclick="return confirm('Bu yeni mağazayı şubeler arasına eklemek istediğinize emin misiniz?');">Sisteme
                        Ekle</button>
                </form>
            </div>

            <!-- KARE 2: TOPLU YÜKLE -->
            <div class="flex-item" style="min-width: 280px;">
                <h3>Toplu Yükle (JSON)</h3>
                <p style="font-size: 13px; margin-bottom: 15px;">Örnek: <code>[{"il":"Ankara", "ilce":"Çankaya"}]</code>
                </p>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="file" name="json_dosya" accept=".json" required>
                    <button type="submit" name="toplu_sube_ekle" class="btn-gold"
                        onclick="return confirm('Toplu şube dosyasını (JSON) yüklemek istediğinize emin misiniz?');">
                        Toplu Şube Yükle
                    </button>
                </form>
            </div>

            <!-- KARE 3: LİSTE -->
            <div class="flex-item store-list-container">
                <h3>Mevcut Kurumsal Şubeler</h3>
                <input type="text" id="storeSearch" placeholder="Şubelerde Ara..." onkeyup="filterStores()">
                <table id="storeTable">
                    <tr>
                        <th>İl / İlçe</th>
                        <th>İşlem</th>
                    </tr>
                    <?php
                        $sorgu = $db->query("SELECT * FROM subeler ORDER BY id DESC");
                        while ($sube = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                    <tr class="store-row">
                        <td><?php echo htmlspecialchars($sube['il'] . ' - ' . $sube['ilce']); ?></td>
                        <td><a href="?sayfa=kurumsal_magazalar&sube_sil=<?php echo $sube['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                                class="sil-btn"
                                onclick="return confirm('Bu mağazayı tamamen silmek istediğinize emin misiniz?');">Sil</a>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>

    <?php elseif ($sayfa == 'iletisim'): ?>
    <div class="admin-nav">
        <a href="admin.php" class="geri-btn">&laquo; Panele Dön</a>
        <a href="logout.php" class="cikis-btn">Çıkış Yap</a>
    </div>
    <div class="admin-kutu admin-kutu-merkez" style="max-width: 900px;">
        <h2>İletişim Yönetimi</h2>
        <p>Lütfen işlem yapmak istediğiniz alanı seçin.</p>
        <div class="dashboard-grid">
            <a href="?sayfa=iletisim_sosyal" class="dash-btn dash-btn-medium">Sosyal Medya Bilgi Düzenleme</a>
            <a href="?sayfa=iletisim_mesajlar" class="dash-btn dash-btn-medium">Öneri ve Görüş Formları</a>
        </div>
    </div>

    <?php elseif ($sayfa == 'iletisim_sosyal'): ?>
    <div class="admin-nav">
        <a href="admin.php?sayfa=iletisim" class="geri-btn">&laquo; İletişim Panele Dön</a>
        <a href="logout.php" class="cikis-btn">Çıkış Yap</a>
    </div>
    <div class="admin-kutu">
        <h2>Sosyal Medya Adresleri Yönetimi</h2>
        <p style="margin-bottom: 20px;">Sitede görünmesini istediğiniz platformu seçip URL'sini (bağlantısını) girin.
            İkonlar otomatik olarak eklenecektir.</p>

        <form method="POST" style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <label style="color: #c6a87c;">Platform (İkon) Seçin:</label>
            <select name="platform" required>
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

            <button type="submit" name="sosyal_ekle">Listeye Ekle</button>
        </form>

        <table style="margin-top: 30px;">
            <tr>
                <th>Platform</th>
                <th>URL Bağlantısı</th>
                <th width="10%">İşlem</th>
            </tr>
            <?php
                $sosyal_sorgu = $db->query("SELECT * FROM sosyal_medya ORDER BY id DESC");
                while ($sosyal = $sosyal_sorgu->fetch(PDO::FETCH_ASSOC)) {
                    ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($sosyal['platform']); ?></strong></td>
                <td><a href="<?php echo htmlspecialchars($sosyal['url']); ?>"
                        target="_blank"><?php echo htmlspecialchars($sosyal['url']); ?></a></td>
                <td><a href="?sayfa=iletisim_sosyal&sosyal_sil=<?php echo $sosyal['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                        class="sil-btn" onclick="return confirm('Silmek istediğinize emin misiniz?');">Sil</a></td>
            </tr>
            <?php } ?>
        </table>
    </div>

    <?php elseif ($sayfa == 'iletisim_mesajlar'): ?>
    <div class="admin-nav">
        <a href="admin.php?sayfa=iletisim" class="geri-btn">&laquo; İletişim Panele Dön</a>
        <a href="logout.php" class="cikis-btn">Çıkış Yap</a>
    </div>
    <div class="admin-kutu" style="max-width: 1200px;">
        <?php
            // Puan Ortalamasını Hesapla
            $puan_sorgu = $db->query("SELECT AVG(yildiz) as ortalama, COUNT(id) as kisi FROM iletisim_mesajlari WHERE yildiz > 0")->fetch(PDO::FETCH_ASSOC);
            $ortalama = round($puan_sorgu['ortalama'], 1);
            $kisi = $puan_sorgu['kisi'];
            ?>
        <div class="admin-nav" style="max-width: none; margin-bottom: 20px;">
            <h2>Gelen Öneri ve Talepler</h2>
            <?php if ($kisi > 0): ?>
            <div class="puan-ortalama-kutu">
                <div class="puan-yildizlar">
                    <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= floor($ortalama))
                                    echo "<i class='fas fa-star'></i>";
                                elseif ($i == ceil($ortalama) && $ortalama > floor($ortalama))
                                    echo "<i class='fas fa-star-half-alt'></i>";
                                else
                                    echo "<i class='far fa-star'></i>";
                            }
                            ?>
                </div>
                <span class="puan-rakam"><?php echo $ortalama; ?> / 5</span>
                <span class="puan-kisi">(<?php echo $kisi; ?> Kişi Puan Verdi)</span>
            </div>
            <?php endif; ?>
        </div>

        <div class="flex-container">
            <!-- SOL: MENÜ TALEPLERİ -->
            <div class="flex-item">
                <h3>Menü Talepleri</h3>
                <?php
                    $menu_sorgu = $db->query("SELECT * FROM iletisim_mesajlari WHERE talep_turu = 'Menü' ORDER BY id DESC");
                    $menu_mesajlar = $menu_sorgu->fetchAll(PDO::FETCH_ASSOC);
                    if (count($menu_mesajlar) > 0) {
                        foreach ($menu_mesajlar as $mesaj) {
                            $güvenli_isim = htmlspecialchars($mesaj['ad_soyad']);
                            $güvenli_email = htmlspecialchars($mesaj['email']);
                            $güvenli_mesaj = nl2br(htmlspecialchars($mesaj['mesaj']));
                            $güvenli_il = htmlspecialchars($mesaj['il']);
                            $güvenli_ilce = htmlspecialchars($mesaj['ilce']);

                            echo "<div class='mesaj-kutusu mesaj-menu'>";
                            echo "<div class='mesaj-header'><strong>{$güvenli_isim}</strong><span>" . date('d.m.Y H:i', strtotime($mesaj['tarih'])) . "</span></div>";
                            echo "<div class='mesaj-meta'><i class='fas fa-envelope'></i> {$güvenli_email}</div>";
                            echo "<div class='mesaj-puan'>";
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $mesaj['yildiz'] ? "<i class='fas fa-star'></i>" : "<i class='far fa-star'></i>";
                            }
                            echo "</div>";
                            echo "<p class='mesaj-icerik'>{$güvenli_mesaj}</p>";
                            echo "<div class='mesaj-footer'>";
                            echo "  <a href='?sayfa=iletisim_mesajlar&mesaj_sil={$mesaj['id']}&csrf_token={$csrf_token}' class='sil-btn' onclick='return confirm(\"Bu mesajı silmek istediğinize emin misiniz?\");'>Mesajı Sil</a>";
                            if (!empty($güvenli_il)) {
                                echo "  <span class='mesaj-konum'><i class='fas fa-map-marker-alt'></i> {$güvenli_il} / {$güvenli_ilce}</span>";
                            }
                            echo "</div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p style='color:#aaa; font-size:14px;'>Henüz menü talebi bulunmuyor.</p>";
                    }
                    ?>
            </div>

            <div class="flex-item">
                <h3>Hizmet Talepleri</h3>
                <?php
                    $hizmet_sorgu = $db->query("SELECT * FROM iletisim_mesajlari WHERE talep_turu = 'Hizmet' ORDER BY id DESC");
                    $hizmet_mesajlar = $hizmet_sorgu->fetchAll(PDO::FETCH_ASSOC);
                    if (count($hizmet_mesajlar) > 0) {
                        foreach ($hizmet_mesajlar as $mesaj) {
                            $güvenli_isim = htmlspecialchars($mesaj['ad_soyad']);
                            $güvenli_email = htmlspecialchars($mesaj['email']);
                            $güvenli_mesaj = nl2br(htmlspecialchars($mesaj['mesaj']));
                            $güvenli_il = htmlspecialchars($mesaj['il']);
                            $güvenli_ilce = htmlspecialchars($mesaj['ilce']);

                            echo "<div class='mesaj-kutusu mesaj-hizmet'>";
                            echo "<div class='mesaj-header'><strong>{$güvenli_isim}</strong><span>" . date('d.m.Y H:i', strtotime($mesaj['tarih'])) . "</span></div>";
                            echo "<div class='mesaj-meta'><i class='fas fa-envelope'></i> {$güvenli_email}</div>";
                            echo "<div class='mesaj-puan'>";
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $mesaj['yildiz'] ? "<i class='fas fa-star'></i>" : "<i class='far fa-star'></i>";
                            }
                            echo "</div>";
                            echo "<p class='mesaj-icerik'>{$güvenli_mesaj}</p>";
                            echo "<div class='mesaj-footer'>";
                            echo "  <a href='?sayfa=iletisim_mesajlar&mesaj_sil={$mesaj['id']}&csrf_token={$csrf_token}' class='sil-btn' onclick='return confirm(\"Bu mesajı silmek istediğinize emin misiniz?\");'>Mesajı Sil</a>";
                            if (!empty($güvenli_il)) {
                                echo "  <span class='mesaj-konum'><i class='fas fa-map-marker-alt'></i> {$güvenli_il} / {$güvenli_ilce}</span>";
                            }
                            echo "</div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>Henüz hizmet talebi bulunmuyor.</p>";
                    }
                    ?>
            </div>
        </div>
        <?php $mevcut_max_id = $db->query("SELECT MAX(id) FROM iletisim_mesajlari")->fetchColumn() ?: 0; ?>
        <input type="hidden" id="current-max-id" value="<?php echo $mevcut_max_id; ?>">
    </div>
    <?php endif; ?>

    <script src="admin.js?v=<?php echo time(); ?>"></script>
</body>

</html>