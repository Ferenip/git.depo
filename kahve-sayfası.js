// --- 1. KATEGORİ (SEKME) DEĞİŞTİRME ---
function switchTab(tabName) {
    const btnKahveler = document.getElementById('btnKahveler');
    const btnKupalar = document.getElementById('btnKupalar');
    const gridKahveler = document.getElementById('kahveler-grid');
    const gridKupalar = document.getElementById('kupalar-grid');

    if (tabName === 'kahveler') {
        // Buton renkleri
        btnKahveler.classList.replace('btn-inactive', 'btn-active');
        btnKupalar.classList.replace('btn-active', 'btn-inactive');
        
        // Görünürlük
        gridKahveler.style.display = 'grid'; 
        gridKupalar.style.display = 'none';  
    } else {
        // Buton renkleri
        btnKupalar.classList.replace('btn-inactive', 'btn-active');
        btnKahveler.classList.replace('btn-active', 'btn-inactive');
        
        // Görünürlük
        gridKahveler.style.display = 'none'; 
        gridKupalar.style.display = 'grid';  
    }
}

// --- 2. SLIDER (KAYAN RESİMLER) KODLARI ---
let currentSlide = 0;
const totalSlides = 4;
const sliderTrack = document.getElementById('sliderTrack');
const indicators = document.querySelectorAll('.indicator');

function updateSlider() {
    if(!sliderTrack) return; // Hata önleyici
    sliderTrack.style.transform = `translateX(-${currentSlide * 25}%)`;
    indicators.forEach((ind, index) => {
        if(index === currentSlide) {
            ind.classList.add('active');
        } else {
            ind.classList.remove('active');
        }
    });
}

function goToSlide(index) {
    currentSlide = index;
    updateSlider();
}

// Otomatik kaydırma
setInterval(() => {
    currentSlide = (currentSlide + 1) % totalSlides;
    updateSlider();
}, 4000);

// --- MAĞAZA GALERİSİ (OTOMATİK ÇEKİLEN VE KAYAN SİSTEM) ---
let currentStore = 0;
let galleryInterval = null;

function moveGallery(direction) {
    const slides = document.querySelectorAll('.store-slide');
    const slider = document.getElementById('gallerySlider');
    
    if(!slides || slides.length === 0 || !slider) return;

    currentStore = (currentStore + direction + slides.length) % slides.length;
    
    // Her slayt tam %100 olduğu için 100, 200, 300 şeklinde temizce kaydırır
    slider.style.transform = `translateX(-${currentStore * 100}%)`;

    resetGalleryInterval(); // Kullanıcı ok tuşlarına manuel basarsa sayacı sıfırla
}

// Otomatik kayma işlemi (Her 3 saniyede bir)
function startGalleryInterval() {
    galleryInterval = setInterval(() => { moveGallery(1); }, 3000);
}
function resetGalleryInterval() {
    if (galleryInterval) clearInterval(galleryInterval);
    startGalleryInterval();
}

// Sayfa yüklendiğinde otomatik kaymayı başlat
document.addEventListener("DOMContentLoaded", () => {
    const slides = document.querySelectorAll('.store-slide');
    if (slides.length > 0) {
        startGalleryInterval();
    }
});

// --- KALİTE BÖLÜMÜ GÖRSEL DEĞİŞTİRME (HOVER EFEKTİ) ---
function changeQualityImage(imageName) {
    const imgElement = document.getElementById('qualityCenterImg');
    if (imgElement && imageName) {
        imgElement.style.opacity = 0.2; // Yumuşak geçiş için anlık saydamlaştır
        setTimeout(() => {
            imgElement.src = imageName;
            imgElement.style.opacity = 1; // Yeni resimle geri getir
        }, 150); // 150ms bekle (CSS transition süresiyle senkronize)
    }
}

function resetQualityImage() {
    const imgElement = document.getElementById('qualityCenterImg');
    if (imgElement) {
        const defaultImg = imgElement.getAttribute('data-default');
        imgElement.style.opacity = 0.2;
        setTimeout(() => {
            imgElement.src = defaultImg;
            imgElement.style.opacity = 1;
        }, 150);
    }
}

// --- SHOWROOM 3D DÖNÜŞ EFEKTİ (SCROLL İLE) ---
let isScrolling = false;

document.addEventListener('scroll', function () {
    if (!isScrolling) {
        window.requestAnimationFrame(function () {
            const image = document.getElementById('qualityCenterImg');
            const section = document.querySelector('.quality-section');

            if (image && section) {
                const rect = section.getBoundingClientRect();
                const windowHeight = window.innerHeight;

                // Sadece bölüm ekranda görünürken animasyonu çalıştır (Performans için)
                if (rect.top <= windowHeight && rect.bottom >= 0) {
                    const scrollAmount = windowHeight - rect.top;
                    const totalScrollDistance = windowHeight + rect.height;
                    
                    let progress = scrollAmount / totalScrollDistance;
                    progress = Math.max(0, Math.min(1, progress)); 
                    
                    // 2D görseller için en şık showroom efekti: -30 ile +30 derece arası dönüş
                    // Hem kağıt gibi kaybolmayı (bütünlük bozulmasını) önler hem de hacim hissi verir.
                    const rotation = (progress * 60) - 30; 
                    image.style.transform = `rotateY(${rotation}deg) scale(1.05) translateZ(20px)`;
                }
            }
            isScrolling = false;
        });
        isScrolling = true;
    }
});
