<?php include 'header.php'; ?>

<section class="video-hero-section">
    <div class="video-box">
        <!-- video link buraya gir -->
        <iframe width="100%" height="100%" src="https://www.youtube.com/embed/vzct5CUBy3w?autoplay=1&mute=1&loop=1&playlist=vzct5CUBy3w&controls=0&showinfo=0&rel=0&playsinline=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
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
        <?php if($kisi_sayisi > 0): ?>
        <div class="average-rating" title="Müşteri Puanımız">
            <div class="stars">
                <?php
                for($i=1; $i<=5; $i++) {
                    if($i <= floor($ortalama)) echo "<i class='fas fa-star'></i>"; // Tam Dolu Yıldız
                    elseif($i == ceil($ortalama) && $ortalama > floor($ortalama)) echo "<i class='fas fa-star-half-alt'></i>"; // Yarım Yıldız (Örn: 4.5 için)
                    else echo "<i class='far fa-star empty'></i>"; // Boş Yıldız
                }
                ?>
            </div>
            <span><?php echo $ortalama; ?> / 5 (<?php echo $kisi_sayisi; ?> Yorum)</span>
        </div>
        <?php endif; ?>
    </div>
    <div class="quality-container">
        <div class="quality-col left">
            <div class="q-item"><h3>Rahat Hissederim</h3><p>İçtikçe rahatlatan lezzetler...</p></div>
            <div class="q-item"><h3>Her Gün Yenilen</h3><p>Güne Colombia lezzetleri ile başla...</p></div>
            <div class="q-item"><h3>Taze Ürün</h3><p>En kaliteli çekirdekleri saklıyoruz...</p></div>
        </div>
        <div class="quality-center">
            <img src="anasayfa_kahvebardağı.png" alt="Colombia Cup"> </div>
        <div class="quality-col right">
            <div class="q-item"><h3>Önce Kalite</h3><p>Portföyümüzü sürekli geliştiriyoruz...</p></div>
            <div class="q-item"><h3>Güne Erken Başla</h3><p>Yeni güne eşlik ediyoruz...</p></div>
            <div class="q-item"><h3>Dostlarımıza Yer Açın</h3><p>Yaşamı dostlarımızla paylaşıyoruz...</p></div>
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
                    if (!file_exists($resim_yolu)) $resim_yolu = 'store-placeholder.jpg';
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

<?php include 'footer.php'; ?>