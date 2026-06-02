<?php 
/**
 * ANA SAYFA
 * Dinamik verilerin vitrinlendiği ana karşılama sayfasıdır.
 */
include 'header.php'; ?>

<?php
// Veritabanından Admin'in girdiği URL'yi çek
$ayar_sorgu = $db->query("SELECT * FROM ayarlar WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$video_url = $ayar_sorgu['video_url'];

// Resim veritabanında boşsa veya silinmişse sitenin kırılmasını engellemek için varsayılan (fallback) resimler atanır
$k_merkez = !empty($ayar_sorgu['kalite_merkez']) ? $ayar_sorgu['kalite_merkez'] : 'anasayfa_kahvebardağı.png';
$k_1 = !empty($ayar_sorgu['kalite_1']) ? $ayar_sorgu['kalite_1'] : 'rahat_hissederim.jpg';
$k_2 = !empty($ayar_sorgu['kalite_2']) ? $ayar_sorgu['kalite_2'] : 'her_gun_yenilen.jpg';
$k_3 = !empty($ayar_sorgu['kalite_3']) ? $ayar_sorgu['kalite_3'] : 'taze_urun.jpg';
$k_4 = !empty($ayar_sorgu['kalite_4']) ? $ayar_sorgu['kalite_4'] : 'once_kalite.jpg';
$k_5 = !empty($ayar_sorgu['kalite_5']) ? $ayar_sorgu['kalite_5'] : 'gune_erken_basla.jpg';
$k_6 = !empty($ayar_sorgu['kalite_6']) ? $ayar_sorgu['kalite_6'] : 'dostlara_yer_acin.jpg';
?>

<style>
    /* Ortadaki resmin "yapıştırma" hissini kırmak için 3 boyutlu gölge ve uçma efekti */
    #qualityCenterImg {
        /* İlk drop-shadow derinliği, ikinci drop-shadow arka plandaki altın rengi hafif parlamayı sağlar */
        filter: drop-shadow(0 30px 20px rgba(0, 0, 0, 0.7)) drop-shadow(0 0 30px rgba(198, 168, 124, 0.2));
    }
    @keyframes floatEffect {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
    .quality-center {
        animation: floatEffect 5s ease-in-out infinite; /* Resmi yukarı aşağı hafifçe dalgalandırır */
    }
</style>

<section class="video-hero-section">
    <div class="video-box">
        <?php if (file_exists('uploads/home_video.mp4')): ?>
            <!-- Yüklenen yerel video -->
            <video autoplay loop muted playsinline
                style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 0;">
                <source src="uploads/home_video.mp4?v=<?php echo time(); ?>" type="video/mp4">
            </video>
        <?php elseif (!empty($video_url)): ?>
            <!-- Yüklü video yoksa Admin panelinden girilen URL videosu -->
            <iframe width="100%" height="100%" src="<?php echo $video_url; ?>" frameborder="0"
                allow="autoplay; encrypted-media" allowfullscreen></iframe>
        <?php endif; ?>
        <div class="video-overlay"></div>
    </div>
    <div class="video-text">
        <h2>Yüksek kalite mükemmel hizmet</h2>
    </div>
</section>

<?php
// Otomatik Puan Ortalama Hesaplaması
$puan_sorgu = $db->query("SELECT AVG(yildiz) as ortalama, COUNT(id) as kisi FROM iletisim_mesajlari WHERE yildiz > 0")->fetch(PDO::FETCH_ASSOC);
$ortalama = round($puan_sorgu['ortalama'], 1);
$kisi_sayisi = $puan_sorgu['kisi'];
?>

<section class="quality-section">
    <div class="section-title">
        <span>Colombia Coffee</span>
        <h2>Kaliteli Kahve</h2>
        <?php if ($kisi_sayisi > 0): ?>
            <div class="average-rating" title="Müşteri Puanımız">
                <div class="stars">
                    <?php
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= floor($ortalama))
                            echo "<i class='fas fa-star'></i>"; // Tam Dolu Yıldız
                        elseif ($i == ceil($ortalama) && $ortalama > floor($ortalama))
                            echo "<i class='fas fa-star-half-alt'></i>"; // Yarım Yıldız (Örn: 4.5 için)
                        else
                            echo "<i class='far fa-star empty'></i>"; // Boş Yıldız
                    }
                    ?>
                </div>
                <span><?php echo $ortalama; ?> / 5 (<?php echo $kisi_sayisi; ?> Yorum)</span>
            </div>
        <?php endif; ?>
    </div>
    <div class="quality-container">
        <div class="quality-col left">
            <div class="q-item" onmouseenter="changeQualityImage('<?php echo htmlspecialchars($k_1); ?>')" onmouseleave="resetQualityImage()">
                <h3>Rahat Hissederim</h3>
                <p>İçtikçe rahatlatan lezzetler...</p>
            </div>
            <div class="q-item" onmouseenter="changeQualityImage('<?php echo htmlspecialchars($k_2); ?>')" onmouseleave="resetQualityImage()">
                <h3>Her Gün Yenilen</h3>
                <p>Güne Colombia lezzetleri ile başla...</p>
            </div>
            <div class="q-item" onmouseenter="changeQualityImage('<?php echo htmlspecialchars($k_3); ?>')" onmouseleave="resetQualityImage()">
                <h3>Taze Ürün</h3>
                <p>En kaliteli çekirdekleri saklıyoruz...</p>
            </div>
        </div>
        <div class="quality-center">
            <img src="<?php echo htmlspecialchars($k_merkez); ?>" alt="Colombia Cup" id="qualityCenterImg" data-default="<?php echo htmlspecialchars($k_merkez); ?>">
        </div>
        <div class="quality-col right">
            <div class="q-item" onmouseenter="changeQualityImage('<?php echo htmlspecialchars($k_4); ?>')" onmouseleave="resetQualityImage()">
                <h3>Önce Kalite</h3>
                <p>Portföyümüzü sürekli geliştiriyoruz...</p>
            </div>
            <div class="q-item" onmouseenter="changeQualityImage('<?php echo htmlspecialchars($k_5); ?>')" onmouseleave="resetQualityImage()">
                <h3>Güne Erken Başla</h3>
                <p>Yeni güne eşlik ediyoruz...</p>
            </div>
            <div class="q-item" onmouseenter="changeQualityImage('<?php echo htmlspecialchars($k_6); ?>')" onmouseleave="resetQualityImage()">
                <h3>Dostlarımıza Yer Açın</h3>
                <p>Yaşamı dostlarımızla paylaşıyoruz...</p>
            </div>
        </div>
    </div>
