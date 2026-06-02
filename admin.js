document.addEventListener("DOMContentLoaded", function () {
    // Türkiye İlçe Veri Seti
    const turkiyeData = {
        "İstanbul": ["Adalar", "Arnavutköy", "Ataşehir", "Avcılar", "Bağcılar", "Bahçelievler", "Bakırköy", "Başakşehir", "Bayrampaşa", "Beşiktaş", "Beykoz", "Beylikdüzü", "Beyoğlu", "Büyükçekmece", "Çatalca", "Çekmeköy", "Esenler", "Esenyurt", "Eyüpsultan", "Fatih", "Gaziosmanpaşa", "Güngören", "Kadıköy", "Kağıthane", "Kartal", "Küçükçekmece", "Maltepe", "Pendik", "Sancaktepe", "Sarıyer", "Silivri", "Sultanbeyli", "Sultangazi", "Şile", "Şişli", "Tuzla", "Ümraniye", "Üsküdar", "Zeytinburnu"],
        "Ankara": ["Akyurt", "Altındağ", "Ayaş", "Bala", "Beypazarı", "Çamlıdere", "Çankaya", "Çubuk", "Elmadağ", "Etimesgut", "Evren", "Gölbaşı", "Güdül", "Haymana", "Kahramankazan", "Kalecik", "Keçiören", "Kızılcahamam", "Mamak", "Nallıhan", "Polatlı", "Pursaklar", "Sincan", "Şereflikoçhisar", "Yenimahalle"],
        "İzmir": ["Aliağa", "Balçova", "Bayındır", "Bayraklı", "Bergama", "Beydağ", "Bornova", "Buca", "Çeşme", "Çiğli", "Dikili", "Foça", "Gaziemir", "Güzelbahçe", "Karabağlar", "Karaburun", "Karşıyaka", "Kemalpaşa", "Kınık", "Kiraz", "Konak", "Menderes", "Menemen", "Narlıdere", "Ödemiş", "Seferihisar", "Selçuk", "Tire", "Torbalı", "Urla"],
        "Sakarya": ["Adapazarı", "Akyazı", "Arifiye", "Erenler", "Ferizli", "Geyve", "Hendek", "Karapürçek", "Karasu", "Kaynarca", "Kocaali", "Pamukova", "Sapanca", "Serdivan", "Söğütlü", "Taraklı"],
    };

    window.filterCities = function () {
        let input = document.getElementById('citySearch').value.toLocaleLowerCase('tr-TR');
        let options = document.getElementById('cityList').options;
        for (let i = 0; i < options.length; i++) {
            options[i].style.display = options[i].text.toLocaleLowerCase('tr-TR').includes(input) ? '' : 'none';
        }
    }

    window.syncCitySelection = function () {
        const cityList = document.getElementById('cityList');
        const districtList = document.getElementById('districtList');
        if (!cityList || !districtList) return;

        document.getElementById('citySearch').value = cityList.value;
        districtList.innerHTML = "";
        document.getElementById('districtSearch').value = "";

        if (turkiyeData[cityList.value]) {
            turkiyeData[cityList.value].forEach(dist => {
                districtList.add(new Option(dist, dist));
            });
        } else {
            districtList.add(new Option("İlçeler yüklenemedi", ""));
        }
    }

    window.filterDistricts = function () {
        let input = document.getElementById('districtSearch').value.toLocaleLowerCase('tr-TR');
        let options = document.getElementById('districtList').options;
        for (let i = 0; i < options.length; i++) {
            options[i].style.display = options[i].text.toLocaleLowerCase('tr-TR').includes(input) ? '' : 'none';
        }
    }

    window.syncDistrictSelection = function () {
        const districtList = document.getElementById('districtList');
        if (districtList) document.getElementById('districtSearch').value = districtList.value;
    }

    window.filterStores = function () {
        let input = document.getElementById('storeSearch').value.toLocaleLowerCase('tr-TR');
        let rows = document.querySelectorAll('#storeTable .store-row');
        rows.forEach(row => {
            row.style.display = row.innerText.toLocaleLowerCase('tr-TR').includes(input) ? '' : 'none';
        });
    }

    // --- OTOMATİK ÇIKIŞ SİSTEMİ (5 DAKİKA) ---
    let inactivityTimer;
    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => { window.location.href = 'logout.php'; }, 300000);
    }
    window.onload = resetInactivityTimer;
    ['mousemove', 'keydown', 'onclick', 'onscroll'].forEach(e => document.addEventListener(e, resetInactivityTimer));

    // --- YENİ MESAJ KONTROLÜ ---
    const currentMaxIdElement = document.getElementById('current-max-id');
    if (currentMaxIdElement) {
        let currentMaxId = parseInt(currentMaxIdElement.value, 10);
        setInterval(() => {
            fetch('admin.php?check_new_messages=1')
                .then(response => response.text())
                .then(data => {
                    if (parseInt(data, 10) > currentMaxId) window.location.reload();
                })
                .catch(err => console.error(err));
        }, 10000);
    }

    // --- ÜRÜN AKTİF/PASİF DURUM DEĞİŞTİRME ---
    window.setDurum = function (urunId, durum) {
        document.getElementById('durum_input_' + urunId).value = durum;
        let btnAktif = document.getElementById('btn_aktif_' + urunId);
        let btnPasif = document.getElementById('btn_pasif_' + urunId);
        if (durum === 1) {
            btnAktif.className = 'durum-btn durum-secili';
            btnPasif.className = 'durum-btn durum-gri';
        } else {
            btnAktif.className = 'durum-btn durum-gri';
            btnPasif.className = 'durum-btn durum-secili';
        }
    }

    // --- ÜRÜN GÜNCELLEME MODU (GİZLİ İNPUTLARI AÇAR) ---
    window.guncelleModu = function (urunId) {
        let guncelleBtn = document.getElementById('btn_guncelle_' + urunId);

        if (guncelleBtn.innerText === 'Güncelle') {
            // Düz metinleri gizle
            document.getElementById('text_ad_' + urunId).style.display = 'none';
            document.getElementById('text_kat_' + urunId).style.display = 'none';
            document.getElementById('text_fiyat_' + urunId).style.display = 'none';
            document.getElementById('img_' + urunId).style.display = 'none';

            // Form (Input) alanlarını göster
            document.getElementById('input_ad_' + urunId).style.display = 'block';
            document.getElementById('input_kat_' + urunId).style.display = 'block';
            document.getElementById('input_fiyat_' + urunId).style.display = 'block';
            document.getElementById('file_' + urunId).style.display = 'block';

            guncelleBtn.innerText = 'Düzenleniyor';
            guncelleBtn.style.background = '#f0ad4e';
        } else {
            // Form alanlarını geri kapat, metni göster (İptal etmek istenirse)
            document.getElementById('input_ad_' + urunId).style.display = 'none';
            document.getElementById('input_kat_' + urunId).style.display = 'none';
            document.getElementById('input_fiyat_' + urunId).style.display = 'none';
            document.getElementById('file_' + urunId).style.display = 'none';

            document.getElementById('text_ad_' + urunId).style.display = '';
            document.getElementById('text_kat_' + urunId).style.display = '';
            document.getElementById('text_fiyat_' + urunId).style.display = '';
            document.getElementById('img_' + urunId).style.display = '';

            guncelleBtn.innerText = 'Güncelle';
            guncelleBtn.style.background = '#153523';
        }
    }

    // --- KATEGORİ FİLTRELEME ---
    window.kategoriFiltrele = function () {
        let secilenKategori = document.getElementById('kategoriFiltre').value;
        let satirlar = document.querySelectorAll('.urun-satiri');
        satirlar.forEach(satir => {
            let satirKategori = satir.getAttribute('data-kategori');
            satir.style.display = (secilenKategori === 'tumu' || satirKategori === secilenKategori) ? '' : 'none';
        });
    }

    // Event listener'ları ata
    const cityList = document.getElementById('cityList');
    if (cityList) cityList.addEventListener('change', window.syncCitySelection);

    const districtList = document.getElementById('districtList');
    if (districtList) districtList.addEventListener('change', window.syncDistrictSelection);

    const kategoriFiltre = document.getElementById('kategoriFiltre');
    if (kategoriFiltre) kategoriFiltre.addEventListener('change', window.kategoriFiltrele);
});