<?php
/**
 * YÖNETİCİ PANELİ İŞLEMLERİ (CONTROLLER)
 * Tüm form gönderimleri (POST) ve silme işlemleri (GET) burada yakalanır.
 * Spagetti kodu engellemek amacıyla HTML arayüzü ile veritabanı işlemleri birbirinden ayrılmıştır.
 */

// Bu dosyaya doğrudan erişimi engelle
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    die('Bu sayfaya doğrudan erişim engellenmiştir.');
}

// --- GÜVENLİK: CSRF TOKEN OLUŞTURMA ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

/**
 * CSRF TOKEN KONTROL FONKSİYONU
 * Formların başka siteler veya tarayıcılar üzerinden (Cross-Site Request Forgery) zorla gönderilmesini engeller.
 * @param string $token Formdan gelen gizli güvenlik anahtarı
 */
function verify_csrf_token($token) {
    if (!isset($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        // Token eşleşmiyorsa işlemi durdur
        die('CSRF token doğrulaması başarısız! Lütfen sayfayı yenileyip tekrar deneyin.');
    }
}

/**
 * DOSYA MIME TÜRÜ KONTROL FONKSİYONU (GELİŞMİŞ GÜVENLİK)
 * Sadece dosya uzantısına (.jpg) değil, dosyanın kendi iç mimarisine bakarak zararlı dosyaları (shell script vb.) engeller.
 * @param string $tmp_name Yüklenen dosyanın geçici yolu
 * @param array $allowed_mimes İzin verilen MIME türleri dizisi
 * @return bool Dosya güvenliyse true döner
 */
function is_allowed_mime_type($tmp_name, $allowed_mimes) {
    if (empty($tmp_name) || !file_exists($tmp_name)) {
        return false;
    }
    
    $mime_type = '';

    // 1. Seçenek: finfo eklentisi aktifse kullan
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $tmp_name);
        finfo_close($finfo);
    } 
    // 2. Seçenek: finfo yoksa mime_content_type kullan
    elseif (function_exists('mime_content_type')) {
        $mime_type = mime_content_type($tmp_name);
    }
    
    // 3. Seçenek: Eğer üsttekiler başarısız olduysa veya desteklenmiyorsa resimler için "getimagesize" kullan
    if (empty($mime_type) && function_exists('getimagesize')) {
        $img_info = @getimagesize($tmp_name);
        if ($img_info !== false && isset($img_info['mime'])) {
            $mime_type = $img_info['mime'];
        }
    }
    
    // Hiçbir okuyucu çalışmazsa engellememek için fallback (son çare)
    if (empty($mime_type)) return true;

    return in_array($mime_type, $allowed_mimes);
}


// --- ARKA PLAN (AJAX) YENİ MESAJ KONTROLÜ ---
if (isset($_GET['check_new_messages'])) {
    $max_id = $db->query("SELECT MAX(id) FROM iletisim_mesajlari")->fetchColumn();
    echo $max_id ? $max_id : 0;
    exit;
}

$sayfa = isset($_GET['sayfa']) ? $_GET['sayfa'] : 'dashboard';

// --- ANA SAYFA: VİDEO YÜKLEME VE URL GİRME İŞLEMLERİ ---
if (isset($_POST['video_yukle'])) {
    verify_csrf_token($_POST['csrf_token']); // CSRF Koruması
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        
        // GÜVENLİK GÜNCELLEMESİ: Sadece uzantıya değil, dosyanın gerçek içeriğine (MIME) bak
        if (!is_allowed_mime_type($_FILES['video']['tmp_name'], ['video/mp4', 'video/webm'])) {
            header("Location: admin.php?sayfa=anasayfa&durum=gecersiz_video");
            exit;
        }

        if (!is_dir('uploads')) mkdir('uploads', 0777, true);
        // Güvenlik için dosya adını sabitliyoruz, orjinal adı kullanmıyoruz.
        move_uploaded_file($_FILES['video']['tmp_name'], 'uploads/home_video.mp4');

        // Yerel video yüklendiğinde eski URL'yi temizle ki çakışma olmasın
        $db->query("UPDATE ayarlar SET video_url = '' WHERE id = 1");

        header("Location: admin.php?sayfa=anasayfa&durum=video_ok");
        exit;
    }
}

