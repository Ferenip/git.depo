<?php
session_start();
include 'baglan.php';

// Eğer zaten giriş yapılmışsa direkt admin paneline yönlendir
if (isset($_SESSION['admin_oturum'])) {
    header("Location: admin.php");
    exit;
}

$hata = "";

// Form gönderildiğinde çalışacak kod
if (isset($_POST['giris_yap'])) {
    $kullanici = $_POST['kullanici_adi'];
    $sifre = $_POST['sifre'];

    // Veritabanında bu kullanıcıyı arıyoruz
    $sorgu = $db->prepare("SELECT * FROM yoneticiler WHERE kullanici_adi = ? AND sifre = ?");
    $sorgu->execute([$kullanici, $sifre]);
    $yonetici = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($yonetici) {
        // Eşleşme varsa oturumu başlat ve admin.php'ye gönder
        $_SESSION['admin_oturum'] = true;
        $_SESSION['admin_isim'] = $yonetici['kullanici_adi'];
        header("Location: admin.php");
        exit;
    } else {
        $hata = "Hatalı kullanıcı adı veya şifre girdiniz!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetici Girişi - Colombia Coffee</title>
    <style>
        body { 
            font-family: 'Arial', sans-serif; 
            background-color: #153523; /* Sitenin koyu yeşil rengi */
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .login-kutu { 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 10px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.5); 
            width: 100%; 
            max-width: 350px; 
            text-align: center; 
        }
        .login-kutu h2 { color: #153523; margin-bottom: 20px; }
        input { 
            width: 100%; 
            padding: 12px; 
            margin: 10px 0 20px 0; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
            box-sizing: border-box; 
        }
        button { 
            width: 100%; 
            padding: 12px; 
            background-color: #c6a87c; /* Sitenin dore/altın rengi */
            color: #153523; 
            border: none; 
            border-radius: 5px; 
            font-size: 16px; 
            font-weight: bold; 
            cursor: pointer; 
        }
        button:hover { background-color: #b5986c; }
        .hata-mesaji { color: red; font-size: 14px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="login-kutu">
    <h2>Yönetici Girişi</h2>
    
    <?php if ($hata != "") { echo "<div class='hata-mesaji'>$hata</div>"; } ?>
    
    <form method="POST">
        <input type="text" name="kullanici_adi" placeholder="Kullanıcı Adı" required>
        <input type="password" name="sifre" placeholder="Şifre" required>
        <button type="submit" name="giris_yap">Giriş Yap</button>
    </form>
</div>

</body>
</html>