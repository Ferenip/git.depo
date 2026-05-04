<?php 
include 'header.php'; 
include 'baglan.php'; 

// --- VERİTABANINA MESAJ KAYDETME İŞLEMİ ---
$mesaj_durum = "";
if (isset($_POST['mesaj_gonder'])) {
    $ad_soyad = htmlspecialchars($_POST['ad_soyad']);
    $email = htmlspecialchars($_POST['email']);
    $talep_turu = htmlspecialchars($_POST['talep_turu']);
    $mesaj = htmlspecialchars($_POST['mesaj']);
    $yildiz = isset($_POST['yildiz']) ? (int)$_POST['yildiz'] : 5; // Seçilmezse varsayılan 5
    $il = isset($_POST['il']) ? htmlspecialchars($_POST['il']) : '';
    $ilce = isset($_POST['ilce']) ? htmlspecialchars($_POST['ilce']) : '';
    
    $sorgu = $db->prepare("INSERT INTO iletisim_mesajlari (ad_soyad, email, talep_turu, mesaj, yildiz, il, ilce) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($sorgu->execute([$ad_soyad, $email, $talep_turu, $mesaj, $yildiz, $il, $ilce])) {
        $mesaj_durum = "<p style='color: lime; font-weight: bold; margin-bottom: 20px; font-size: 14px;'>Mesajınız başarıyla iletildi, teşekkür ederiz!</p>";
    } else {
        $mesaj_durum = "<p style='color: red; font-weight: bold; margin-bottom: 20px; font-size: 14px;'>Mesajınız gönderilirken bir hata oluştu.</p>";
    }
}
?>

<section class="kurumsal-hero-section">
    <div style="text-align: center; width: 100%; padding: 40px 0;">
        <h1 style="font-size: 3.5rem; color: var(--gold);">İletişim</h1>
        <p style="color: var(--text-gray); font-size: 1.1rem; margin-top: 10px;">Bize Ulaşın ve Bizi Takip Edin</p>
    </div>
</section>

<div class="kurumsal-container" style="min-height: 45vh;">
    <div class="contact-wrapper">
    <?php
    $sosyal_sorgu = $db->query("SELECT * FROM sosyal_medya ORDER BY id ASC");
    $sosyal_hesaplar = $sosyal_sorgu->fetchAll(PDO::FETCH_ASSOC);

    // Panelden en az 1 tane hesap eklenmişse kutuyu göster
    if (count($sosyal_hesaplar) > 0): 
    ?>
        <div class="contact-box">
            <h2>Bizi Takip Edin</h2>
            <div class="social-links-container">
                <?php
                foreach ($sosyal_hesaplar as $hesap) {
                    // Platform adına göre uygun FontAwesome ikonunu belirle
                    $icon = 'fas fa-link'; 
                    if ($hesap['platform'] == 'Instagram') $icon = 'fab fa-instagram';
                    if ($hesap['platform'] == 'Facebook') $icon = 'fab fa-facebook';
                    if ($hesap['platform'] == 'Twitter') $icon = 'fab fa-twitter'; // veya fa-x-twitter
                    if ($hesap['platform'] == 'YouTube') $icon = 'fab fa-youtube';
                    if ($hesap['platform'] == 'LinkedIn') $icon = 'fab fa-linkedin';
                    if ($hesap['platform'] == 'TikTok') $icon = 'fab fa-tiktok';
                    if ($hesap['platform'] == 'WhatsApp') $icon = 'fab fa-whatsapp';

                    // Ekranda çirkin durmaması için uzun url'nin baş kısımlarını temizle
                    $kisa_yazi = str_replace(['https://', 'http://', 'www.'], '', $hesap['url']);
                    $kisa_yazi = rtrim($kisa_yazi, '/'); // sondaki slash'ı da atar
                ?>
                <a href="<?php echo htmlspecialchars($hesap['url']); ?>" target="_blank" class="social-item">
                    <i class="<?php echo $icon; ?>"></i>
                    <span class="social-text"><?php echo htmlspecialchars($kisa_yazi); ?></span>
                </a>
                <?php } ?>
            </div>
        </div>
    <?php endif; // Hesap yoksa buradaki HTML bloğu komple yok olur ve sayfa boş kalır ?>

        <!-- SAĞ TARAF: İLETİŞİM / ÖNERİ FORMU -->
        <div class="contact-form-box">
            <h2>Öneri ve Talepleriniz</h2>
            <?php echo $mesaj_durum; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Adınız Soyadınız</label>
                    <input type="text" name="ad_soyad" required placeholder="Adınız Soyadınız">
                </div>
                <div class="form-group">
                    <label>E-Posta Adresiniz</label>
                    <input type="email" name="email" required placeholder="ornek@mail.com">
                </div>
                <div class="form-group">
                    <label>Talep Türü</label>
                    <select name="talep_turu" required>
                        <option value="Menü">Menü Önerisi/Şikayet/Teşekkür</option>
                        <option value="Hizmet">Hizmet/Şikayet/Teşekkür</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Şube İli</label>
                    <select name="il" id="formIl" required onchange="populateFormIlce()">
                        <option value="">İl Seçiniz...</option>
                        <?php 
                        $iller = ["Adana", "Adıyaman", "Afyonkarahisar", "Ağrı", "Amasya", "Ankara", "Antalya", "Artvin", "Aydın", "Balıkesir", "Bilecik", "Bingöl", "Bitlis", "Bolu", "Burdur", "Bursa", "Çanakkale", "Çankırı", "Çorum", "Denizli", "Diyarbakır", "Edirne", "Elazığ", "Erzincan", "Erzurum", "Eskişehir", "Gaziantep", "Giresun", "Gümüşhane", "Hakkari", "Hatay", "Isparta", "Mersin", "İstanbul", "İzmir", "Kars", "Kastamonu", "Kayseri", "Kırklareli", "Kırşehir", "Kocaeli", "Konya", "Kütahya", "Malatya", "Manisa", "Kahramanmaraş", "Mardin", "Muğla", "Muş", "Nevşehir", "Niğde", "Ordu", "Rize", "Sakarya", "Samsun", "Siirt", "Sinop", "Sivas", "Tekirdağ", "Tokat", "Trabzon", "Tunceli", "Şanlıurfa", "Uşak", "Van", "Yozgat", "Zonguldak", "Aksaray", "Bayburt", "Karaman", "Kırıkkale", "Batman", "Şırnak", "Bartın", "Ardahan", "Iğdır", "Yalova", "Karabük", "Kilis", "Osmaniye", "Düzce"];
                        sort($iller);
                        foreach ($iller as $il_adi) { echo "<option value='$il_adi'>$il_adi</option>"; }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Şube İlçesi</label>
                    <select name="ilce" id="formIlce" required>
                        <option value="">Önce İl Seçiniz...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mesajınız (Maks. 500 Karakter)</label>
                    <textarea name="mesaj" rows="5" maxlength="500" required placeholder="Mesajınızı buraya yazabilirsiniz..."></textarea>
                </div>
                <div class="form-group" style="text-align: center;">
                    <label style="display:inline-block; margin-bottom: 10px;">Hizmetimizi Puanlayın</label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="yildiz" value="5" checked /><label for="star5" title="5 Yıldız"><i class="fas fa-star"></i></label>
                        <input type="radio" id="star4" name="yildiz" value="4" /><label for="star4" title="4 Yıldız"><i class="fas fa-star"></i></label>
                        <input type="radio" id="star3" name="yildiz" value="3" /><label for="star3" title="3 Yıldız"><i class="fas fa-star"></i></label>
                        <input type="radio" id="star2" name="yildiz" value="2" /><label for="star2" title="2 Yıldız"><i class="fas fa-star"></i></label>
                        <input type="radio" id="star1" name="yildiz" value="1" /><label for="star1" title="1 Yıldız"><i class="fas fa-star"></i></label>
                    </div>
                </div>
                <button type="submit" name="mesaj_gonder" class="submit-btn">Gönder</button>
            </form>
        </div>
    </div>