if (isset($_POST['url_kaydet'])) {
    verify_csrf_token($_POST['csrf_token']); // CSRF Koruması
    $girilen_url = filter_var($_POST['video_url'], FILTER_SANITIZE_URL); // GÜVENLİK: URL'yi temizle
    $final_url = $girilen_url; 

    if (strpos($girilen_url, 'youtube.com/watch?v=') !== false) {
        parse_str(parse_url($girilen_url, PHP_URL_QUERY), $query_params);
        if (isset($query_params['v'])) {
            $video_id = htmlspecialchars($query_params['v']); // GÜVENLİK
            $final_url = "https://www.youtube.com/embed/{$video_id}?autoplay=1&mute=1&loop=1&playlist={$video_id}&controls=0&showinfo=0&rel=0&playsinline=1";
        }
    }
    elseif (strpos($girilen_url, 'youtu.be/') !== false) {
        $video_id = htmlspecialchars(ltrim(parse_url($girilen_url, PHP_URL_PATH), '/')); // GÜVENLİK
        if ($video_id) {
            $final_url = "https://www.youtube.com/embed/{$video_id}?autoplay=1&mute=1&loop=1&playlist={$video_id}&controls=0&showinfo=0&rel=0&playsinline=1";
        }
    }

    if (file_exists('uploads/home_video.mp4')) {
        unlink('uploads/home_video.mp4');
    }

    $sorgu = $db->prepare("UPDATE ayarlar SET video_url = ? WHERE id = 1");
    $sorgu->execute([$final_url]);
    header("Location: admin.php?sayfa=anasayfa&durum=url_ok");
    exit;
}

