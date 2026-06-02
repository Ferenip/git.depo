<?php
include 'baglan.php';

header('Content-Type: application/json');

// Sadece dışarıdan AJAX isteklerine açık bir arama uç noktasıdır.
if (isset($_GET['q'])) {
    $aranan = trim($_GET['q']);
    
    if (strlen($aranan) > 1) {
        $sonuclar = [];
        
        // 1. Ürünlerde Arama (Maksimum 5 sonuç)
        $sorgu_urun = $db->prepare("SELECT id, urun_adi as baslik, fiyat as alt_bilgi, resim, 'urun' as tip FROM urunler WHERE durum = 1 AND urun_adi LIKE ? ORDER BY urun_adi ASC LIMIT 5");
        $sorgu_urun->execute(["%" . $aranan . "%"]);
        $urunler = $sorgu_urun->fetchAll(PDO::FETCH_ASSOC);

        // 2. Mağazalarda / Şubelerde Arama (Maksimum 5 sonuç)
        $sorgu_sube = $db->prepare("SELECT id, CONCAT(il, ' - ', ilce) as baslik, 'Kurumsal Mağaza' as alt_bilgi, resim, 'sube' as tip FROM subeler WHERE il LIKE ? OR ilce LIKE ? ORDER BY il ASC, ilce ASC LIMIT 5");
        $sorgu_sube->execute(["%" . $aranan . "%", "%" . $aranan . "%"]);
        $subeler = $sorgu_sube->fetchAll(PDO::FETCH_ASSOC);

        // Sonuçları birleştirip JSON olarak gönder
        $tum_sonuclar = array_merge($urunler, $subeler);
        echo json_encode($tum_sonuclar);
    } else {
        echo json_encode([]); // 2 harften az girilirse boş dön
    }
} else {
    echo json_encode([]);
}
?>