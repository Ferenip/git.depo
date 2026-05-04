<?php 
include 'header.php'; 
include 'baglan.php'; 

// Hangi sekmede olduğumuzu kontrol edelim (Varsayılan: magazalar)
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'magazalar';

$baslik = ($tab == 'hakkimizda') ? 'Hakkımızda' : 'Mağazalarımız';
$alt_baslik = ($tab == 'hakkimizda') ? 'Bizi Daha Yakından Tanıyın' : 'Size En Yakın Şubelerimiz';
?>

<section class="kurumsal-hero-section">
    <div style="text-align: center; width: 100%; padding: 40px 0;">
        <h1 style="font-size: 3.5rem; color: var(--gold);"><?php echo $baslik; ?></h1>
        <p style="color: var(--text-gray); font-size: 1.1rem; margin-top: 10px;"><?php echo $alt_baslik; ?></p>
    </div>
</section>

<div class="kurumsal-container">
    <?php if ($tab == 'magazalar'): ?>
        <?php
        // Sayfalama (Pagination) Ayarları
        $limit = 8; // Her sayfada gösterilecek mağaza sayısı
        $sayfa = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($sayfa < 1) $sayfa = 1;

        $baslangic = ($sayfa - 1) * $limit;

        // Toplam mağaza sayısını bul
        $toplam_kayit = $db->query("SELECT COUNT(*) FROM subeler")->fetchColumn();
        $toplam_sayfa = ceil($toplam_kayit / $limit);

        // Sayfaya ait mağazaları çek
        $subeler = $db->query("SELECT * FROM subeler ORDER BY id DESC LIMIT $baslangic, $limit")->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <div class="stores-grid">
            <?php if (count($subeler) > 0): ?>
                <?php foreach ($subeler as $sube): ?>
                    <div class="store-card">
                        <img src="icon.png" alt="Colombia Coffee Şube İkonu" class="store-icon">
                        <h3 class="store-city"><?php echo $sube['il']; ?></h3>
                        <p class="store-district"><?php echo $sube['ilce']; ?> Şubesi</p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; font-size: 1.2rem; color: var(--text-gray);">Henüz eklenmiş bir mağaza bulunmamaktadır.</p>
            <?php endif; ?>
        </div>

        <!-- Sayfa Numaraları -->
        <?php if ($toplam_sayfa > 1): ?>
            <div class="pagination">
                <?php if ($sayfa > 1): ?>
                    <a href="?tab=magazalar&page=<?php echo $sayfa - 1; ?>" class="page-btn">&laquo; Önceki</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $toplam_sayfa; $i++): ?>
                    <a href="?tab=magazalar&page=<?php echo $i; ?>" class="page-btn <?php echo ($i == $sayfa) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($sayfa < $toplam_sayfa): ?>
                    <a href="?tab=magazalar&page=<?php echo $sayfa + 1; ?>" class="page-btn">Sonraki &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php elseif ($tab == 'hakkimizda'): ?>
        <?php
        // Yazıyı veritabanından çekiyoruz
        $hakkimizda_metni = $db->query("SELECT hakkimizda_metin FROM ayarlar WHERE id = 1")->fetchColumn();
        ?>
        <div class="hakkimizda-wrapper">
            <div class="paper-container">
                <img src="kurabiye.png" alt="Kurabiye" class="cookie-corner">
                <h2 class="paper-title">Hakkımızda</h2>
                <p class="paper-text"><?php echo nl2br(htmlspecialchars($hakkimizda_metni)); ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>