// --- ANA SAYFA: KALİTE BÖLÜMÜ GÖRSELLERİNİ GÜNCELLEME İŞLEMİ ---
if (isset($_POST['kalite_gorsel_guncelle'])) {
    verify_csrf_token($_POST['csrf_token']); // CSRF Koruması
    
    $alanlar = [
        'kalite_merkez', 'kalite_1', 'kalite_2', 
        'kalite_3', 'kalite_4', 'kalite_5', 'kalite_6'
    ];

    // Klasör yoksa oluştur
    if (!is_dir('uploads/kalite')) {
        mkdir('uploads/kalite', 0777, true);
    }

    $guncelleme_yapildi = false;
    $hata = false;

    foreach ($alanlar as $alan) {
        if (isset($_FILES[$alan]) && $_FILES[$alan]['error'] == 0) {
            // GÜVENLİK GÜNCELLEMESİ: GIF formatına da izin verdik
            if (is_allowed_mime_type($_FILES[$alan]['tmp_name'], ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
                $uzanti = strtolower(pathinfo($_FILES[$alan]['name'], PATHINFO_EXTENSION));
                $yeni_resim_adi = time() . '_' . $alan . '.' . $uzanti;
                $hedef_yol = 'uploads/kalite/' . $yeni_resim_adi;
                
                if (move_uploaded_file($_FILES[$alan]['tmp_name'], $hedef_yol)) {
                    $sorgu = $db->prepare("UPDATE ayarlar SET {$alan} = ? WHERE id = 1");
                    $sorgu->execute([$hedef_yol]);
                    $guncelleme_yapildi = true;
                }
            } else {
                $hata = true;
            }
        } elseif (isset($_FILES[$alan]) && $_FILES[$alan]['error'] !== 4) { // 4 numaralı hata = dosya seçilmemesi (normaldir)
            $hata = true; // Boyut aşımı veya PHP Upload hataları
        }
    }

    if ($guncelleme_yapildi && !$hata) {
        header("Location: admin.php?sayfa=anasayfa&durum=kalite_ok");
    } elseif ($guncelleme_yapildi && $hata) {
        header("Location: admin.php?sayfa=anasayfa&durum=kalite_kismen");
    } elseif (!$guncelleme_yapildi && $hata) {
        header("Location: admin.php?sayfa=anasayfa&durum=kalite_hata");
    } else {
        header("Location: admin.php?sayfa=anasayfa");
    }
    exit;
}

// --- KURUMSAL: GÜVENLİ ŞUBE EKLEME İŞLEMİ ---
if (isset($_POST['sube_ekle'])) {
    verify_csrf_token($_POST['csrf_token']); // CSRF Koruması
    $il = trim($_POST['il']);
    $ilce = trim($_POST['ilce']);

    if (empty($il) || empty($ilce)) {
        header("Location: admin.php?sayfa=kurumsal_magazalar&durum=bos_alan");
        exit;
    }

    $sorgu = $db->prepare("INSERT INTO subeler (il, ilce, resim) VALUES (?, ?, '')");
    $sorgu->execute([htmlspecialchars($il), htmlspecialchars($ilce)]); // GÜVENLİK
    header("Location: admin.php?sayfa=kurumsal_magazalar&durum=ok");
    exit;
}

// --- KURUMSAL: ŞUBE SİLME İŞLEMİ ---
if (isset($_GET['sube_sil']) && isset($_GET['csrf_token'])) {
    verify_csrf_token($_GET['csrf_token']); // CSRF Koruması (GET üzerinden)
    $id = $_GET['sube_sil'];

    if (!isset($id) || !is_numeric($id)) {
        header("Location: admin.php?sayfa=kurumsal_magazalar&durum=gecersiz_id");
        exit;
    }

    $sorgu = $db->prepare("DELETE FROM subeler WHERE id = ?");
    $sorgu->execute([$id]);
    header("Location: admin.php?sayfa=kurumsal_magazalar&durum=silindi");
    exit;
}

// --- KURUMSAL: JSON İLE TOPLU ŞUBE EKLEME (GÜVENLİ) ---
if (isset($_POST['toplu_sube_ekle'])) {
    verify_csrf_token($_POST['csrf_token']); // CSRF Koruması
    if (isset($_FILES['json_dosya']) && $_FILES['json_dosya']['error'] == 0) {
        
        // GÜVENLİK GÜNCELLEMESİ: MIME TÜRÜ KONTROLÜ
        if (!is_allowed_mime_type($_FILES['json_dosya']['tmp_name'], ['application/json', 'text/plain'])) {
            header("Location: admin.php?sayfa=kurumsal_magazalar&durum=gecersiz_json_tip");
            exit;
        }

        $json_icerik = file_get_contents($_FILES['json_dosya']['tmp_name']);
        $veriler = json_decode($json_icerik, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($veriler)) {
            $eklenen_sayi = 0;
            $sorgu = $db->prepare("INSERT INTO subeler (il, ilce, resim) VALUES (?, ?, ?)");

            foreach ($veriler as $sube) {
                if (isset($sube['il']) && isset($sube['ilce'])) {
                    $sorgu->execute([htmlspecialchars($sube['il']), htmlspecialchars($sube['ilce']), '']);
                    $eklenen_sayi++;
                }
            }
            header("Location: admin.php?sayfa=kurumsal_magazalar&durum=toplu_ok&sayi=$eklenen_sayi");
            exit;
        } else {
            header("Location: admin.php?sayfa=kurumsal_magazalar&durum=gecersiz_json_icerik");
            exit;
        }
    }
}

// --- ANA SAYFA VİTRİN ŞUBELERİ GÜNCELLEME İŞLEMİ ---
if (isset($_POST['anasayfa_sube_guncelle'])) {
    verify_csrf_token($_POST['csrf_token']); // CSRF Koruması
    $id = $_POST['vitrin_id'];
    $il = htmlspecialchars(trim($_POST['il']));
    $ilce = htmlspecialchars(trim($_POST['ilce']));
    $resim_adi = $_POST['mevcut_resim']; 

    if (isset($_FILES['resim']) && $_FILES['resim']['error'] == 0) {
        // GÜVENLİK GÜNCELLEMESİ: MIME TÜRÜ KONTROLÜ
        if (!is_allowed_mime_type($_FILES['resim']['tmp_name'], ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
            header("Location: admin.php?sayfa=anasayfa&durum=gecersiz_resim");
            exit;
        }
        $uzanti = strtolower(pathinfo($_FILES['resim']['name'], PATHINFO_EXTENSION));
        $resim_adi = time() . '_vitrin_' . $id . '.' . $uzanti;
        if (!is_dir('uploads/subeler')) mkdir('uploads/subeler', 0777, true);
        move_uploaded_file($_FILES['resim']['tmp_name'], 'uploads/subeler/' . $resim_adi);
    }

    $sorgu = $db->prepare("UPDATE anasayfa_subeler SET il = ?, ilce = ?, resim = ? WHERE id = ?");
    $sorgu->execute([$il, $ilce, $resim_adi, $id]);
    header("Location: admin.php?sayfa=anasayfa&durum=vitrin_ok");
    exit;
}

// --- MENÜ: ÜRÜN EKLEME İŞLEMİ ---
if (isset($_POST['urun_ekle'])) {
    verify_csrf_token($_POST['csrf_token']); // CSRF Koruması
    $urun_adi = trim($_POST['urun_adi']);
    $kategori = trim($_POST['kategori']);
    $fiyat = trim($_POST['fiyat']);
    $resim_adi = "";

    if (empty($urun_adi) || empty($kategori) || empty($fiyat)) {
        header("Location: admin.php?sayfa=menu&durum=bos_alan");
        exit;
    }

    if (!is_numeric($fiyat) || $fiyat <= 0) {
        header("Location: admin.php?sayfa=menu&durum=gecersiz_fiyat");
        exit;
    }

    if (isset($_FILES['resim']) && $_FILES['resim']['error'] == 0) {
        // GÜVENLİK GÜNCELLEMESİ: MIME TÜRÜ KONTROLÜ
        if (!is_allowed_mime_type($_FILES['resim']['tmp_name'], ['image/jpeg', 'image/png', 'image/webp'])) {
            header("Location: admin.php?sayfa=menu&durum=gecersiz_dosya");
            exit;
        }
        $uzanti = strtolower(pathinfo($_FILES['resim']['name'], PATHINFO_EXTENSION));
        $resim_adi = time() . '_' . uniqid() . '.' . $uzanti;
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['resim']['tmp_name'], 'uploads/' . $resim_adi);
    }

    $sorgu = $db->prepare("INSERT INTO urunler (urun_adi, kategori, fiyat, resim) VALUES (?, ?, ?, ?)");
    $sorgu->execute([htmlspecialchars($urun_adi), htmlspecialchars($kategori), $fiyat, $resim_adi]);
    header("Location: admin.php?sayfa=menu&durum=eklendi");
    exit;
}

// --- MENÜ: ÜRÜN SİLME İŞLEMİ ---
if (isset($_GET['sil']) && isset($_GET['csrf_token'])) {
    verify_csrf_token($_GET['csrf_token']); // CSRF Koruması
    $id = $_GET['sil'];

    if (!isset($id) || !is_numeric($id)) {
        header("Location: admin.php?sayfa=menu&durum=gecersiz_id");
        exit;
    }
    
    // Silmeden önce resim dosyasını da sunucudan silelim
    $resim_sorgu = $db->prepare("SELECT resim FROM urunler WHERE id = ?");
    $resim_sorgu->execute([$id]);
    $resim_dosyasi = $resim_sorgu->fetchColumn();
    if ($resim_dosyasi && file_exists('uploads/' . $resim_dosyasi)) {
        unlink('uploads/' . $resim_dosyasi);
    }

    $sorgu = $db->prepare("DELETE FROM urunler WHERE id = ?");
    $sorgu->execute([$id]);
    header("Location: admin.php?sayfa=menu&durum=silindi");
    exit;
}

// --- MENÜ: TOPLU DEĞİŞİKLİKLERİ KAYDETME (AKTİF/PASİF VE KISMİ GÜNCELLEME) ---
if (isset($_POST['toplu_durum_kaydet'])) {
    verify_csrf_token($_POST['csrf_token']); // CSRF Koruması
    
    $durumlar = $_POST['durumlar'] ?? [];
    $adlar = $_POST['urun_adlari'] ?? [];
    $kategoriler = $_POST['kategoriler'] ?? [];
    $fiyatlar = $_POST['fiyatlar'] ?? [];
    
    foreach ($durumlar as $id => $durum) {
        $urun_adi = isset($adlar[$id]) ? trim($adlar[$id]) : null;
        $kategori = isset($kategoriler[$id]) ? trim($kategoriler[$id]) : null;
        $fiyat = isset($fiyatlar[$id]) ? trim($fiyatlar[$id]) : null;
        $durum = (int)$durum;

        // Metin ve fiyat verilerini (eski veriyi koruyarak) güncelle
        if ($urun_adi !== null && $kategori !== null && $fiyat !== null) {
            $sorgu = $db->prepare("UPDATE urunler SET urun_adi = ?, kategori = ?, fiyat = ?, durum = ? WHERE id = ?");
            $sorgu->execute([htmlspecialchars($urun_adi), htmlspecialchars($kategori), $fiyat, $durum, $id]);
        }

        // SADECE EĞER YENİ BİR RESİM SEÇİLMİŞSE RESMİ GÜNCELLE (Eskisi gibi zorunlu değil!)
        if (isset($_FILES['resimler']['name'][$id]) && $_FILES['resimler']['error'][$id] == 0) {
            $tmp_name = $_FILES['resimler']['tmp_name'][$id];
            $name = $_FILES['resimler']['name'][$id];

            if (is_allowed_mime_type($tmp_name, ['image/jpeg', 'image/png', 'image/webp'])) {
                $uzanti = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $yeni_resim_adi = time() . '_' . uniqid() . '.' . $uzanti;
                
                if (!is_dir('uploads')) mkdir('uploads', 0777, true);
                if (move_uploaded_file($tmp_name, 'uploads/' . $yeni_resim_adi)) {
                    $db->prepare("UPDATE urunler SET resim = ? WHERE id = ?")->execute([$yeni_resim_adi, $id]);
                }
            }
        }
    }
    
    header("Location: admin.php?sayfa=menu&durum=guncellendi");
    exit;
}

// --- MENÜ: SLIDER GÜNCELLEME İŞLEMİ ---
if (isset($_POST['slider_guncelle'])) {
    verify_csrf_token($_POST['csrf_token']); // CSRF Koruması
    $slider_id = $_POST['slider_id'];

    if (isset($_FILES['slider_resim']) && $_FILES['slider_resim']['error'] == 0) {
        // GÜVENLİK GÜNCELLEMESİ: MIME TÜRÜ KONTROLÜ
        if (!is_allowed_mime_type($_FILES['slider_resim']['tmp_name'], ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
            header("Location: admin.php?sayfa=menu&durum=gecersiz_resim");
            exit;
        }
        $uzanti = strtolower(pathinfo($_FILES['slider_resim']['name'], PATHINFO_EXTENSION));
        $resim_adi = time() . '_slider_' . $slider_id . '.' . $uzanti;
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['slider_resim']['tmp_name'], 'uploads/' . $resim_adi);
        
        $sorgu = $db->prepare("UPDATE slider SET resim = ? WHERE id = ?");
        $sorgu->execute([$resim_adi, $slider_id]);
        header("Location: admin.php?sayfa=menu&durum=slider_ok");
        exit;
    }
}

// --- KURUMSAL: HAKKIMIZDA GÜNCELLEME İŞLEMİ ---
if (isset($_POST['hakkimizda_guncelle'])) {
    verify_csrf_token($_POST['csrf_token']); // CSRF Koruması
    $metin = $_POST['hakkimizda_metin']; // htmlspecialchars burada kullanılmaz, nl2br ile gösterilecek
    $sorgu = $db->prepare("UPDATE ayarlar SET hakkimizda_metin = ? WHERE id = 1");
    $sorgu->execute([$metin]);
    header("Location: admin.php?sayfa=kurumsal_hakkimizda&durum=ok");
    exit;
}

// --- İLETİŞİM: SOSYAL MEDYA EKLEME İŞLEMİ ---
if (isset($_POST['sosyal_ekle'])) {
    verify_csrf_token($_POST['csrf_token']); // CSRF Koruması
    $platform = htmlspecialchars($_POST['platform']);
    $url = filter_var($_POST['url'], FILTER_SANITIZE_URL); // GÜVENLİK
    $sorgu = $db->prepare("INSERT INTO sosyal_medya (platform, url) VALUES (?, ?)");
    $sorgu->execute([$platform, $url]);
    header("Location: admin.php?sayfa=iletisim_sosyal&durum=ok");
    exit;
}

// --- İLETİŞİM: SOSYAL MEDYA SİLME İŞLEMİ ---
if (isset($_GET['sosyal_sil']) && isset($_GET['csrf_token'])) {
    verify_csrf_token($_GET['csrf_token']); // CSRF Koruması
    $id = $_GET['sosyal_sil'];
    if (!isset($id) || !is_numeric($id)) {
        header("Location: admin.php?sayfa=iletisim_sosyal&durum=gecersiz_id");
        exit;
    }
    $sorgu = $db->prepare("DELETE FROM sosyal_medya WHERE id = ?");
    $sorgu->execute([$id]);
    header("Location: admin.php?sayfa=iletisim_sosyal&durum=silindi");
    exit;
}

// --- KURUMSAL: KARİYER URL GÜNCELLEME İŞLEMİ ---
if (isset($_POST['kariyer_guncelle'])) {
    verify_csrf_token($_POST['csrf_token']); // CSRF Koruması
    $kariyer_url = filter_var($_POST['kariyer_url'], FILTER_SANITIZE_URL);
    $sorgu = $db->prepare("UPDATE ayarlar SET kariyer_url = ? WHERE id = 1");
    $sorgu->execute([$kariyer_url]);
    header("Location: admin.php?sayfa=kurumsal_kariyer&durum=ok");
    exit;
}

// --- İLETİŞİM: MESAJ SİLME İŞLEMİ ---
if (isset($_GET['mesaj_sil']) && isset($_GET['csrf_token'])) {
    verify_csrf_token($_GET['csrf_token']); // CSRF Koruması
    $id = $_GET['mesaj_sil'];

    if (!isset($id) || !is_numeric($id)) {
        header("Location: admin.php?sayfa=iletisim_mesajlar&durum=gecersiz_id");
        exit;
    }

    $sorgu = $db->prepare("DELETE FROM iletisim_mesajlari WHERE id = ?");
    $sorgu->execute([$id]);
    header("Location: admin.php?sayfa=iletisim_mesajlar&durum=mesaj_silindi");
    exit;
}
?>