<?php include 'baglan.php'; ?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colombia Coffee - Menü</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="kahve-sayfası.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="icon.png">
    <link href="index.php">
    <link href="menu.php">

</head>

<body>
    <header>
        <div class="logo">
            <img src="icon.png" alt="Colombia Coffee Logo">
        </div>

        <div class="right-menu">
            <nav id="navbar">
                <a href="index.php">Ana Sayfa</a>
                <div class="dropdown">
                    <a href="kurumsal.php" class="dropbtn">Kurumsal <i class="fa-solid fa-chevron-down"
                            style="font-size: 10px; margin-left: 3px;"></i></a>
                    <div class="dropdown-content">
                        <a href="kurumsal.php?tab=hakkimizda">Hakkımızda</a>
                        <a href="kurumsal.php?tab=magazalar">Mağazalarımız</a>
                        <a href="https://www.kariyer.net/firma-profil/colombia-coffee-293821-338005"
                            target="_blank">Kariyer</a>
                    </div>
                </div>
                <a href="menu.php">Menü</a>
                <a href="iletisim.php">İletişim</a>
            </nav>
            <div class="lang-search">
                <!-- ÇOKLU DİL SEÇENEĞİ (TÜRKÇE/İNGİLİZCE) -->
                <div class="lang-dropdown">
                    <img src="https://flagcdn.com/w40/tr.png" alt="Dil" class="current-lang-flag" id="currentLangFlag">
                    <div class="lang-dropdown-content" id="langDropdownContent">
                        <div class="lang-close-btn" id="langCloseBtn"><i class="fa-solid fa-xmark"></i></div>
                        <a href="#" onclick="changeLanguage('tr'); return false;"><img src="https://flagcdn.com/w40/tr.png" alt="TR"> Türkçe</a>
                        <a href="#" onclick="changeLanguage('en'); return false;"><img src="https://flagcdn.com/w40/gb.png" alt="EN"> English</a>
                    </div>
                </div>
                
                <div class="search-container">
                    <i class="fa-solid fa-magnifying-glass search-icon" id="searchIcon"></i>
                    <div class="search-box" id="searchBox">
                        <input type="text" id="searchInput" placeholder="Ürün veya mağaza ara... (Örn: İstanbul)" autocomplete="off">
                        <div class="search-results" id="searchResults"></div>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- Google Translate API (Arka Planda Gizli Çalışır) -->
    <div id="google_translate_element" style="display:none;"></div>
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({pageLanguage: 'tr', includedLanguages: 'en,tr', autoDisplay: false}, 'google_translate_element');
        }
    </script>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchIcon = document.getElementById('searchIcon');
            const searchBox = document.getElementById('searchBox');
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');

            // Dil Menüsü Tanımlamaları
            const currentFlag = document.getElementById('currentLangFlag');
            const langDropdownContent = document.getElementById('langDropdownContent');
            const langCloseBtn = document.getElementById('langCloseBtn');

            // Bayrağa Tıklayınca Menüyü Açma/Kapama İşlemi
            currentFlag.addEventListener('click', function(e) {
                e.stopPropagation();
                langDropdownContent.classList.toggle('active');
                searchBox.classList.remove('active'); // Eğer arama kutusu açıksa onu kapatır
            });

            // Çarpı (X) İşaretine Basınca Kapatma İşlemi
            langCloseBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                langDropdownContent.classList.remove('active');
            });

            // Seçili Dili (Bayrağı) Kontrol Et ve Göster
            if (document.cookie.includes('googtrans=/tr/en')) {
                currentFlag.src = "https://flagcdn.com/w40/gb.png";
            } else {
                currentFlag.src = "https://flagcdn.com/w40/tr.png";
            }

            // Dili Değiştiren Fonksiyon (Google Translate Çerezlerini Ayarlar)
            window.changeLanguage = function(lang) {
                if(lang === 'tr') {
                    document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                    document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain=" + window.location.hostname + "; path=/;";
                } else {
                    document.cookie = "googtrans=/tr/" + lang + "; path=/;";
                    document.cookie = "googtrans=/tr/" + lang + "; domain=" + window.location.hostname + "; path=/;";
                }
                window.location.reload();
            };

            // Büyütece tıklayınca arama kutusunu aç/kapat
            searchIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                searchBox.classList.toggle('active');
                if(searchBox.classList.contains('active')) {
                    searchInput.focus(); // Kutu açılınca doğrudan yazmaya başlanabilir
                }
            });

            // Kutunun dışına tıklayınca arama kutusunu kapat
            document.addEventListener('click', function(e) {
                if(!searchIcon.contains(e.target) && !searchBox.contains(e.target)) {
                    searchBox.classList.remove('active');
                }
                if(!currentFlag.contains(e.target) && !langDropdownContent.contains(e.target)) {
                    langDropdownContent.classList.remove('active');
                }
            });

            // Canlı Arama (Live Search) - Her harf girildiğinde çalışır
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                if(query.length > 1) { // En az 2 harf girilince aramaya başla
                    fetch('arama.php?q=' + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        if(data.length > 0) {
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'search-item';
                                
                                // Resim yolu belirleme
                                let imgSrc = 'icon.png'; // Şubeler veya resimsizler için varsayılan ikon
                                if (item.tip === 'urun' && item.resim && item.resim !== '') {
                                    imgSrc = 'uploads/' + item.resim;
                                }
                                
                                // Alt bilgi (Ürünse fiyatı yazar, Şubeyse "Kurumsal Mağaza" yazar)
                                let altBilgiText = item.tip === 'urun' ? item.alt_bilgi + ' ₺' : item.alt_bilgi;
                                
                                div.innerHTML = `
                                    <img src="${imgSrc}" alt="${item.baslik}">
                                    <div class="item-info">
                                        <div class="item-title">${item.baslik}</div>
                                        <div class="item-price">${altBilgiText}</div>
                                    </div>
                                `;
                                // Tıklanınca ilgili doğru sayfaya yönlendirir
                                div.addEventListener('click', () => { 
                                    if (item.tip === 'urun') {
                                        window.location.href = 'menu.php'; 
                                    } else {
                                        window.location.href = 'kurumsal.php?tab=magazalar'; 
                                    }
                                });
                                searchResults.appendChild(div);
                            });
                            searchResults.style.display = 'block';
                        } else {
                            searchResults.innerHTML = '<div class="no-result">Aramanızla eşleşen sonuç bulunamadı.</div>';
                            searchResults.style.display = 'block';
                        }
                    });
                } else {
                    searchResults.style.display = 'none';
                }
            });
        });
    </script>