</div>

<script>
const turkiyeDataContact = {
    "İstanbul": ["Adalar", "Arnavutköy", "Ataşehir", "Avcılar", "Bağcılar", "Bahçelievler", "Bakırköy", "Başakşehir", "Bayrampaşa", "Beşiktaş", "Beykoz", "Beylikdüzü", "Beyoğlu", "Büyükçekmece", "Çatalca", "Çekmeköy", "Esenler", "Esenyurt", "Eyüpsultan", "Fatih", "Gaziosmanpaşa", "Güngören", "Kadıköy", "Kağıthane", "Kartal", "Küçükçekmece", "Maltepe", "Pendik", "Sancaktepe", "Sarıyer", "Silivri", "Sultanbeyli", "Sultangazi", "Şile", "Şişli", "Tuzla", "Ümraniye", "Üsküdar", "Zeytinburnu"],
    "Ankara": ["Akyurt", "Altındağ", "Ayaş", "Bala", "Beypazarı", "Çamlıdere", "Çankaya", "Çubuk", "Elmadağ", "Etimesgut", "Evren", "Gölbaşı", "Güdül", "Haymana", "Kahramankazan", "Kalecik", "Keçiören", "Kızılcahamam", "Mamak", "Nallıhan", "Polatlı", "Pursaklar", "Sincan", "Şereflikoçhisar", "Yenimahalle"],
    "İzmir": ["Aliağa", "Balçova", "Bayındır", "Bayraklı", "Bergama", "Beydağ", "Bornova", "Buca", "Çeşme", "Çiğli", "Dikili", "Foça", "Gaziemir", "Güzelbahçe", "Karabağlar", "Karaburun", "Karşıyaka", "Kemalpaşa", "Kınık", "Kiraz", "Konak", "Menderes", "Menemen", "Narlıdere", "Ödemiş", "Seferihisar", "Selçuk", "Tire", "Torbalı", "Urla"],
    "Sakarya": ["Adapazarı", "Akyazı", "Arifiye", "Erenler", "Ferizli", "Geyve", "Hendek", "Karapürçek", "Karasu", "Kaynarca", "Kocaali", "Pamukova", "Sapanca", "Serdivan", "Söğütlü", "Taraklı"]
};

function populateFormIlce() {
    const il = document.getElementById('formIl').value;
    const ilceSelect = document.getElementById('formIlce');
    ilceSelect.innerHTML = '<option value="">İlçe Seçiniz...</option>';
    if (turkiyeDataContact[il]) {
        turkiyeDataContact[il].forEach(dist => {
            let opt = document.createElement('option');
            opt.value = dist;
            opt.text = dist;
            ilceSelect.appendChild(opt);
        });
    } else if(il !== "") {
        ilceSelect.innerHTML += '<option value="Merkez / Diğer">Merkez / Diğer</option>';
    }
}
</script>

<?php include 'footer.php'; ?>