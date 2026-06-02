<?php
/**
 * GÜVENLİ YAPAY ZEKA API İŞLEYİCİ (COLOMBOT BACKEND)
 * Bu dosya, web arayüzünden (JavaScript) gelen kullanıcı mesajlarını alır,
 * sunucu tarafında güvenli bir şekilde harici Groq API'sine iletir ve sonucu geri döndürür.
 * Bu sayede API anahtarımız web sitesini gezen kullanıcılar tarafından asla görülemez.
 */

header('Content-Type: application/json');

// 1. GÜVENLİ API ANAHTARI YÜKLEMESİ
include 'config.php'; // API anahtarını harici ve güvenli bir dosyadan yükle
$api_anahtari = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';

// 2. JAVASCRIPT TARAFINDAN GELEN JSON VERİSİNİN OKUNMASI
$gelen_ham_veri = file_get_contents('php://input');
$istek_verisi = json_decode($gelen_ham_veri, true);

// Gelen veri eksik veya hatalıysa API isteğini iptal edip 400 hata kodu fırlatıyoruz
if (!$istek_verisi || !isset($istek_verisi['messages']) || !isset($istek_verisi['system_prompt'])) {
    http_response_code(400); 
    echo json_encode(['error' => ['message' => 'Geçersiz veya eksik istek verisi fırlatıldı.']]);
    exit;
}

// 2.5 GÜVENLİK FİLTRESİ (BLACKLIST) VE MESAJ SARMA (WRAPPER)
$yasakli_kelimeler = ['unut', 'ignore', 'sistem', 'kod yaz', 'prompt', 'önceki talimat', 'talimat', 'html'];
$mesajlar = $istek_verisi['messages'];
$son_mesaj_index = count($mesajlar) - 1;

if ($son_mesaj_index >= 0 && isset($mesajlar[$son_mesaj_index]['role']) && $mesajlar[$son_mesaj_index]['role'] === 'user' && isset($mesajlar[$son_mesaj_index]['content'])) {
    $kullanici_mesaji = mb_strtolower($mesajlar[$son_mesaj_index]['content'], 'UTF-8');
    
    // Kara liste (Blacklist) kontrolü
    foreach ($yasakli_kelimeler as $kelime) {
        if (mb_strpos($kullanici_mesaji, $kelime, 0, 'UTF-8') !== false) {
            // Zararlı kelime algılandı, API isteğini hiç yapmadan doğrudan güvenli cevap dön
            echo json_encode([
                'choices' => [
                    ['message' => ['content' => 'Üzgünüm, size yalnızca kahve menümüz ve mağaza hizmetlerimiz hakkında yardımcı olabilirim.']]
                ]
            ]);
            exit;
        }
    }

    // Wrapper (Bağlam Eklemek)
    $orijinal_mesaj = $mesajlar[$son_mesaj_index]['content'];
    $mesajlar[$son_mesaj_index]['content'] = "Müşteri şunu sordu: [" . $orijinal_mesaj . "]. Eğer bu soru kahve menümüz, sipariş, şubelerimiz veya firmamızla ilgili değilse kesinlikle reddet ve cevaplama.";
}

// 3. GROQ YAPAY ZEKA SERVİSİNE GÖNDERİLECEK PAKETİN HAZIRLANMASI
$gonderilecek_veri_paketi = [
    'model' => 'llama-3.3-70b-versatile', // Kullanılacak yapay zeka model mimarisi
    'temperature' => 0.1, // YAPAY ZEKANIN YARATICILIĞINI KISITLA (DAHA ROBOTİK, KESİN CEVAPLAR)
    'max_tokens' => 30, // İŞLETME MALİYET KORUMASI: Maksimum 30 token uzunluğunda cevap verebilir
    'messages' => array_merge(
        [['role' => 'system', 'content' => $istek_verisi['system_prompt']]], // Botun karakter tanımı (System Prompt)
        $mesajlar // Güvenlik çemberinden (Wrapper) geçirilmiş mesaj geçmişi
    )
];

// 4. cURL KÜTÜPHANESİ İLE SUNUCUDAN SUNUCUYA API İSTEĞİ YAPILMASI
$curl_oturumu = curl_init(); // Değişken adı düzeltildi

// cURL İstek Ayarlarının Yapılandırılması
curl_setopt($curl_oturumu, CURLOPT_URL, 'https://api.groq.com/openai/v1/chat/completions'); 
curl_setopt($curl_oturumu, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($curl_oturumu, CURLOPT_POST, 1); 
curl_setopt($curl_oturumu, CURLOPT_POSTFIELDS, json_encode($gonderilecek_veri_paketi)); 

// HTTP Header (Başlık) ayarları yapılarak API anahtarı sisteme güvenli şekilde tanıtılıyor
curl_setopt($curl_oturumu, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_anahtari,
    'Content-Type: application/json'
]);

// İstek tetikleniyor ve Groq yapay zeka sunucusundan gelen cevap değişkene alınıyor
$yapay_zeka_cevabi = curl_exec($curl_oturumu);

// Eğer cURL bağlantısı esnasında ağ hatası oluşursa hatayı yakala
if (curl_errno($curl_oturumu)) {
    http_response_code(500);
    echo json_encode(['error' => ['message' => 'Groq API bağlantı hatası: ' . curl_error($curl_oturumu)]]);
    exit;
}

// Yapay zekadan gelen ham yanıtı tarayıcıya (JavaScript'e) geri fırlatıyoruz
echo $yapay_zeka_cevabi;
?>