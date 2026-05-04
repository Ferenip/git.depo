<?php include 'header.php'; ?>

    <section class="hero-section">
        <div class="hero-text">
            <h1>Ürünler</h1>
            <p>Colombia Coffee -- Dinamik Menü --</p>
            
            <div class="slider-indicators">
                <div class="indicator active" onclick="goToSlide(0)"><span>01</span><div class="line"></div></div>
                <div class="indicator" onclick="goToSlide(1)"><span>02</span><div class="line"></div></div>
                <div class="indicator" onclick="goToSlide(2)"><span>03</span><div class="line"></div></div>
                <div class="indicator" onclick="goToSlide(3)"><span>04</span><div class="line"></div></div>
            </div>
        </div>

       <div class="slider-container">
            <div class="slider-track" id="sliderTrack">
                <?php
                $slider_sorgu = $db->query("SELECT * FROM slider ORDER BY id ASC LIMIT 4");
                while ($slider = $slider_sorgu->fetch(PDO::FETCH_ASSOC)) {
                    $bg_image = file_exists('uploads/'.$slider['resim']) ? 'uploads/'.$slider['resim'] : $slider['resim'];
                   echo '<div class="slide" style="background-image: url(\''.$bg_image.'\'); background-size: contain; background-repeat: no-repeat; background-position: center; background-color: #153523;"></div>';
                }
                ?>
            </div>
        </div>
    </section>

    <div class="toggle-section">
        <button id="btnKahveler" class="toggle-btn btn-active" onclick="switchTab('kahveler')">KAHVELER</button>
        <button id="btnKupalar" class="toggle-btn btn-inactive" onclick="switchTab('kupalar')">KUPALAR</button>
    </div>

    <section class="products-grid" id="kahveler-grid">
        <?php
        $kahve_sorgu = $db->prepare("SELECT * FROM urunler WHERE kategori = 'kahveler' ORDER BY id DESC");
        $kahve_sorgu->execute();
        $kahveler = $kahve_sorgu->fetchAll(PDO::FETCH_ASSOC);

        foreach ($kahveler as $urun) {
            $resim_yolu = !empty($urun['resim']) ? 'uploads/' . $urun['resim'] : 'frappe.png';
        ?>
            <div class="product-card">
                <img src="<?php echo $resim_yolu; ?>" alt="<?php echo $urun['urun_adi']; ?>" class="product-img">
                <h3 class="product-title"><?php echo $urun['urun_adi']; ?></h3>
                <div class="product-price">₺<?php echo $urun['fiyat']; ?></div>
            </div>
        <?php } ?>
    </section>

   <section class="products-grid" id="kupalar-grid" style="display:none;">
    <?php
    // Değişken ismini netleştirelim ve sorgunun çalıştığından emin olalım
    $kupa_sorgu = $db->prepare("SELECT * FROM urunler WHERE kategori = 'kupalar' ORDER BY id DESC");
    $kupa_sorgu->execute();
    $kupa_listesi = $kupa_sorgu->fetchAll(PDO::FETCH_ASSOC);

    if (count($kupa_listesi) > 0) {
        foreach ($kupa_listesi as $kupa_urun) {
            $resim_yolu = !empty($kupa_urun['resim']) ? 'uploads/' . $kupa_urun['resim'] : 'kupa1.png';
            ?>
            <div class="product-card">
                <img src="<?php echo $resim_yolu; ?>" alt="<?php echo $kupa_urun['urun_adi']; ?>" class="product-img">
                <h3 class="product-title"><?php echo $kupa_urun['urun_adi']; ?></h3>
                <div class="product-price">₺<?php echo $kupa_urun['fiyat']; ?></div>
            </div>
            <?php 
        }
    } else {
        echo "<p>Bu kategoride ürün bulunamadı.</p>";
    }
    ?>
</section>

<?php include 'footer.php'; ?>