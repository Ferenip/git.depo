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

// Otomatik kayma işlemi (Her 20 saniyede bir)
function startGalleryInterval() {
    galleryInterval = setInterval(() => { moveGallery(1); }, 20000);
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