</section>

<?php
// Veritabanından Dinamik Şubeleri Çekme
$sube_sorgu = $db->query("SELECT * FROM anasayfa_subeler ORDER BY id ASC");
$subeler = $sube_sorgu->fetchAll(PDO::FETCH_ASSOC);

$ilk_sube_adi = ($subeler[0]['il'] == 'Yakında') ? 'Yakında - Hizmetinizdeyiz' : $subeler[0]['il'] . ' - ' . $subeler[0]['ilce'] . ' Şubesi';
?>
<section class="store-gallery">
    <div class="gallery-wrapper">
        <button class="nav-btn prev" onclick="moveGallery(-1)">❮</button>
        <div class="gallery-track" id="galleryTrack">
            <div class="gallery-slider" id="gallerySlider">
                <?php foreach ($subeler as $index => $sube): ?>
                    <?php
                    $resim_yolu = (isset($sube['id']) && !empty($sube['resim'])) ? 'uploads/subeler/' . $sube['resim'] : 'store-placeholder.jpg';
                    if (!file_exists($resim_yolu))
                        $resim_yolu = 'store-placeholder.jpg';
                    $sube_adi = ($sube['il'] == 'Yakında') ? 'Yakında - Hizmetinizdeyiz' : $sube['il'] . ' - ' . $sube['ilce'] . ' Şubesi';
                    ?>
                    <div class="store-slide">
                        <div class="store-label-inline"><?php echo $sube_adi; ?></div>
                        <img src="<?php echo $resim_yolu; ?>" class="store-img">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="nav-btn next" onclick="moveGallery(1)">❯</button>
    </div>
</section>

<section class="promo-banner">
    <div class="promo-content">
        <h2>Mükemmel lezzet, keyifli bir tat!</h2>
        <button class="promo-btn" onclick="window.location.href='menu.php'">Tüm Ürünler</button>
    </div>
</section>

<?php include 'colombot.php'; ?>
<?php include 'footer.php'; ?>